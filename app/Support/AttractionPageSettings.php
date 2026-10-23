<?php

namespace App\Support;

use App\Models\Setting;

class AttractionPageSettings
{
    public const SETTING_KEY = 'attraction_page_settings';

    public static function defaults(): array
    {
        return [
            'hero_label' => '[THINGS TO DO + LOCAL GUIDE]',
            'hero_title' => 'Explore Bengaluru',
            'hero_title_suffix' => 'discover nearby experiences',
            'hero_description' => 'Find standout places to visit during the event, from food and shopping to culture, wellness, and local highlights.',
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
