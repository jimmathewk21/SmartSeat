<?php

namespace App\Support;

use App\Models\Setting;

class DownloadPageSettings
{
    public const SETTING_KEY = 'download_page_settings';

    protected static function normalizeMultilineContent(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/<\s*br\s*\/?>/i', "\n", $value);
        $value = preg_replace('/<\s*\/p\s*>\s*<\s*p\s*>/i', "\n\n", $value);
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace("/\r\n?/", "\n", $value);
        $value = preg_replace("/\n{3,}/", "\n\n", $value);

        return trim($value);
    }

    public static function defaults(): array
    {
        return [
            'hero_label' => '[MEDIA KIT]',
            'hero_title' => 'Download',
            'hero_title_suffix' => 'Media Kit',
            'hero_description' => 'Access our comprehensive Media Kit to explore event details, partnership opportunities, branding guidelines, and key information about the summit. Download now to discover how you can collaborate, sponsor, and be part of this global intellectual property gathering.',
            'intro_paragraph_1' => 'LESI invites all speakers, attendees, sponsors, and friends to help us spread the word on social media about the annual conference.',
            'intro_heading' => 'Who can use the media kit',
            'intro_paragraph_2' => 'Be sure to follow the event on LinkedIn, X, and Instagram. Like, comment, retweet, and share our posts. A little engagement goes a long way.',
            'intro_paragraph_3' => 'You can also create your own posts. Below you will find pre-drafted messages for you to use when mentioning the conference on your channels. Be sure to include hashtags and do not forget to download a graphic to include with your post.',
            'left_column_heading' => 'How to use this Media Kit',
            'left_column_content' => "To use when mentioning the meeting before, during, and after:\n\n#LESI2026",
            'right_column_heading' => 'How to use this Media Kit',
            'right_column_content' => "To use when mentioning the event and/or LESI:\n\n#LESI\n#IP\n#Innovation\n#Dublin",
            'testimonial_title' => 'What People Say...',
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

        $settings = array_merge(self::defaults(), $decoded);
        $settings['left_column_content'] = self::normalizeMultilineContent($settings['left_column_content'] ?? '');
        $settings['right_column_content'] = self::normalizeMultilineContent($settings['right_column_content'] ?? '');

        return $settings;
    }

    public static function set(array $data): void
    {
        $payload = array_merge(self::defaults(), $data);
        $payload['left_column_content'] = self::normalizeMultilineContent($payload['left_column_content'] ?? '');
        $payload['right_column_content'] = self::normalizeMultilineContent($payload['right_column_content'] ?? '');

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }
}
