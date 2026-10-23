<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Setting;

class FrontendFooterSettings
{
    public const SETTING_KEY = 'frontend_footer';

    public static function defaults(): array
    {
        return [
            'logo_url' => 'images/logo.webp',
            'social_links' => [
                ['icon' => 'fa-brands fa-facebook-f', 'url' => '#'],
                ['icon' => 'fa-brands fa-x-twitter', 'url' => '#'],
                ['icon' => 'fa-brands fa-instagram', 'url' => '#'],
                ['icon' => 'fa-brands fa-pinterest-p', 'url' => '#'],
            ],

            'upcoming_events_title' => 'Upcoming Events',
            'upcoming_events' => [
                [
                    'datetime' => '11 April 2024 At 10 Pm In Dhaka',
                    'name' => 'Ui/ux Designer Meetup',
                    'cta_text' => 'Get A Ticket',
                    'cta_link' => '#',
                ],
                [
                    'datetime' => '11 April 2024 At 10 Pm In Dhaka',
                    'name' => 'Ui/ux Designer Meetup',
                    'cta_text' => 'Get A Ticket',
                    'cta_link' => '#',
                ],
            ],

            'quick_links_title' => 'Quick links',
            'quick_links' => [
                ['text' => 'About Us', 'url' => '#'],
                ['text' => 'Service', 'url' => '#'],
                ['text' => 'Online Ticket', 'url' => '#'],
                ['text' => 'Blog Post', 'url' => '#'],
                ['text' => 'Contact Us', 'url' => '#'],
            ],

            'company_links_title' => 'Our Company',
            'company_links' => [
                ['text' => 'Jubilee Events', 'url' => '#'],
                ['text' => 'Gala Affairs', 'url' => '#'],
                ['text' => 'Inspire and Delight Events', 'url' => '#'],
                ['text' => 'Enchanted Gatherings', 'url' => '#'],
                ['text' => 'Eventful Ventures', 'url' => '#'],
            ],

            'contact_title' => 'Contact',
            'contact_email' => 'nafi25@gmail.com',
            'contact_email_icon' => 'fa fa-envelope',
            'contact_address' => '3891 Ranchview Dr. Richardson',
            'contact_address_icon' => 'fa fa-map-marker',
            'contact_phone' => '01245789321, 012457895146',
            'contact_phone_icon' => 'fa fa-phone',

            'copyright_text' => '(c) EventFlow 2024 | All Rights Reserved',
            'bottom_links' => [
                ['text' => 'Terms & Condition', 'url' => '#'],
                ['text' => 'Privacy Policy', 'url' => '#'],
                ['text' => 'Contact Us', 'url' => '#'],
            ],
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

        return self::applyPageSortOrder(self::normalize(array_merge(self::defaults(), $decoded)));
    }

    public static function set(array $data): void
    {
        $payload = self::normalize(array_merge(self::defaults(), $data));

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }

    private static function normalize(array $data): array
    {
        if (empty($data['social_links']) || ! is_array($data['social_links'])) {
            $socialLinks = [];
            $legacySocialMap = [
                ['icon' => 'fa-brands fa-facebook-f', 'key' => 'social_facebook'],
                ['icon' => 'fa-brands fa-x-twitter', 'key' => 'social_twitter'],
                ['icon' => 'fa-brands fa-instagram', 'key' => 'social_instagram'],
                ['icon' => 'fa-brands fa-pinterest-p', 'key' => 'social_pinterest'],
            ];

            foreach ($legacySocialMap as $item) {
                $url = trim((string) ($data[$item['key']] ?? ''));
                if ($url !== '') {
                    $socialLinks[] = ['icon' => $item['icon'], 'url' => $url];
                }
            }

            if (! empty($socialLinks)) {
                $data['social_links'] = $socialLinks;
            }
        }

        if (empty($data['upcoming_events']) || ! is_array($data['upcoming_events'])) {
            $legacyEvents = [];
            for ($i = 1; $i <= 2; $i++) {
                $legacyEvents[] = [
                    'datetime' => (string) ($data['event_' . $i . '_datetime'] ?? ''),
                    'name' => (string) ($data['event_' . $i . '_name'] ?? ''),
                    'cta_text' => (string) ($data['event_' . $i . '_cta_text'] ?? ''),
                    'cta_link' => (string) ($data['event_' . $i . '_cta_link'] ?? ''),
                ];
            }
            $data['upcoming_events'] = $legacyEvents;
        }

        if (empty($data['quick_links']) || ! is_array($data['quick_links'])) {
            $legacyQuickLinks = [];
            for ($i = 1; $i <= 5; $i++) {
                $legacyQuickLinks[] = [
                    'text' => (string) ($data['quick_link_' . $i . '_text'] ?? ''),
                    'url' => (string) ($data['quick_link_' . $i . '_url'] ?? ''),
                ];
            }
            $data['quick_links'] = $legacyQuickLinks;
        }

        if (empty($data['company_links']) || ! is_array($data['company_links'])) {
            $legacyCompanyLinks = [];
            for ($i = 1; $i <= 5; $i++) {
                $legacyCompanyLinks[] = [
                    'text' => (string) ($data['company_link_' . $i . '_text'] ?? ''),
                    'url' => (string) ($data['company_link_' . $i . '_url'] ?? ''),
                ];
            }
            $data['company_links'] = $legacyCompanyLinks;
        }

        if (empty($data['bottom_links']) || ! is_array($data['bottom_links'])) {
            $legacyBottomLinks = [];
            for ($i = 1; $i <= 3; $i++) {
                $legacyBottomLinks[] = [
                    'text' => (string) ($data['bottom_link_' . $i . '_text'] ?? ''),
                    'url' => (string) ($data['bottom_link_' . $i . '_url'] ?? ''),
                ];
            }
            $data['bottom_links'] = $legacyBottomLinks;
        }

        $data['upcoming_events'] = collect($data['upcoming_events'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->map(fn ($item) => [
                'datetime' => (string) ($item['datetime'] ?? ''),
                'name' => (string) ($item['name'] ?? ''),
                'cta_text' => (string) ($item['cta_text'] ?? ''),
                'cta_link' => FrontendLink::normalize((string) ($item['cta_link'] ?? '')),
            ])
            ->values()
            ->all();

        foreach (['quick_links', 'company_links', 'bottom_links'] as $key) {
            $data[$key] = collect($data[$key] ?? [])
                ->filter(fn ($item) => is_array($item))
                ->map(fn ($item) => [
                    'text' => (string) ($item['text'] ?? ''),
                    'url' => FrontendLink::normalize((string) ($item['url'] ?? '')),
                ])
                ->values()
                ->all();
        }

        $data['contact_email_icon'] = trim((string) ($data['contact_email_icon'] ?? '')) ?: 'fa fa-envelope';
        $data['contact_address_icon'] = trim((string) ($data['contact_address_icon'] ?? '')) ?: 'fa fa-map-marker';
        $data['contact_phone_icon'] = trim((string) ($data['contact_phone_icon'] ?? '')) ?: 'fa fa-phone';

        return $data;
    }

    private static function applyPageSortOrder(array $data): array
    {
        try {
            // slug => sort_order directly from DB (e.g. 'terms-condition' => 1)
            $pageOrderMap = Page::query()
                ->select(['slug', 'sort_order'])
                ->where('is_active', true)
                ->get()
                ->pluck('sort_order', 'slug')
                ->all();
        } catch (\Throwable $e) {
            return $data;
        }

        if (empty($pageOrderMap)) {
            return $data;
        }

        foreach (['quick_links', 'company_links', 'bottom_links'] as $key) {
            $data[$key] = collect($data[$key] ?? [])
                ->values()
                ->map(function ($item, int $position) use ($pageOrderMap) {
                    $path        = (string) parse_url((string) ($item['url'] ?? ''), PHP_URL_PATH);
                    $trimmedPath = trim($path, '/');

                    // Support both /pages/{slug} and direct /{slug} URL formats
                    $slug = str_starts_with($trimmedPath, 'pages/')
                        ? trim(substr($trimmedPath, strlen('pages/')), '/')
                        : $trimmedPath;

                    $sortOrder = ($slug !== '' && isset($pageOrderMap[$slug]))
                        ? (int) $pageOrderMap[$slug]
                        : PHP_INT_MAX;

                    return ['position' => $position, 'sort_order' => $sortOrder, 'item' => $item];
                })
                ->sort(fn ($a, $b) => [$a['sort_order'], $a['position']] <=> [$b['sort_order'], $b['position']])
                ->pluck('item')
                ->values()
                ->all();
        }

        return $data;
    }
}
