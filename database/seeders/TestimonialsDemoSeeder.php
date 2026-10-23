<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name' => 'David Jonson',
                'designation' => 'VP, Product Strategy',
                'company' => 'Microsoft, India',
                'year' => 2026,
                'message' => 'Excellent curation of speakers and strong networking value. The sessions were practical, relevant, and easy to apply back at work.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2026-02-04',
                'is_active' => true,
                'sort_order' => 1,
                'show_on_homepage' => true,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Sophia Carter',
                'designation' => 'Director, Innovation',
                'company' => 'Accenture',
                'year' => 2026,
                'message' => 'The event balanced strategy and execution very well. I especially appreciated the agenda flow and the quality of conversations after each panel.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2026-02-11',
                'is_active' => true,
                'sort_order' => 2,
                'show_on_homepage' => true,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Pravin Anand',
                'designation' => 'Senior Counsel',
                'company' => 'Anand and Anand',
                'year' => 2025,
                'message' => 'Thoughtful content, excellent moderation, and a very focused audience. The summit created the right environment for useful business discussions.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2025-11-15',
                'is_active' => true,
                'sort_order' => 3,
                'show_on_homepage' => false,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Howard Dale',
                'designation' => 'Global Partnerships Lead',
                'company' => 'Dale Advisory',
                'year' => 2025,
                'message' => 'A well-managed event with a professional audience. The sponsor visibility and speaker access were both stronger than expected.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2025-10-28',
                'is_active' => true,
                'sort_order' => 4,
                'show_on_homepage' => false,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Malcolm Barrett',
                'designation' => 'Chief Commercial Officer',
                'company' => 'Barrett Ventures',
                'year' => 2024,
                'message' => 'The audience quality stood out. We had productive meetings throughout the event and the sessions felt highly relevant to our market.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2024-09-19',
                'is_active' => true,
                'sort_order' => 5,
                'show_on_homepage' => false,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Nina Roberts',
                'designation' => 'Head of Events',
                'company' => 'Future Grid',
                'year' => 2024,
                'message' => 'From registration to closing remarks, the experience was smooth and polished. The production quality and venue operations were excellent.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2024-08-06',
                'is_active' => true,
                'sort_order' => 6,
                'show_on_homepage' => false,
                'show_on_scrollbar' => false,
            ],
        ];

        foreach ($rows as $row) {
            Testimonial::query()->updateOrCreate(
                [
                    'name' => $row['name'],
                    'company' => $row['company'],
                    'year' => $row['year'],
                ],
                $row
            );
        }
    }
}
