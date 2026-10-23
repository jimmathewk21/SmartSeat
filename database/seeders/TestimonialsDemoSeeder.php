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
                'name' => 'Riya Deshmukh',
                'designation' => 'Passenger',
                'company' => 'Pune',
                'year' => 2026,
                'message' => 'The train search was simple to use, and I could compare the available trips before booking.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2026-02-04',
                'is_active' => true,
                'sort_order' => 1,
                'show_on_homepage' => true,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Arjun Kulkarni',
                'designation' => 'Passenger',
                'company' => 'Nagpur',
                'year' => 2026,
                'message' => 'My booking details were easy to find, and the seat information was clear.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2026-02-11',
                'is_active' => true,
                'sort_order' => 2,
                'show_on_homepage' => true,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Meera Joshi',
                'designation' => 'Passenger',
                'company' => 'Nashik',
                'year' => 2025,
                'message' => 'The booking flow made it straightforward to choose a trip and keep track of my reservation.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2025-11-15',
                'is_active' => true,
                'sort_order' => 3,
                'show_on_homepage' => false,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Vikram Patil',
                'designation' => 'Passenger',
                'company' => 'Kolhapur',
                'year' => 2025,
                'message' => 'The search page showed the route options clearly and saved time when planning my journey.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2025-10-28',
                'is_active' => true,
                'sort_order' => 4,
                'show_on_homepage' => false,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Sana Khan',
                'designation' => 'Passenger',
                'company' => 'Aurangabad',
                'year' => 2024,
                'message' => 'I could review my booking and passenger details in one place after signing in.',
                'photo_url' => 'images/testimonial/1.webp',
                'testimonial_date' => '2024-09-19',
                'is_active' => true,
                'sort_order' => 5,
                'show_on_homepage' => false,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Omkar Shah',
                'designation' => 'Passenger',
                'company' => 'Mumbai',
                'year' => 2024,
                'message' => 'The trip information was presented in a clear format from search through booking.',
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
