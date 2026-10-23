<?php

namespace App\Support;

use App\Models\Setting;

class FaqPageSettings
{
    public const SETTING_KEY = 'faq_page_settings';

    public static function defaults(): array
    {
        return [
            'page_title' => 'FAQ + SUPPORT',
            'subtitle' => 'FREQUENTLY ASKED QUESTIONS',
            'subtitle_suffix' => '',
            'description' => 'Find quick answers by category. If your question is not listed, use the form to contact us.',
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
