<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LoginActivityGeoService
{
    public function lookup(?string $ipAddress): array
    {
        $ipAddress = trim((string) $ipAddress);

        if ($ipAddress === '') {
            return $this->emptyLocation();
        }

        $cacheKey = 'login_activity_geojs:' . sha1($ipAddress);
        $ttlMinutes = (int) config('services.geojs.cache_ttl_minutes', 1440);

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(max($ttlMinutes, 1)),
            fn () => $this->fetchLocation($ipAddress)
        );
    }

    private function fetchLocation(string $ipAddress): array
    {
        $baseUrl = rtrim((string) config('services.geojs.base_url', 'https://get.geojs.io'), '/');
        $timeout = (int) config('services.geojs.timeout_seconds', 5);

        try {
            $response = Http::timeout(max($timeout, 1))
                ->acceptJson()
                ->get($baseUrl . '/v1/ip/geo/' . rawurlencode($ipAddress) . '.json');

            if ($response->successful()) {
                $data = $response->json();

                if (is_array($data)) {
                    return [
                        'city' => $this->normalizeValue($data['city'] ?? null),
                        'country' => $this->normalizeValue($data['country'] ?? null),
                    ];
                }
            }
        } catch (\Throwable) {
            // Geo lookup is informational only. Fall back to empty values on any failure.
        }

        return $this->emptyLocation();
    }

    private function normalizeValue(mixed $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : '-';
    }

    private function emptyLocation(): array
    {
        return [
            'city' => '-',
            'country' => '-',
        ];
    }
}
