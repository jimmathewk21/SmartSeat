<?php

namespace Database\Seeders;

use App\Models\BookingTicket;
use App\Models\Event;
use App\Models\EventBooking;
use App\Models\EventBookingDelegate;
use App\Models\EventTicketPlan;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketDemoSeeder extends Seeder
{
    public function run()
    {
        $event = Event::query()->first();
        if (! $event) {
            $event = Event::create([
                'title' => 'AI Commerce Summit 2026',
                'slug' => 'ai-commerce-summit-2026',
                'venue' => 'Innovation Hall, Bengaluru',
                'start_at' => now()->addDays(30),
                'end_at' => now()->addDays(31),
                'description' => 'Demo event for ticketing checkout, promo and Razorpay testing.',
                'is_published' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }

        $plans = [
            [
                'plan_index' => 1,
                'sort_order' => 1,
                'name' => 'Economy',
                'mrp_inr_paise' => 99900,
                'sale_inr_paise' => 69900,
                'mrp_usd_cents' => 1500,
                'sale_usd_cents' => 1099,
                'benefits' => "Access to keynote sessions\nNetworking opportunities\nDigital event kit",
                'valid_till_text' => 'Valid till June 30',
                'is_active' => true,
            ],
            [
                'plan_index' => 2,
                'sort_order' => 2,
                'name' => 'Prime',
                'mrp_inr_paise' => 149900,
                'sale_inr_paise' => 119900,
                'mrp_usd_cents' => 2400,
                'sale_usd_cents' => 1999,
                'benefits' => "All Economy benefits\nVIP lounge access\nPriority seating",
                'valid_till_text' => 'Valid till July 10',
                'is_active' => true,
            ],
            [
                'plan_index' => 3,
                'sort_order' => 3,
                'name' => 'Premium (VIP)',
                'mrp_inr_paise' => 249900,
                'sale_inr_paise' => 199900,
                'mrp_usd_cents' => 3900,
                'sale_usd_cents' => 3199,
                'benefits' => "All Prime benefits\nMeet-and-greet access\nAfter-party access",
                'valid_till_text' => 'Valid till July 20',
                'is_active' => true,
            ],
            [
                'plan_index' => 4,
                'sort_order' => 4,
                'name' => 'Enterprise',
                'mrp_inr_paise' => 399900,
                'sale_inr_paise' => 299900,
                'mrp_usd_cents' => 6200,
                'sale_usd_cents' => 4899,
                'benefits' => "All Premium benefits\nDedicated relationship desk\nPriority support",
                'valid_till_text' => 'Valid till July 31',
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            EventTicketPlan::query()->updateOrCreate(
                ['event_id' => $event->id, 'plan_index' => $plan['plan_index']],
                [
                    'event_id' => $event->id,
                    'plan_index' => $plan['plan_index'],
                    'sort_order' => $plan['sort_order'],
                    'name' => $plan['name'],
                    'price_paise' => $plan['sale_inr_paise'],
                    'mrp_inr_paise' => $plan['mrp_inr_paise'],
                    'sale_inr_paise' => $plan['sale_inr_paise'],
                    'mrp_usd_cents' => $plan['mrp_usd_cents'],
                    'sale_usd_cents' => $plan['sale_usd_cents'],
                    'benefits' => $plan['benefits'],
                    'valid_till_text' => $plan['valid_till_text'],
                    'is_active' => $plan['is_active'],
                ]
            );
        }

        $promos = [
            [
                'code' => 'SAVE10',
                'name' => '10 Percent OFF',
                'discount_type' => 'percentage',
                'discount_percent' => 10,
                'flat_inr_paise' => 0,
                'flat_usd_cents' => 0,
                'quantity_buy' => 0,
                'quantity_pay' => 0,
            ],
            [
                'code' => 'FLAT50',
                'name' => 'Flat 50 Discount',
                'discount_type' => 'flat',
                'discount_percent' => 0,
                'flat_inr_paise' => 5000,
                'flat_usd_cents' => 700,
                'quantity_buy' => 0,
                'quantity_pay' => 0,
            ],
            [
                'code' => 'BUY2P1',
                'name' => 'Buy 2 Pay 1',
                'discount_type' => 'quantity',
                'discount_percent' => 0,
                'flat_inr_paise' => 0,
                'flat_usd_cents' => 0,
                'quantity_buy' => 2,
                'quantity_pay' => 1,
            ],
        ];

        foreach ($promos as $promo) {
            PromoCode::query()->updateOrCreate(
                ['code' => $promo['code']],
                array_merge($promo, [
                    'max_uses' => 500,
                    'used_count' => 0,
                    'starts_at' => now()->subDays(1),
                    'ends_at' => now()->addMonths(6),
                    'is_active' => true,
                ])
            );
        }

        $user = User::query()->first();
        $plan = EventTicketPlan::query()
            ->where('event_id', $event->id)
            ->orderBy('sort_order')
            ->first();

        if ($user && $plan) {
            $booking = EventBooking::query()->updateOrCreate(
                ['booking_code' => 'BKDEMO001'],
                [
                    'user_id' => $user->id,
                    'participant_id' => null,
                    'event_id' => $event->id,
                    'event_ticket_plan_id' => $plan->id,
                    'quantity' => 2,
                    'unit_price_paise' => $plan->sale_inr_paise,
                    'total_amount_paise' => $plan->sale_inr_paise * 2,
                    'currency' => 'INR',
                    'billing_country_code' => 'IN',
                    'display_currency' => 'INR',
                    'display_unit_amount_minor' => $plan->sale_inr_paise,
                    'display_total_amount_minor' => $plan->sale_inr_paise * 2,
                    'fx_rate_usd_to_inr' => null,
                    'billing_company_name' => 'Demo Ventures Pvt Ltd',
                    'billing_address_line_1' => 'MG Road',
                    'billing_address_line_2' => 'Bengaluru',
                    'gstin' => '29ABCDE1234F1Z5',
                    'promo_code' => null,
                    'promo_discount_minor' => 0,
                    'gateway_charge_minor' => 0,
                    'subtotal_display_minor' => $plan->sale_inr_paise * 2,
                    'grand_total_display_minor' => $plan->sale_inr_paise * 2,
                    'status' => EventBooking::STATUS_PAID,
                    'razorpay_order_id' => null,
                    'razorpay_payment_id' => 'pay_demo_001',
                    'razorpay_signature' => 'demo-signature',
                    'failure_reason' => null,
                    'paid_at' => now()->subDay(),
                    'payment_payload' => json_encode(['source' => 'demo-seeder']),
                ]
            );

            EventBookingDelegate::query()->where('event_booking_id', $booking->id)->delete();
            BookingTicket::query()->where('event_booking_id', $booking->id)->delete();

            $delegates = [
                [
                    'delegate_index' => 1,
                    'delegate_name' => 'Amit Sharma',
                    'designation' => 'Engineering Manager',
                    'company_name' => 'Demo Ventures Pvt Ltd',
                    'country' => 'India',
                    'email' => 'amit.sharma@example.com',
                    'mobile' => '+919876543210',
                ],
                [
                    'delegate_index' => 2,
                    'delegate_name' => 'Neha Rao',
                    'designation' => 'Product Lead',
                    'company_name' => 'Demo Ventures Pvt Ltd',
                    'country' => 'India',
                    'email' => 'neha.rao@example.com',
                    'mobile' => '+919812345678',
                ],
            ];

            foreach ($delegates as $delegate) {
                EventBookingDelegate::query()->create(array_merge($delegate, [
                    'event_booking_id' => $booking->id,
                ]));
            }

            for ($i = 1; $i <= 2; $i++) {
                BookingTicket::query()->create([
                    'event_booking_id' => $booking->id,
                    'ticket_code' => 'TKT-DEMO-001-' . $i,
                    'status' => 'issued',
                    'issued_at' => now()->subDay(),
                ]);
            }
        }
    }
}
