<?php

namespace App\Support;

use App\Models\Setting;

class TestimonialPageSettings
{
    public const SETTING_KEY = 'testimonial_page_settings';

    public static function defaults(): array
    {
        return [
            'hero_label' => 'TESTIMONIALS + FEEDBACK',
            'hero_title' => 'WHAT PEOPLE SAY',
            'hero_suffix' => 'real attendee voices',
            'hero_description' => 'Read attendee and speaker experiences from recent sessions, workshops, and event highlights.',
            'filter_title' => 'Filters',
            'search_placeholder' => 'Search testimonials',
            'feedback_title' => 'Submit Feedback',
            'empty_state_text' => 'No testimonials available right now.',
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
