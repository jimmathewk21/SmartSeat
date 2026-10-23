<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqDemoSeeder extends Seeder
{
    public function run(): void
    {
        $category = FaqCategory::query()->updateOrCreate(
            ['name' => 'Train Booking'],
            ['is_active' => true, 'sort_order' => 1]
        );

        $faqs = [
            [
                'question' => 'How do I search for a train?',
                'answer' => 'Choose your origin, destination, and travel date, then submit the search to view available trips.',
                'sort_order' => 1,
            ],
            [
                'question' => 'How can I view my train bookings?',
                'answer' => 'Sign in with your passenger account and open My Train Bookings to view your reservations.',
                'sort_order' => 2,
            ],
            [
                'question' => 'Can I cancel a train booking?',
                'answer' => 'Open the booking from My Train Bookings and use the cancellation option when it is available.',
                'sort_order' => 3,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::query()->updateOrCreate(
                ['question' => $faq['question']],
                array_merge($faq, [
                    'faq_category_id' => $category->id,
                    'is_active' => true,
                    'show_on_homepage' => false,
                ])
            );
        }
    }
}
