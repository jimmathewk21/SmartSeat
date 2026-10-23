<?php

namespace App\Support;

use App\Models\Setting;

class EventPageSettings
{
    public const SETTING_KEY = 'event_page_settings';

    public static function defaults(): array
    {
        return [
            'hero_label' => '[EVENTS + HIGHLIGHTS]',
            'hero_title' => 'What People Say',
            'hero_title_suffix' => 'real attendee voices',
            'hero_description' => 'Browse published events, see upcoming editions, and revisit past event highlights, brochures, and venue details.',
            'upcoming_title' => 'Upcoming Event',
            'past_title' => 'Past Events',
            'testimonial_title' => 'What People Say...',
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
}
