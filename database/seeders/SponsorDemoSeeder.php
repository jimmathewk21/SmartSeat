<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use App\Models\SponsorCategory;
use Illuminate\Database\Seeder;

class SponsorDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categoryRows = [
            ['name' => 'Platinum Sponsors', 'is_active' => true, 'sort_order' => 1, 'logo_size' => 'lg'],
            ['name' => 'Gold Sponsors', 'is_active' => true, 'sort_order' => 2, 'logo_size' => 'md'],
            ['name' => 'Silver Sponsors', 'is_active' => true, 'sort_order' => 3, 'logo_size' => 'sm'],
            ['name' => 'Archive Sponsors', 'is_active' => false, 'sort_order' => 99, 'logo_size' => 'md'],
        ];

        $categories = [];
        foreach ($categoryRows as $row) {
            $categories[$row['name']] = SponsorCategory::query()->updateOrCreate(
                ['name' => $row['name']],
                $row
            );
        }

        $sponsorRows = [
            [
                'name' => 'TechNova',
                'website_url' => 'https://technova.example.com',
                'logo_url' => 'images/sponsors/1.webp',
                'is_active' => true,
                'sort_order' => 1,
                'primary_category' => 'Platinum Sponsors',
                'secondary_categories' => ['Gold Sponsors'],
                'show_on_homepage' => true,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'LexBridge',
                'website_url' => 'https://lexbridge.example.com',
                'logo_url' => 'images/sponsors/2.webp',
                'is_active' => true,
                'sort_order' => 2,
                'primary_category' => 'Platinum Sponsors',
                'secondary_categories' => [],
                'show_on_homepage' => true,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Future Grid',
                'website_url' => 'https://futuregrid.example.com',
                'logo_url' => 'images/sponsors/3.webp',
                'is_active' => true,
                'sort_order' => 1,
                'primary_category' => 'Gold Sponsors',
                'secondary_categories' => [],
                'show_on_homepage' => true,
                'show_on_scrollbar' => false,
            ],
            [
                'name' => 'Barrett Ventures',
                'website_url' => 'https://barrettventures.example.com',
                'logo_url' => 'images/sponsors/4.webp',
                'is_active' => true,
                'sort_order' => 2,
                'primary_category' => 'Gold Sponsors',
                'secondary_categories' => ['Silver Sponsors'],
                'show_on_homepage' => false,
                'show_on_scrollbar' => true,
            ],
            [
                'name' => 'Archive Partner',
                'website_url' => 'https://archivepartner.example.com',
                'logo_url' => 'images/sponsors/5.webp',
                'is_active' => false,
                'sort_order' => 1,
                'primary_category' => 'Archive Sponsors',
                'secondary_categories' => [],
                'show_on_homepage' => false,
                'show_on_scrollbar' => false,
            ],
        ];

        foreach ($sponsorRows as $row) {
            $primaryCategory = $row['primary_category'];
            $secondaryCategories = $row['secondary_categories'];
            unset($row['primary_category'], $row['secondary_categories']);

            $sponsor = Sponsor::query()->updateOrCreate(
                ['name' => $row['name']],
                array_merge($row, [
                    'sponsor_category_id' => $categories[$primaryCategory]->id,
                ])
            );

            $categoryIds = [$categories[$primaryCategory]->id];
            foreach ($secondaryCategories as $secondaryCategory) {
                if (isset($categories[$secondaryCategory])) {
                    $categoryIds[] = $categories[$secondaryCategory]->id;
                }
            }

            $sponsor->sponsorCategories()->sync(array_values(array_unique($categoryIds)));
        }
    }
}
