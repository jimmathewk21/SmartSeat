<?php

namespace App\Support;

use App\Models\Setting;

class SpeakerPageSettings
{
    public const SETTING_KEY = 'speaker_page_settings';

    public static function defaults(): array
    {
        return [
            'page_title' => 'SPEAKERS + PARTNERS',
            'subtitle' => 'EVENT SPEAKERS',
            'subtitle_suffix' => 'meet the experts',
            'description' => "In today's fast-paced and highly saturated market, standing out is essential. Strategic sponsorship offers a practical way to build visibility, strengthen trust, and connect with the right audience.",
            'apply_page_label' => '[Speaker Nomination]',
            'apply_page_title' => 'SPEAKER FORM',
            'apply_page_title_suffix' => 'Become A Speaker',
            'apply_page_description' => 'I will change the text later for time being use a dummy text. I will change the text later for time being use a dummy text',
            'guidelines_button_text' => 'SPEAKER GUIDELINES',
            'become_button_text' => 'BECOME A SPEAKER',
            'guidelines_content' => '<p class="mb-2">Speaker display should include:</p><ol class="mb-0"><li>Name</li><li>Designation</li><li>Company Name</li><li>Country</li><li>Profile/Bio</li><li>LinkedIn profile</li><li>Topic (3-4 lines)</li><li>Download advert</li></ol>',
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
