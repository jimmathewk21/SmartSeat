<?php

namespace App\Support;

use App\Models\Setting;

class AuthPageSettings
{
    public const SETTING_KEY = 'auth_page_settings';

    public static function defaults(): array
    {
        return [
            'brand_image_url' => 'images/logo.webp',
            'signup_left_content' => "We respect your privacy and do not want your data or personal details to be exposed in the public domain, which may lead to unnecessary marketing emails or unsolicited communication.\n\nTo ensure a secure and focused experience for genuine participants, most pages of this conference website are restricted. Access will be granted only to verified users.\n\nWe kindly request you to submit your details for verification. Once your information is reviewed and approved, you will receive access to view all pages of the conference website, including complete details about the upcoming conference.\n\nWe appreciate your understanding and cooperation.",
            'login_left_content' => "We respect your privacy and do not want your data or personal details to be exposed in the public domain, which may lead to unnecessary marketing emails or unsolicited communication.\n\nTo ensure a secure and focused experience for genuine participants, most pages of this conference website are restricted. Access will be granted only to verified users.\n\nWe kindly request you to sign in with your approved account to continue and access the conference website details.\n\nWe appreciate your understanding and cooperation.",
            'verification_note' => 'This sign-up is only for accessing the website and does not confirm your participation in the conference.',
            'ip_label' => 'Your Current IP Address',
            'location_label' => 'Location',
            'location_value' => 'Kolkata, India',
            'signup_form_title' => 'Sign Up',
            'signup_form_subtitle' => 'Register to view complete conference information including speakers & sponsors — no fee required.',
            'signup_button_text' => 'Register',
            'login_form_title' => 'Sign In',
            'login_form_subtitle' => 'Sign in to view conference sessions and resources',
            'login_button_text' => 'Sign In',
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

        $defaults = self::defaults();
        $merged = array_merge($defaults, $decoded);

        if (empty($decoded['signup_left_content']) && ! empty($decoded['note_text'])) {
            $merged['signup_left_content'] = (string) $decoded['note_text'];
        }

        if (empty($decoded['login_left_content'])) {
            if (! empty($decoded['security_text'])) {
                $merged['login_left_content'] = (string) $decoded['security_text'];
            } elseif (! empty($decoded['note_text'])) {
                $merged['login_left_content'] = (string) $decoded['note_text'];
            }
        }

        return $merged;
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
