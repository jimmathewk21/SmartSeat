<?php

namespace App\Support;

use App\Models\Setting;

class FrontendMaintenanceSettings
{
    public const SETTING_KEY = 'frontend_maintenance_settings';

    public static function defaults(): array
    {
        return [
            'enabled' => false,
            'background_image' => 'images/background/8.webp',
            'logo_url' => '',
            'badge_icon' => '',
            'title' => 'Website Under Maintenance',
            'headline' => "We're currently updating our website to serve you better. We'll be live again soon!",
            'thanks_text' => 'Thank you for your patience.',
            'inquiry_prefix' => 'For Inquiries, please email -',
            'inquiry_email' => '',
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
        $payload = array_merge(self::defaults(), self::get(), $data);

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function enabled(): bool
    {
        return (bool) (self::get()['enabled'] ?? false);
    }
}
