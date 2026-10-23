<?php

namespace App\Support;

class FrontendLink
{
    /**
     * Frontend route prefixes that are safe to convert to local relative links.
     *
     * @var array<int, string>
     */
    private const INTERNAL_PREFIXES = [
        '',
        'index.html',
        'pages',
        'trains',
        'my-train-bookings',
        'faqs',
        'contact-us',
        'download',
        'login',
        'register',
        'password',
        'privacy-policy',
        'terms-and-conditions',
        'admin',
    ];

    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '#' || str_starts_with($value, '#')) {
            return $value;
        }

        if (! preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        $host = strtolower((string) parse_url($value, PHP_URL_HOST));
        $path = (string) (parse_url($value, PHP_URL_PATH) ?: '/');

        if (! self::isInternalPath($path) && ! self::matchesConfiguredHost($host)) {
            return $value;
        }

        $query = (string) parse_url($value, PHP_URL_QUERY);
        $fragment = (string) parse_url($value, PHP_URL_FRAGMENT);
        $normalized = $path === '' ? '/' : $path;

        if ($query !== '') {
            $normalized .= '?' . $query;
        }

        if ($fragment !== '') {
            $normalized .= '#' . $fragment;
        }

        return $normalized;
    }

    private static function isInternalPath(string $path): bool
    {
        $trimmed = trim($path);

        if ($trimmed === '' || $trimmed === '/') {
            return true;
        }

        $firstSegment = strtok(ltrim($trimmed, '/'), '/');

        return in_array($firstSegment, self::INTERNAL_PREFIXES, true);
    }

    private static function matchesConfiguredHost(string $host): bool
    {
        if ($host === '') {
            return false;
        }

        try {
            $appUrl = function_exists('config') ? (string) config('app.url') : '';
        } catch (\Throwable $e) {
            $appUrl = '';
        }

        $appHost = strtolower((string) parse_url($appUrl, PHP_URL_HOST));

        return $appHost !== '' && $host === $appHost;
    }
}
