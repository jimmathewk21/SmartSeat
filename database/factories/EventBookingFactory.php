<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventBooking;
use App\Models\EventTicketPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventBookingFactory extends Factory
{
    protected $model = EventBooking::class;

    public function definition(): array
    {
        return [
            'booking_code' => 'BK-' . now()->format('Ymd') . '-' . strtoupper(fake()->bothify('########')),
            'user_id' => User::factory(),
            'participant_id' => null,
            'event_id' => Event::factory(),
            'event_ticket_plan_id' => EventTicketPlan::factory(),
            'quantity' => 2,
            'unit_price_paise' => 29900,
            'total_amount_paise' => 59800,
            'currency' => 'INR',
            'billing_country_code' => 'IN',
            'display_currency' => 'INR',
            'display_unit_amount_minor' => 29900,
            'display_total_amount_minor' => 59800,
            'fx_rate_usd_to_inr' => null,
            'status' => EventBooking::STATUS_PENDING,
            'razorpay_order_id' => null,
            'razorpay_payment_id' => null,
            'razorpay_signature' => null,
            'failure_reason' => null,
            'paid_at' => null,
            'payment_payload' => null,
        ];
    }
}
