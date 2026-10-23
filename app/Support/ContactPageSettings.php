<?php

namespace App\Support;

use App\Models\Setting;

class ContactPageSettings
{
    public const SETTING_KEY = 'contact_page_settings';

    public static function defaults(): array
    {
        return [
            'hero_label' => '[Contact US]',
            'hero_title' => 'Get In Touch',
            'hero_title_suffix' => "Let's Connect",
            'hero_description' => 'Need help with SmartSeat? Send us a message and our team will get back to you.',
            'organizer_heading' => 'Organised by:',
            'organizer_logo_url' => 'images/logo.webp',
            'address_heading' => 'Office Address:',
            'address_text' => '',
            'contact_heading' => 'Contact Details:',
            'contact_email' => '',
            'contact_phone' => '',
            'hours_heading' => 'Business Hours:',
            'hours_text' => "Monday - Friday\n11:00 AM to 5:00 PM (IST)",
            'social_heading' => 'Social Media:',
            'social_linkedin_url' => '#',
            'social_facebook_url' => '#',
            'social_x_url' => '#',
            'social_instagram_url' => '#',
            'social_whatsapp_url' => '#',
            'form_title' => 'SEND MESSAGE',
            'form_name_placeholder' => 'Enter Your Full Name',
            'form_email_placeholder' => 'Enter Your E-mail id',
            'form_subject_placeholder' => 'Enter Subject',
            'form_message_placeholder' => 'Enter Your Message',
            'form_button_text' => 'Submit',
            'form_success_message' => 'Thank you for reaching out. Our team will contact you shortly.',
            'testimonial_title' => 'What People Say...',
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
