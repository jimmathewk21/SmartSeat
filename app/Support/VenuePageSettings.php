<?php

namespace App\Support;

use App\Models\Setting;

class VenuePageSettings
{
    public const SETTING_KEY = 'venue_page_settings';

    public static function defaults(): array
    {
        return [
            'hero_label' => 'EVENT VENUE + HOTELS',
            'hero_title' => 'CHOOSE YOUR HOTEL',
            'hero_title_suffix' => 'book your stay',
            'hero_description' => 'Select from partner hotels and continue to room reservation on the next step.',
            'empty_state_text' => 'No hotels available right now.',
            'tax_rate' => '18',
            'tax_note_text' => ':tax_rate% GST will be applicable extra on the above rates.',
            'tax_badge_text' => ':tax_rate% GST extra',
            'tax_summary_note_text' => '* Taxes will be additionally charged as applied.',
            'reservation_eyebrow_text' => 'VENUE RESERVATION',
            'reservation_heading' => 'Reserve Your Stay',
            'reservation_heading_suffix' => '',
            'reservation_description' => 'Please fill all the details carefully so the hotel reservation team can review your request and contact you directly with the next steps.',
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
        // Preserve existing keys from the other venue settings screens.
        $payload = array_merge(self::get(), $data);

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }
}
