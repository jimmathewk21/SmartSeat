<?php

namespace App\Support;

use App\Models\Setting;

class MyContactPageSettings
{
    public const SETTING_KEY = 'my_contact_page_settings';

    public static function defaults(): array
    {
        return [
            'page_title' => '[World Leaders]',
            'subtitle' => 'IP Professionals',
            'subtitle_suffix' => 'across world',
            'description' => 'All information presented on this website is for informational purposes only. If you would like to have your name or profile listed, you may submit your details by filling out the official form available on the website.',
            'disclaimer_button_text' => 'Disclaimer',
            'form_button_text' => 'List Yourself',
            'form_button_link' => '/add-my-contact',
            'notification_email' => '',
            'guidelines_content' => '<p>This directory is for informational purposes only. Contact visibility and profile details are managed by the admin team.</p>',
            'apply_page_label' => '[Add My Contact]',
            'apply_page_title' => 'MY CONTACT FORM',
            'apply_page_title_suffix' => 'Submit Your Details',
            'apply_page_description' => 'Share your details through the official form and the admin team will review them before publishing your profile.',
            'country_options' => '',
            'specialization_options' => "Patent\nTrademark\nCopyright\nDesign\nLicensing",
            'speaker_tag_options' => "Speaker\nDelegate\nVIP",
            'experience_options' => "0-3 Years\n4-7 Years\n8-12 Years\n13-20 Years\n20+ Years",
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

        $settings = array_merge(self::defaults(), $decoded);

        if (trim((string) ($settings['form_button_text'] ?? '')) === 'Form') {
            $settings['form_button_text'] = 'List Yourself';
        }

        return $settings;
    }

    public static function set(array $data): void
    {
        $payload = array_merge(self::defaults(), $data);

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function parseOptionLines(mixed $value): array
    {
        $lines = preg_split('/[\r\n,]+/', (string) $value) ?: [];

        return collect($lines)
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->unique(fn (string $line) => mb_strtolower($line))
            ->values()
            ->all();
    }
}
