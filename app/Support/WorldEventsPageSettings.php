<?php

namespace App\Support;

use App\Models\Setting;

class WorldEventsPageSettings
{
    public const SETTING_KEY = 'world_events_page_settings';

    public static function defaults(): array
    {
        return [
            'hero_label' => '[Global IP Events]',
            'hero_title' => 'Monthly & Yearly Events Overview',
            'hero_title_suffix' => 'track events across the year',
            'hero_description' => 'Browse intellectual property events by month or year, search by event name, and filter by country from a single overview page.',
            'overview_title' => 'Monthly & Yearly Events Overview',
            'monthly_tab_text' => 'Monthly View',
            'yearly_tab_text' => 'Yearly View',
            'search_placeholder' => 'Search Events',
            'event_filter_label' => 'All Events',
            'country_filter_label' => 'All Countries',
            'monthly_timeline_title' => 'Events Timeline',
            'yearly_calendar_title' => 'Yearly Calendar',
            'empty_state_text' => 'No world events found for the selected filters.',
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
