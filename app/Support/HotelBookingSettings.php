<?php

namespace App\Support;

use App\Models\Setting;

class HotelBookingSettings
{
    public const SETTING_KEY = 'hotel_booking_settings';

    public static function defaults(): array
    {
        return [
            'hotel_name' => 'Event Partner Hotel',
            'hotel_address' => '',
            'available_from_date' => '',
            'available_to_date' => '',
            'direction_from' => '',
            'direction_to' => '',
            'admin_notification_email' => '',
        ];
    }

    public static function get(): array
    {
        $raw = Setting::query()->where('key', self::SETTING_KEY)->value('value');
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
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }
}

