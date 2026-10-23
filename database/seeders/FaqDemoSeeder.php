<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Registration', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Payments', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Event Access', 'is_active' => true, 'sort_order' => 3],
        ];

        $categoryModels = [];
        foreach ($categories as $category) {
            $categoryModels[$category['name']] = FaqCategory::query()->updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $event = Event::query()->where('slug', 'ai-leadership-summit-2026')->first();

        $faqs = [
            [
                'faq_category_id' => $categoryModels['Registration']->id,
                'question' => 'Can I register multiple delegates in one booking?',
                'answer' => 'Yes. The ticket flow supports multiple delegates and plan quantities in a single booking.',
                'sort_order' => 1,
                'show_on_homepage' => true,
            ],
            [
                'faq_category_id' => $categoryModels['Payments']->id,
                'question' => 'Which currency is used for payments?',
                'answer' => 'All payments are processed in INR through Razorpay. Display prices can still be shown in INR or USD depending on the checkout flow.',
                'sort_order' => 2,
                'show_on_homepage' => true,
            ],
            [
                'faq_category_id' => $categoryModels['Event Access']->id,
                'question' => 'How do I find my ticket after payment?',
                'answer' => 'Once payment is verified, the booking is marked paid and the ticket appears in My Tickets for the logged-in user.',
                'sort_order' => 3,
                'show_on_homepage' => true,
            ],
            [
                'faq_category_id' => $categoryModels['Event Access']->id,
                'question' => 'Can I browse the demo event content locally?',
                'answer' => 'Yes. The demo seeders populate agenda sessions, speakers, sponsors, testimonials, downloads, and related event content for local browsing.',
                'sort_order' => 4,
                'show_on_homepage' => false,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::query()->updateOrCreate(
                ['question' => $faq['question']],
                array_merge($faq, [
                    'event_id' => $event?->id,
                    'is_active' => true,
                ])
            );
        }
    }
}
