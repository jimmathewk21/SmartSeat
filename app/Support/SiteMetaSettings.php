<?php

namespace App\Support;

use App\Models\Setting;

class SiteMetaSettings
{
    public const SETTING_KEY = 'site_meta';

    public static function defaults(): array
    {
        return [
            'default_title' => 'AIvent - AI Event, Conference and Meetup Website Template',
            'site_icon' => 'images/icon.webp',
            'admin_site_icon' => '',
            'pages' => [],
            'restricted_paths' => [],
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
        $data['pages'] = collect($data['pages'] ?? [])
            ->filter(fn ($item) => is_array($item))
            ->map(fn ($item) => [
                'path' => (string) ($item['path'] ?? ''),
                'title' => (string) ($item['title'] ?? ''),
            ])
            ->filter(fn ($item) => $item['path'] !== '' && $item['title'] !== '')
            ->values()
            ->all();

        $data['restricted_paths'] = collect($data['restricted_paths'] ?? [])
            ->map(fn ($path) => self::normalizePath((string) $path))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $data;
    }

    public static function set(array $data): void
    {
        $payload = array_merge(self::defaults(), $data);

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function resolveForPath(string $path): array
    {
        $settings = self::get();
        $normalizedPath = self::normalizePath($path);

        $matched = collect($settings['pages'])
            ->first(fn ($item) => self::normalizePath((string) ($item['path'] ?? '')) === $normalizedPath);

        return [
            'title' => $matched['title'] ?? $settings['default_title'],
            'site_icon' => $settings['site_icon'],
            'default_title' => $settings['default_title'],
            'pages' => $settings['pages'],
            'restricted_paths' => $settings['restricted_paths'] ?? [],
        ];
    }

    public static function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/') {
            return '/';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path, PHP_URL_PATH) ?: '/';
            $path = $parsed;
        }

        if (! str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return rtrim($path, '/') ?: '/';
    }
}
