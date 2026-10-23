<?php

namespace App\Support;

use App\Models\Setting;

class FrontendHeaderSettings
{
    public const SETTING_KEY = 'frontend_header';

    private const RETIRED_PATHS = [
        '/speakers',
        '/become-speaker',
        '/sponsors',
        '/become-sponsor',
        '/events',
        '/world-events',
        '/agenda',
        '/attractions',
        '/things-to-do',
        '/testimonials',
        '/venue',
        '/hotel-booking',
        '/my-tickets',
    ];

    private const RETIRED_ANCHORS = [
        '#section-hero',
        '#section-about',
        '#section-why-attend',
        '#section-speakers',
        '#section-schedule',
        '#section-tickets',
        '#section-venue',
        '#section-faq',
        '#section-newsletter',
    ];

    public static function defaults(): array
    {
        return [
            'logo_url' => 'images/logo.webp',
            'menu_items' => [],
            'button_text' => 'Search Trains',
            'button_link' => '/trains',
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

        $data['menu_items'] = collect($data['menu_items'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->map(fn ($item) => [
                'name' => (string) ($item['name'] ?? ''),
                'link' => FrontendLink::normalize((string) ($item['link'] ?? '')),
            ])
            ->filter(fn ($item) => $item['name'] !== '' && $item['link'] !== '' && ! self::isRetiredLink($item['link']))
            ->values()
            ->all();

        $data['button_link'] = FrontendLink::normalize((string) ($data['button_link'] ?? ''));

        if (self::isRetiredLink($data['button_link'])) {
            $data['button_text'] = self::defaults()['button_text'];
            $data['button_link'] = self::defaults()['button_link'];
        }

        return $data;
    }

    public static function set(array $data): void
    {
        $payload = array_merge(self::defaults(), $data);
        $payload['menu_items'] = collect($payload['menu_items'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->map(fn ($item) => [
                'name' => (string) ($item['name'] ?? ''),
                'link' => FrontendLink::normalize((string) ($item['link'] ?? '')),
            ])
            ->filter(fn ($item) => $item['name'] !== '' && $item['link'] !== '' && ! self::isRetiredLink($item['link']))
            ->values()
            ->all();
        $payload['button_link'] = FrontendLink::normalize((string) ($payload['button_link'] ?? ''));

        if (self::isRetiredLink($payload['button_link'])) {
            $payload['button_text'] = self::defaults()['button_text'];
            $payload['button_link'] = self::defaults()['button_link'];
        }

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function pageOptions(): array
    {
        return [
            '/' => 'Railway Home',
            '/index.html' => 'Railway Home (legacy link)',
            '/trains' => 'Train Search',
            '/my-train-bookings' => 'My Train Bookings',
            '/faqs' => 'FAQs',
            '/contact-us' => 'Contact Us',
            '/download' => 'Downloads',
            'custom' => 'Custom URL',
        ];
    }

    private static function isRetiredLink(string $link): bool
    {
        $normalizedLink = strtolower(trim($link));

        if (in_array($normalizedLink, self::RETIRED_ANCHORS, true)) {
            return true;
        }

        if (preg_match('/^https?:\/\//i', $normalizedLink)) {
            return false;
        }

        $path = '/' . ltrim((string) parse_url($normalizedLink, PHP_URL_PATH), '/');

        foreach (self::RETIRED_PATHS as $retiredPath) {
            if ($path === $retiredPath || str_starts_with($path, $retiredPath . '/')) {
                return true;
            }
        }

        return false;
    }
}
