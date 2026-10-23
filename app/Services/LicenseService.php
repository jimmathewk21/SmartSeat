<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class LicenseService
{
    public const SETTING_KEY = 'LICENSE_BUNDLE';

    /**
     * Validate the license with an external API (if configured) and persist encrypted bundle.
     */
    public function validateAndStore(string $licenseKey, string $domain, bool $forceApi = false): void
    {
        $this->callValidationApiIfConfigured($licenseKey, $domain, $forceApi);

        $signature = $this->makeSignature($licenseKey, $domain);

        $payload = json_encode([
            'license_key' => $licenseKey,
            'domain'      => $domain,
            'signature'   => $signature,
        ]);

        $encrypted = Crypt::encryptString($payload);

        Setting::updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => $encrypted]
        );
    }

    /**
     * Return decrypted bundle or null when missing.
     */
    public function getBundle(): ?array
    {
        $encrypted = Setting::where('key', self::SETTING_KEY)->value('value');

        if (! $encrypted) {
            return null;
        }

        try {
            return json_decode(Crypt::decryptString($encrypted), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Check if bundle matches domain and signature.
     */
    public function isBundleValidForDomain(array $bundle, string $domain): bool
    {
        if (! isset($bundle['license_key'], $bundle['domain'], $bundle['signature'])) {
            return false;
        }

        $licensedDomain = $bundle['domain'];

        if (! $this->isDomainAllowed($domain, $licensedDomain)) {
            return false;
        }

        $expected = $this->makeSignature($bundle['license_key'], $licensedDomain);

        return hash_equals($expected, (string) $bundle['signature']);
    }

    private function isDomainAllowed(string $requested, string $licensed): bool
    {
        $requested = strtolower($requested);
        $licensed  = strtolower($licensed);

        if (hash_equals($requested, $licensed)) {
            return true;
        }

        if (in_array($requested, $this->devHosts(), true)) {
            return true;
        }

        // Allow dev.<licensed-domain> and nested like dev.foo.bar.com
        foreach ($this->devPrefixes() as $prefix) {
            if ($prefix === '') {
                continue;
            }

            if (str_starts_with($requested, $prefix . '.') && str_ends_with($requested, $licensed)) {
                return true;
            }
        }

        return false;
    }

    private function devHosts(): array
    {
        return array_map('strtolower', config('services.license.dev_hosts', []));
    }

    private function devPrefixes(): array
    {
        return array_map('strtolower', config('services.license.dev_prefixes', []));
    }

    private function makeSignature(string $licenseKey, string $domain): string
    {
        return hash_hmac('sha256', $licenseKey . '|' . $domain, config('app.key'));
    }

    private function callValidationApiIfConfigured(string $licenseKey, string $domain, bool $forceApi): void
    {
        $url = config('services.license.url');

        if (! $url && ! $forceApi) {
            return;
        }

        if (! $url && $forceApi) {
            throw new \RuntimeException('LICENSE API URL is not configured.');
        }

        $response = Http::post($url, [
            'license_key' => $licenseKey,
            'domain'      => $domain,
        ]);

        if (! $response->successful()) {
            throw RequestException::create($response);
        }

        $json = $response->json();

        if (! ($json['valid'] ?? false)) {
            throw new \RuntimeException('License validation failed at API.');
        }
    }
}
