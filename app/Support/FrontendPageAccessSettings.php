<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Request;

class FrontendPageAccessSettings
{
    public const SETTING_KEY = 'frontend_page_access';

    /** Pages that the "User" role cannot access by default */
    private const USER_BLOCKED_DEFAULTS = ['my_contacts'];

    /** Public/basic pages guests can access by default */
    private const GUEST_ALLOWED_DEFAULTS = [
        'home',
        'dynamic_pages',
        'faqs',
        'contact',
        'download',
    ];

    public static function definitions(): array
    {
        return [
            'home' => [
                'label' => 'Home',
                'routes' => ['frontend.trains.home', 'frontend.trains.search', 'frontend.trains.legacy-home', 'frontend.newsletter.store'],
                'paths' => ['/', 'trains', 'index.html'],
            ],
            'dynamic_pages' => [
                'label' => 'Dynamic Pages',
                'routes' => ['frontend.page.show'],
            ],
            'faqs' => [
                'label' => 'FAQs',
                'routes' => ['frontend.faqs.index', 'frontend.faqs.store'],
            ],
            'contact' => [
                'label' => 'Contact Us',
                'routes' => ['frontend.contact.index', 'frontend.contact.store'],
            ],
            'my_contacts' => [
                'label' => 'My Contacts',
                'routes' => ['frontend.my-contacts.index', 'frontend.my-contacts.create', 'frontend.my-contacts.check-email', 'frontend.my-contacts.store'],
            ],
            'download' => [
                'label' => 'Download / Media Kit',
                'routes' => ['frontend.download.index'],
            ],
        ];
    }

    public static function defaults(): array
    {
        return collect(self::definitions())
            ->mapWithKeys(fn (array $def, string $key) => [
                $key => [
                    'user'   => ! in_array($key, self::USER_BLOCKED_DEFAULTS, true),
                    'member' => true,
                    'guest'  => in_array($key, self::GUEST_ALLOWED_DEFAULTS, true),
                ],
            ])
            ->all();
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

        $defaults = self::defaults();

        return collect($defaults)
            ->mapWithKeys(function (array $roleDefaults, string $key) use ($decoded, $defaults) {
                $stored = $decoded[$key] ?? null;

                // Migrate old flat boolean format → new nested format
                if (is_bool($stored)) {
                    return [$key => $defaults[$key]];
                }

                if (! is_array($stored)) {
                    return [$key => $roleDefaults];
                }

                return [$key => [
                    'user'   => (bool) ($stored['user']   ?? $roleDefaults['user']),
                    'member' => (bool) ($stored['member'] ?? $roleDefaults['member']),
                    'guest'  => (bool) ($stored['guest']  ?? $roleDefaults['guest']),
                ]];
            })
            ->all();
    }

    public static function set(array $data): void
    {
        $payload = collect(self::defaults())
            ->mapWithKeys(fn (array $roleDefaults, string $key) => [
                $key => [
                    'user'   => (bool) ($data[$key]['user']   ?? false),
                    'member' => (bool) ($data[$key]['member'] ?? false),
                    'guest'  => (bool) ($data[$key]['guest']  ?? false),
                ],
            ])
            ->all();

        Setting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function isAccessible(Request $request): bool
    {
        $settings = self::get();
        $user     = auth()->user();

        // Admins always have full access
        if ($user && $user->is_admin) {
            return true;
        }

        // Determine which role column to check
        if (! $user) {
            $roleKey = 'guest';
        } elseif ($user->hasRole('Member')) {
            $roleKey = 'member';
        } else {
            $roleKey = 'user';
        }

        foreach (self::definitions() as $key => $definition) {
            if (! ($settings[$key][$roleKey] ?? false)) {
                continue;
            }

            foreach ($definition['routes'] ?? [] as $routeName) {
                if ($request->routeIs($routeName)) {
                    return true;
                }
            }

            foreach ($definition['paths'] ?? [] as $path) {
                $normalized = ltrim($path, '/');
                if ($normalized === '' && $request->path() === '/') {
                    return true;
                }
                if ($normalized !== '' && $request->is($normalized)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @deprecated Use isAccessible() */
    public static function isGuestAccessible(Request $request): bool
    {
        return self::isAccessible($request);
    }
}
