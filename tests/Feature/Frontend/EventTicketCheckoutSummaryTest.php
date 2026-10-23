<?php

namespace Tests\Feature\Frontend;

use App\Models\Event;
use App\Models\EventTicketPlan;
use App\Models\PromoCode;
use App\Models\User;
use App\Support\TicketCheckoutSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventTicketCheckoutSummaryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_created_promo_code_is_applied_in_ticket_summary(): void
    {
        TicketCheckoutSettings::set([
            'gateway_charge_type' => 'none',
            'gateway_charge_percent' => 0,
            'gateway_charge_inr' => '0.00',
            'gateway_charge_usd' => '0.00',
            'min_ticket_quantity' => 1,
            'max_ticket_quantity' => 10,
            'plan_caption' => TicketCheckoutSettings::defaults()['plan_caption'],
        ]);

        $user = User::factory()->create();
        $event = Event::factory()->create([
            'is_active' => true,
            'is_published' => true,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(11),
        ]);
        $plan = EventTicketPlan::factory()->create([
            'event_id' => $event->id,
            'is_active' => true,
            'sale_inr_paise' => 300000,
            'sale_usd_cents' => 4000,
        ]);

        PromoCode::query()->create([
            'code' => 'SAVE10',
            'name' => 'Save Ten',
            'discount_type' => 'percentage',
            'discount_percent' => 10,
            'flat_inr_paise' => 0,
            'flat_usd_cents' => 0,
            'quantity_buy' => 0,
            'quantity_pay' => 0,
            'max_uses' => 100,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('frontend.events.summary', $event->slug), [
                'event_ticket_plan_id' => $plan->id,
                'quantity' => 2,
                'billing_country_code' => 'IN',
                'preferred_currency' => 'INR',
                'promo_code' => 'SAVE10',
            ])
            ->assertOk()
            ->assertJsonPath('display_currency', 'INR')
            ->assertJsonPath('subtotal_minor', 600000)
            ->assertJsonPath('promo_discount_minor', 60000)
            ->assertJsonPath('promo_code', 'SAVE10')
            ->assertJsonPath('gateway_charge_minor', 0)
            ->assertJsonPath('grand_total_minor', 540000);
    }

    public function test_flat_gateway_charge_switches_between_inr_and_usd(): void
    {
        TicketCheckoutSettings::set([
            'gateway_charge_type' => 'flat',
            'gateway_charge_percent' => 0,
            'gateway_charge_inr' => '18.00',
            'gateway_charge_usd' => '4.50',
            'min_ticket_quantity' => 1,
            'max_ticket_quantity' => 10,
            'plan_caption' => TicketCheckoutSettings::defaults()['plan_caption'],
        ]);

        $user = User::factory()->create();
        $event = Event::factory()->create([
            'is_active' => true,
            'is_published' => true,
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(11),
        ]);
        $plan = EventTicketPlan::factory()->create([
            'event_id' => $event->id,
            'is_active' => true,
            'sale_inr_paise' => 29900,
            'sale_usd_cents' => 499,
        ]);

        $this->actingAs($user)
            ->postJson(route('frontend.events.summary', $event->slug), [
                'event_ticket_plan_id' => $plan->id,
                'quantity' => 1,
                'billing_country_code' => 'IN',
                'preferred_currency' => 'INR',
            ])
            ->assertOk()
            ->assertJsonPath('display_currency', 'INR')
            ->assertJsonPath('gateway_charge_minor', 1800)
            ->assertJsonPath('grand_total_minor', 31700);

        $this->actingAs($user)
            ->postJson(route('frontend.events.summary', $event->slug), [
                'event_ticket_plan_id' => $plan->id,
                'quantity' => 1,
                'billing_country_code' => 'US',
                'preferred_currency' => 'USD',
            ])
            ->assertOk()
            ->assertJsonPath('display_currency', 'USD')
            ->assertJsonPath('gateway_charge_minor', 450)
            ->assertJsonPath('grand_total_minor', 949);
    }
}
