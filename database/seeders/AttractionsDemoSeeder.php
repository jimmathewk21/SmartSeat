<?php

namespace Database\Seeders;

use App\Models\Attraction;
use Illuminate\Database\Seeder;

class AttractionsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Isha Foundation',
                'category' => 'Spiritual & Nature',
                'image_url' => 'images/misc/l1.webp',
                'website_url' => 'https://isha.sadhguru.org',
                'direction_url' => 'https://maps.google.com/?q=Isha+Foundation+Bengaluru',
                'detail' => "The Isha Foundation's Sannidhi near Bengaluru offers meditation spaces, yoga programs, spiritual experiences, and peaceful open surroundings for visitors during their stay.",
                'sort_order' => 10,
            ],
            [
                'name' => 'Cubbon Park',
                'category' => 'Spiritual & Nature',
                'image_url' => 'images/misc/l2.webp',
                'website_url' => 'https://karnatakaforest.gov.in',
                'direction_url' => 'https://maps.google.com/?q=Cubbon+Park+Bengaluru',
                'detail' => 'A large green space in central Bengaluru, Cubbon Park is ideal for morning walks, quiet breaks, and relaxed sightseeing between conference sessions.',
                'sort_order' => 20,
            ],
            [
                'name' => 'Bangalore Palace',
                'category' => 'Shopping',
                'image_url' => 'images/misc/l3.webp',
                'website_url' => 'https://www.bangalorepalace.org',
                'direction_url' => 'https://maps.google.com/?q=Bangalore+Palace',
                'detail' => 'Bangalore Palace combines historic architecture with a landmark destination feel, making it a strong pick for visitors looking to explore the city.',
                'sort_order' => 30,
            ],
            [
                'name' => 'UB City',
                'category' => 'Shopping',
                'image_url' => 'images/misc/l4.webp',
                'website_url' => 'https://www.ubcitybangalore.in',
                'direction_url' => 'https://maps.google.com/?q=UB+City+Bengaluru',
                'detail' => 'UB City offers premium retail, dining, and an upscale atmosphere for shopping, meetings, and evening leisure in Bengaluru.',
                'sort_order' => 40,
            ],
            [
                'name' => 'Toit Brewpub',
                'category' => 'Food & Bar',
                'image_url' => 'images/misc/l5.webp',
                'website_url' => 'https://toit.in',
                'direction_url' => 'https://maps.google.com/?q=Toit+Indiranagar+Bengaluru',
                'detail' => 'A well-known Bengaluru food and beverage destination, Toit is popular for team dinners, informal networking, and a lively city experience.',
                'sort_order' => 50,
            ],
            [
                'name' => 'Church Street',
                'category' => 'Food & Bar',
                'image_url' => 'images/misc/c2.webp',
                'website_url' => 'https://www.google.com/search?q=Church+Street+Bengaluru',
                'direction_url' => 'https://maps.google.com/?q=Church+Street+Bengaluru',
                'detail' => 'Church Street is one of the city’s most active social stretches, filled with cafes, bookstores, bars, and casual places to unwind after the event.',
                'sort_order' => 60,
            ],
        ];

        foreach ($items as $item) {
            Attraction::query()->updateOrCreate(
                ['name' => $item['name']],
                array_merge($item, [
                    'show_on_homepage' => true,
                    'is_active' => true,
                ])
            );
        }
    }
}
