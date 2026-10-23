<?php

namespace App\Support;

use App\Models\Setting;

class FrontendHeaderSettings
{
    public const SETTING_KEY = 'frontend_header';

    public static function defaults(): array
    {
        return [
            'logo_url' => 'images/logo.webp',
            'menu_items' => [],
            'button_text' => 'Buy Tickets',
            'button_link' => '#section-tickets',
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
            ->filter(fn ($item) => $item['name'] !== '' && $item['link'] !== '')
            ->values()
            ->all();

        $data['button_link'] = FrontendLink::normalize((string) ($data['button_link'] ?? ''));

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
            ->values()
            ->all();
        $payload['button_link'] = FrontendLink::normalize((string) ($payload['button_link'] ?? ''));

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function pageOptions(): array
    {
        return [
            '#section-hero' => 'Home Section',
            '#section-about' => 'About Section',
            '#section-why-attend' => 'Why Attend Section',
            '#section-speakers' => 'Speakers Section',
            '#section-schedule' => 'Schedule Section',
            '#section-tickets' => 'Tickets Section',
            '#section-venue' => 'Venue Section',
            '#section-faq' => 'FAQ Section',
            '/' => 'Home Page',
            '/sponsors' => 'Sponsors Page',
            '/testimonials' => 'Testimonials Page',
            '/agenda' => 'Agenda Page',
            '/faqs' => 'FAQ Page',
            '/venue' => 'Venue Hotels Page',
            '/become-sponsor' => 'Become Sponsor Page',
            '/admin' => 'Admin Page',
            'custom' => 'Custom URL',
        ];
    }
}
