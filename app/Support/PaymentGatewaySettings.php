<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;

class PaymentGatewaySettings
{
    public const SETTING_KEY = 'payment_gateway_settings';

    public static function defaults(): array
    {
        return [
            'enabled' => true,
        ];
    }

    public static function get(): array
    {
        $raw = Setting::query()
            ->where('key', self::SETTING_KEY)
            ->value('value');

        if (! $raw) {
            return self::defaults();
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return self::defaults();
        }

        if (! is_array($decoded)) {
            return self::defaults();
        }

        return array_merge(self::defaults(), $decoded);
    }

    public static function set(array $data): void
    {
        $payload = array_merge(self::defaults(), $data);

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function enabled(): bool
    {
        return (bool) (self::get()['enabled'] ?? true);
    }

    public static function hasCredentials(): bool
    {
        $keyId = trim((string) Config::get('services.razorpay.key_id', ''));
        $keySecret = trim((string) Config::get('services.razorpay.key_secret', ''));

        return $keyId !== '' && $keySecret !== '';
    }

    public static function isOperational(): bool
    {
        return self::enabled() && self::hasCredentials();
    }
}
