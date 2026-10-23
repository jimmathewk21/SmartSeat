<?php

namespace Tests\Feature\Frontend;

use App\Models\Event;
use App\Models\EventBooking;
use App\Models\EventTicketPlan;
use App\Models\User;
use App\Services\RazorpayTicketService;
use App\Support\PaymentGatewaySettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class EventBookingFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_user_can_create_event_order(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'is_active' => true,
            'is_published' => true,
        ]);
        $plan = EventTicketPlan::factory()->create([
            'event_id' => $event->id,
            'plan_index' => 1,
            'sort_order' => 1,
            'price_paise' => 29900,
            'sale_inr_paise' => 29900,
            'sale_usd_cents' => 499,
            'is_active' => true,
        ]);

        EventTicketPlan::factory()->create(['event_id' => $event->id, 'plan_index' => 2, 'sort_order' => 2, 'price_paise' => 49900, 'sale_inr_paise' => 49900, 'sale_usd_cents' => 699, 'is_active' => true, 'name' => 'Prime']);
        EventTicketPlan::factory()->create(['event_id' => $event->id, 'plan_index' => 3, 'sort_order' => 3, 'price_paise' => 69900, 'sale_inr_paise' => 69900, 'sale_usd_cents' => 899, 'is_active' => true, 'name' => 'Premium']);

        $booking = EventBooking::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_ticket_plan_id' => $plan->id,
            'booking_code' => 'BK-TEST-123',
            'quantity' => 2,
            'unit_price_paise' => 29900,
            'total_amount_paise' => 59800,
            'currency' => 'INR',
            'status' => EventBooking::STATUS_PENDING,
            'razorpay_order_id' => 'order_test_123',
        ]);

        $mock = Mockery::mock(RazorpayTicketService::class);
        $mock->shouldReceive('createOrderWithDetails')
            ->once()
            ->andReturn([
                'booking' => $booking,
                'order_id' => 'order_test_123',
                'amount' => 59800,
                'currency' => 'INR',
                'display_currency' => 'INR',
                'display_unit_amount_minor' => 29900,
                'display_total_amount_minor' => 59800,
                'subtotal_minor' => 59800,
                'promo_discount_minor' => 0,
                'gst_charge_minor' => 0,
                'gateway_charge_minor' => 0,
                'grand_total_minor' => 59800,
                'promo_code' => null,
                'billing_country_code' => 'IN',
                'fx_rate_usd_to_inr' => null,
                'key' => 'rzp_test_key',
                'name' => 'Event Management',
                'description' => 'AI Ticket',
            ]);
        $this->app->instance(RazorpayTicketService::class, $mock);

        $this->actingAs($user)
            ->postJson(route('frontend.events.orders', $event->slug), [
                'event_ticket_plan_id' => $plan->id,
                'quantity' => 2,
                'billing_country_code' => 'IN',
                'billing_company_name' => 'Acme Corp',
                'billing_address_line_1' => 'Line 1',
                'billing_address_line_2' => 'Line 2',
                'gstin' => '27ABCDE1234F1Z5',
                'delegates' => [
                    [
                        'delegate_name' => 'Delegate One',
                        'designation' => 'Manager',
                        'company_name' => 'Acme Corp',
                        'country' => 'India',
                        'email' => 'delegate1@example.com',
                        'mobile' => '9999999991',
                    ],
                    [
                        'delegate_name' => 'Delegate Two',
                        'designation' => 'Lead',
                        'company_name' => 'Acme Corp',
                        'country' => 'India',
                        'email' => 'delegate2@example.com',
                        'mobile' => '9999999992',
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('currency', 'INR')
            ->assertJsonPath('amount', 59800)
            ->assertJsonPath('booking_code', 'BK-TEST-123');
    }

    public function test_my_tickets_page_requires_authentication(): void
    {
        $this->get(route('my-tickets.index'))
            ->assertRedirect(route('login'));
    }

    public function test_event_order_creation_is_blocked_when_payment_gateway_is_turned_off(): void
    {
        PaymentGatewaySettings::set(['enabled' => false]);

        $user = User::factory()->create();
        $event = Event::factory()->create([
            'is_active' => true,
            'is_published' => true,
        ]);
        $plan = EventTicketPlan::factory()->create([
            'event_id' => $event->id,
            'plan_index' => 1,
            'sort_order' => 1,
            'price_paise' => 29900,
            'sale_inr_paise' => 29900,
            'sale_usd_cents' => 499,
            'is_active' => true,
        ]);

        $payload = [
            'event_ticket_plan_id' => $plan->id,
            'quantity' => 1,
            'billing_country_code' => 'IN',
            'billing_company_name' => 'Acme Corp',
            'billing_address_line_1' => 'Line 1',
            'billing_address_line_2' => '',
            'gstin' => '',
            'delegates' => [[
                'delegate_name' => 'Delegate One',
                'designation' => 'Manager',
                'company_name' => 'Acme Corp',
                'country' => 'India',
                'email' => 'delegate1@example.com',
                'mobile' => '9999999991',
            ]],
        ];

        $this->actingAs($user)
            ->postJson(route('frontend.events.orders', $event->slug), $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Online payment is currently disabled.');

        $this->assertDatabaseMissing('event_bookings', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_ticket_plan_id' => $plan->id,
        ]);
    }
}
