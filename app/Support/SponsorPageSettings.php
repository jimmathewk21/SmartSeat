<?php

namespace App\Support;

use App\Models\Setting;

class SponsorPageSettings
{
    public const SETTING_KEY = 'sponsor_page_settings';

    public static function defaults(): array
    {
        return [
            'page_title' => 'SPONSORS + PARTNERS',
            'subtitle' => 'EVENT SPONSORS',
            'subtitle_suffix' => "don't forget it",
            'description' => "In today's fast-paced and highly saturated market, standing out is essential. Strategic sponsorship offers a practical way to build visibility, strengthen trust, and connect with the right audience.",
            'button_text' => 'BECOME A SPONSOR',
            'button_link' => '/become-sponsor',
            'guidelines_button_text' => 'SPONSOR GUIDELINES',
            'guidelines_content' => "<p class=\"mb-2\">Sponsor display should include:</p><ol class=\"mb-0\"><li>Category</li><li>Name</li><li>Logo</li><li>Profile on logo click</li></ol>",
            'apply_page_label' => '[Sponsor Nomination]',
            'apply_page_title' => 'SPONSOR FORM',
            'apply_page_title_suffix' => 'Become A Sponsor',
            'apply_page_description' => 'Share your company details, sponsorship interest, and preferred category. Our team will review the information and contact you with the most suitable sponsorship proposal.',
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

        $data = array_merge(self::defaults(), $decoded);
        $data['button_link'] = FrontendLink::normalize((string) ($data['button_link'] ?? ''));

        return $data;
    }

    public static function set(array $data): void
    {
        $payload = array_merge(self::defaults(), $data);
        $payload['button_link'] = FrontendLink::normalize((string) ($payload['button_link'] ?? ''));

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }
}
