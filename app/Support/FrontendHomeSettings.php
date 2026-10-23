<?php

namespace App\Support;

use App\Models\Setting;

class FrontendHomeSettings
{
    public const SETTING_KEY = 'frontend_home_banner';

    public static function defaults(): array
    {
        return [
            'video_src' => 'mp4:video/2.mp4',
            'subtitle' => 'The Future of Intelligence',
            'title' => 'AI Summit 2026',
            'title_line_1' => 'AI Summit 2026',
            'title_line_2' => '',
            'title_line_3' => '',
            'event_date' => 'October 1-5, 2026',
            'event_location' => 'San Francisco, CA',
            'event_date_icon' => 'fa fa-calendar',
            'event_location_icon' => 'fa fa-location-pin',
            'about_subtitle' => 'About the Event',
            'about_title' => 'A Global Gathering of AI Innovators',
            'about_description' => 'Join thought leaders, developers, researchers, and founders as we explore how artificial intelligence is reshaping industries, creativity, and the future of work.',
            'about_point_1' => '5 days of keynotes, workshops, and networking',
            'about_point_2' => '50 world-class speakers',
            'about_point_3' => 'Startup showcase and live demos',
            'about_image' => 'images/misc/c1.webp',
            'hide_about_section' => false,
            'why_us_section_name' => 'Why Us',
            'why_us_title' => 'WHAT ABOUT EVENT',
            'why_us_description' => 'MDA EarthSat periodically provides business conferences for both Energy and Agricultural clients ranging from day conferences to week-long conferences. Our goal is to make each conference an informative and valuable event for you, your company, and for our industry.',
            'why_us_card_1_image' => 'images/misc/s3.webp',
            'why_us_card_1_title' => 'WHO WE ARE?',
            'why_us_card_1_text' => 'The Muscular Dystrophy Association is the world\'s leading nonprofit health agency dedicated to finding treatments and cures for muscular dystrophy and related diseases.',
            'why_us_card_2_image' => 'images/misc/s4.webp',
            'why_us_card_2_title' => 'WHAT WE DO?',
            'why_us_card_2_text' => 'We fund worldwide research, provide comprehensive health care services, and support local communities through advocacy and fundraising.',
            'why_us_card_3_image' => 'images/misc/s5.webp',
            'why_us_card_3_title' => 'WHY US?',
            'why_us_card_3_text' => 'Joining with us helps you access research news, health care information, and practical daily-living resources for families and professionals.',
            'why_us_card_4_image' => 'images/misc/s6.webp',
            'why_us_card_4_title' => 'OUR IMPACT?',
            'why_us_card_4_text' => 'Discover measurable outcomes through practical sessions, high-value networking, and collaboration opportunities.',
            'hide_why_us_section' => false,
            'testimonials_subtitle' => 'Testimonials',
            'testimonials_title' => 'What People Say',
            'testimonials_button_text' => 'View More',
            'testimonials_button_link' => '/testimonials',
            'countdown_title' => 'Hurry Up!',
            'countdown_subtitle' => 'Book Your Seat Now',
            'countdown_until' => '2026-10-01T08:00',
            'countdown_location_line_1' => '121 AI Blvd,',
            'countdown_location_line_2' => 'San Francisco BCA 94107',
            'primary_button_text' => 'Get Tickets',
            'primary_button_link' => '#section-tickets',
            'secondary_button_text' => 'View Schedule',
            'secondary_button_link' => '#section-schedule',
            'hide_banner_section' => false,
            'hide_countdown_section' => false,
            'hide_testimonials_section' => false,
            'speakers_subtitle' => 'Event Speakers',
            'speakers_title' => 'Meet Our Speakers',
            'speakers_description' => 'Connect with industry leaders, innovators, and subject-matter experts shaping the future of the event.',
            'speakers_view_all_text' => 'View All Speakers',
            'speakers_view_all_link' => '/speakers',
            'speakers_become_text' => 'Become a Speaker',
            'speakers_become_link' => '/become-speaker',
            'hide_speakers_section' => false,
            'counter_1_icon' => 'fa fa-users',
            'counter_1_number' => '1200',
            'counter_1_text' => 'Participants',
            'counter_2_icon' => 'fa fa-cubes',
            'counter_2_number' => '1500',
            'counter_2_text' => 'Topics',
            'counter_3_icon' => 'fa fa-power-off',
            'counter_3_number' => '1700',
            'counter_3_text' => 'Sessions',
            'counter_4_icon' => 'fa fa-microphone',
            'counter_4_number' => '1800',
            'counter_4_text' => 'Speakers',
            'hide_counter_section' => false,
            'agenda_subtitle' => 'Content Marketing Institute:',
            'agenda_title' => 'Executive Forum',
            'agenda_meta' => 'March 10-11, 2016 - Las Vegas, NV',
            'agenda_description_1' => 'Content Marketing Institute Executive Forum is an exclusive meeting for senior-level marketers in the discipline of content marketing.',
            'agenda_description_2' => 'The Executive Forum is limited to senior-level marketers with responsibility over some aspect of content marketing in their organizations.',
            'agenda_button_text' => 'Download Brochure',
            'agenda_button_link' => '#',
            'agenda_background_image' => 'images/background/4.webp',
            'agenda_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'hide_agenda_section' => false,
            'brands_subtitle' => 'Our Official Sponsors',
            'brands_title' => 'Trusted By Over 150 Organizations In More Than 50 Countries',
            'brands_button_text' => 'Become a Sponsor',
            'brands_button_link' => '#',
            'brands_view_all_text' => 'View All Sponsors',
            'brands_view_all_link' => '/sponsors',
            'brand_logo_1' => 'images/logo-light/1.webp',
            'brand_logo_2' => 'images/logo-light/2.webp',
            'brand_logo_3' => 'images/logo-light/3.webp',
            'brand_logo_4' => 'images/logo-light/4.webp',
            'brand_logo_5' => 'images/logo-light/5.webp',
            'brand_logo_6' => 'images/logo-light/6.webp',
            'brand_logo_7' => 'images/logo-light/7.webp',
            'brand_logo_8' => 'images/logo-light/8.webp',
            'hide_brands_section' => false,
            'gallery_image_1' => 'images/gallery/1.webp',
            'gallery_image_2' => 'images/gallery/2.webp',
            'gallery_image_3' => 'images/gallery/3.webp',
            'gallery_image_4' => 'images/gallery/4.webp',
            'gallery_image_5' => 'images/gallery/5.webp',
            'gallery_image_6' => 'images/gallery/6.webp',
            'gallery_images' => [
                'images/gallery/1.webp',
                'images/gallery/2.webp',
                'images/gallery/3.webp',
                'images/gallery/4.webp',
                'images/gallery/5.webp',
                'images/gallery/6.webp',
            ],
            'gallery_section_name' => 'Image Gallery',
            'gallery_main_title' => 'Moments From Our Events',
            'gallery_description' => 'Explore highlights from past conferences, sessions, and networking experiences.',
            'gallery_view_more_text' => 'View More',
            'gallery_view_more_link' => '#',
            'hide_gallery_section' => false,
            'faq_subtitle' => 'Everything You Need to Know',
            'faq_title' => 'Frequently Asked Questions',
            'faq_view_more_text' => 'View More',
            'faq_view_more_link' => '/faqs',
            'hide_faq_section' => false,
            'ticket_subtitle' => 'Ticket Options',
            'ticket_title' => 'Choose Your Pass',
            'ticket_description' => 'Select the perfect ticket for your needs and gain access to exclusive sessions, workshops, and more.',
            'home_featured_event_id' => null,
            'home_ticket_plan_limit' => 3,
            'ticket_1_image' => 'images/logo.webp',
            'ticket_1_title' => 'Standard',
            'ticket_1_price' => '$299',
            'ticket_1_date' => 'October 1 to 5 - 10:00 AM',
            'ticket_1_points' => "Access to keynotes and sessions.\nAdmission to exhibitions and demos.\nNetworking opportunities.\nDigital materials and session recordings.",
            'ticket_1_button_text' => 'Buy Ticket',
            'ticket_1_button_link' => 'tickets.html',
            'ticket_2_image' => 'images/logo.webp',
            'ticket_2_title' => 'VIP',
            'ticket_2_price' => '$699',
            'ticket_2_date' => 'October 1 to 5 - 10:00 AM',
            'ticket_2_points' => "All Standard benefits.\nVIP lounge access and exclusive events.\nFront-row seating and priority workshop access.\nVIP swag bag and exclusive content.",
            'ticket_2_button_text' => 'Buy Ticket',
            'ticket_2_button_link' => 'tickets.html',
            'ticket_3_image' => 'images/logo.webp',
            'ticket_3_title' => 'Full Access',
            'ticket_3_price' => '$1199',
            'ticket_3_date' => 'October 1 to 5 - 10:00 AM',
            'ticket_3_points' => "All VIP benefits.\nAccess to all workshops and breakout sessions.\nPersonalized session scheduling.\nSpeaker meet-and-greet and after-party access.",
            'ticket_3_button_text' => 'Buy Ticket',
            'ticket_3_button_link' => 'tickets.html',
            'hide_ticket_section' => false,
            'venue_subtitle' => 'Event Location',
            'venue_title' => 'Location & Venue',
            'venue_description' => 'Join us in the heart of innovation at San Francisco Tech Pavilion-surrounded by top hotels, transit, and culture.',
            'venue_image_1' => 'images/misc/l1.webp',
            'venue_image_2' => 'images/misc/l2.webp',
            'venue_images' => [
                'images/misc/l1.webp',
                'images/misc/l2.webp',
            ],
            'venue_view_more_text' => 'View More',
            'venue_view_more_link' => '#',
            'venue_address_heading' => 'Address',
            'venue_address_text' => '121 AI Blvd, San Francisco, CA 94107',
            'venue_phone_heading' => 'Phone',
            'venue_phone_text' => 'Call: +1 123 456 789',
            'venue_email_heading' => 'Email',
            'venue_email_text' => 'contact@aivent.com',
            'hide_venue_section' => false,
            'map_heading' => 'Reach Us',
            'map_title' => 'Get Direction to the Event Hall',
            'map_content' => 'Find us easily on the map and plan your route to the venue.',
            'map_iframe' => '<iframe src="https://www.google.com/maps?q=London+Eye&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
            'map_address_title' => 'Brighton Waterfront Hotel, Brighton, London',
            'map_address_line_1' => '1Hd- 50, 010 Avenue, NY 90001',
            'map_address_line_2' => 'United States',
            'map_tickets_title' => 'Tickets info',
            'map_tickets_name' => 'Name: Ronaldo Konig',
            'map_tickets_phone' => 'Phone: 009-21-55-595',
            'map_tickets_email' => 'Email: info@example.com',
            'map_programme_title' => 'Programme Details',
            'map_programme_name' => 'Name: Ronaldo Konig',
            'map_programme_phone' => 'Phone: 009-21-55-595',
            'map_programme_email' => 'Email: info@example.com',
            'hide_map_section' => false,
            'things_subtitle' => 'Things To Do',
            'things_title' => 'Discover Local Attractions',
            'things_description' => 'Explore standout nearby experiences, landmarks, and attractions while attending the event.',
            'things_card_1_image' => 'images/misc/1.webp',
            'things_card_1_title' => 'THE GRAND PALACE',
            'things_card_2_image' => 'images/misc/2.webp',
            'things_card_2_title' => 'SAFARI WORLD AND MARINE PARK',
            'things_card_3_image' => 'images/misc/3.webp',
            'things_card_3_title' => 'KING POWER MAHANAKHON SKYWALK',
            'things_card_4_image' => 'images/misc/4.webp',
            'things_card_4_title' => 'CITY SKYLINE EXPERIENCE',
            'things_button_text' => 'VIEW ALL ATTRACTIONS',
            'things_button_link' => '#',
            'hide_things_section' => false,
            'newsletter_title' => 'Join the Future of Innovation',
            'newsletter_placeholder' => 'Enter Your Email Address',
            'newsletter_button_text' => 'SIGN UP',
            'hide_newsletter_section' => false,
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

        return self::normalize(array_merge(self::defaults(), $decoded));
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
        $lineOne = trim((string) ($data['title_line_1'] ?? ''));
        $lineTwo = trim((string) ($data['title_line_2'] ?? ''));
        $lineThree = trim((string) ($data['title_line_3'] ?? ''));
        $legacyTitle = trim((string) ($data['title'] ?? ''));

        if ($lineOne === '') {
            $segments = preg_split('/\r\n|\r|\n/', $legacyTitle, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $lineOne = trim((string) ($segments[0] ?? $legacyTitle));
            $lineTwo = trim((string) ($segments[1] ?? $lineTwo));
            $lineThree = trim((string) ($segments[2] ?? $lineThree));
        }

        $data['title_line_1'] = $lineOne;
        $data['title_line_2'] = $lineTwo;
        $data['title_line_3'] = $lineThree;
        $data['title'] = implode("\n", array_values(array_filter([$lineOne, $lineTwo, $lineThree], static fn ($value) => $value !== '')));

        foreach ([
            'testimonials_button_link',
            'primary_button_link',
            'secondary_button_link',
            'speakers_view_all_link',
            'speakers_become_link',
            'agenda_button_link',
            'brands_button_link',
            'brands_view_all_link',
            'gallery_view_more_link',
            'faq_view_more_link',
            'ticket_1_button_link',
            'ticket_2_button_link',
            'ticket_3_button_link',
            'venue_view_more_link',
            'things_button_link',
        ] as $key) {
            $data[$key] = FrontendLink::normalize((string) ($data[$key] ?? ''));
        }

        return $data;
    }
}
