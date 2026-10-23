<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventTicketPlan;
use Illuminate\Database\Seeder;

class EventDemoSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'title' => 'AI Leadership Summit 2026',
                'slug' => 'ai-leadership-summit-2026',
                'venue' => 'Innovation Hall, San Francisco',
                'start_at' => now()->addMonths(4)->setTime(9, 0),
                'end_at' => now()->addMonths(4)->addDay()->setTime(18, 0),
                'description' => 'A flagship upcoming event focused on AI strategy, enterprise adoption, legal frameworks, and high-value networking.',
                'image_url' => 'images/misc/l1.webp',
                'gallery_url' => '/speakers',
                'brochure_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'is_published' => true,
                'is_active' => true,
                'sort_order' => 1,
                'plans' => [
                    [
                        'plan_index' => 1,
                        'sort_order' => 1,
                        'name' => 'Standard',
                        'mrp_inr_paise' => 149900,
                        'sale_inr_paise' => 99900,
                        'mrp_usd_cents' => 1999,
                        'sale_usd_cents' => 1499,
                        'benefits' => "Keynote access\nNetworking lounge\nDelegate kit",
                        'valid_till_text' => 'Valid till early bird window',
                        'is_active' => true,
                    ],
                    [
                        'plan_index' => 2,
                        'sort_order' => 2,
                        'name' => 'VIP',
                        'mrp_inr_paise' => 249900,
                        'sale_inr_paise' => 199900,
                        'mrp_usd_cents' => 3499,
                        'sale_usd_cents' => 2799,
                        'benefits' => "All Standard benefits\nVIP seating\nSpeaker meet-and-greet",
                        'valid_till_text' => 'Valid till premium window',
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'title' => 'Global IP Forum 2025',
                'slug' => 'global-ip-forum-2025',
                'venue' => 'Hotel Conrad, Dubai',
                'start_at' => now()->subMonths(3)->setTime(10, 0),
                'end_at' => now()->subMonths(3)->addDay()->setTime(17, 0),
                'description' => 'A past event covering cross-border IP strategy, innovation law, and technology policy insights.',
                'image_url' => 'images/misc/l2.webp',
                'gallery_url' => '/testimonials',
                'brochure_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'is_published' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Legal Tech Connect 2025',
                'slug' => 'legal-tech-connect-2025',
                'venue' => 'Marina Bay Centre, Singapore',
                'start_at' => now()->subMonths(7)->setTime(9, 30),
                'end_at' => now()->subMonths(7)->addDay()->setTime(18, 30),
                'description' => 'A past event on legal operations, AI tooling, digital contracts, and litigation technology.',
                'image_url' => 'images/misc/l3.webp',
                'gallery_url' => '/agenda',
                'brochure_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'is_published' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Enterprise Data & AI Summit 2024',
                'slug' => 'enterprise-data-ai-summit-2024',
                'venue' => 'BKC Convention Center, Mumbai',
                'start_at' => now()->subYear()->setTime(10, 0),
                'end_at' => now()->subYear()->addDay()->setTime(18, 0),
                'description' => 'A past event focused on enterprise data platforms, compliance, and AI transformation programs.',
                'image_url' => 'images/misc/l4.webp',
                'gallery_url' => '/sponsors',
                'brochure_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                'is_published' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($events as $row) {
            $plans = $row['plans'] ?? [];
            unset($row['plans']);

            $event = Event::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row
            );

            if (! empty($plans)) {
                foreach ($plans as $plan) {
                    EventTicketPlan::query()->updateOrCreate(
                        ['event_id' => $event->id, 'plan_index' => $plan['plan_index']],
                        array_merge($plan, [
                            'event_id' => $event->id,
                            'price_paise' => $plan['sale_inr_paise'],
                        ])
                    );
                }
            }
        }
    }
}
