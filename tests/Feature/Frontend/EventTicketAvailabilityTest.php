<?php

namespace Tests\Feature\Frontend;

use App\Models\Event;
use App\Models\EventTicketPlan;
use App\Models\User;
use App\Support\FrontendPageAccessSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventTicketAvailabilityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ticket_page_is_not_available_for_past_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'start_at' => now()->subDays(5),
            'end_at' => now()->subDays(4),
            'is_active' => true,
            'is_published' => true,
        ]);

        $this->actingAs($user)
            ->get(route('frontend.events.tickets', $event->slug))
            ->assertRedirect(route('frontend.home'));
    }

    public function test_authenticated_user_can_only_book_upcoming_event(): void
    {
        $user = User::factory()->create();

        $upcomingEvent = Event::factory()->create([
            'start_at' => now()->addDays(10),
            'end_at' => now()->addDays(11),
            'is_active' => true,
            'is_published' => true,
        ]);

        $upcomingPlan = EventTicketPlan::factory()->create([
            'event_id' => $upcomingEvent->id,
            'is_active' => true,
            'sale_inr_paise' => 29900,
            'sale_usd_cents' => 499,
        ]);

        $pastEvent = Event::factory()->create([
            'start_at' => now()->subDays(10),
            'end_at' => now()->subDays(9),
            'is_active' => true,
            'is_published' => true,
        ]);

        $pastPlan = EventTicketPlan::factory()->create([
            'event_id' => $pastEvent->id,
            'is_active' => true,
            'sale_inr_paise' => 29900,
            'sale_usd_cents' => 499,
        ]);

        $payload = [
            'event_ticket_plan_id' => $upcomingPlan->id,
            'quantity' => 1,
            'billing_country_code' => 'IN',
            'billing_company_name' => 'Test Company',
            'billing_address_line_1' => 'Address 1',
            'billing_address_line_2' => '',
            'gstin' => '',
            'delegates' => [[
                'delegate_name' => 'Delegate One',
                'designation' => 'Manager',
                'company_name' => 'Test Company',
                'country' => 'India',
                'email' => 'delegate@example.com',
                'mobile' => '9999999999',
            ]],
        ];

        $this->actingAs($user)
            ->postJson(route('frontend.events.summary', $upcomingEvent->slug), $payload)
            ->assertOk()
            ->assertJsonPath('gst_charge_minor', 0);

        $payload['event_ticket_plan_id'] = $pastPlan->id;

        $this->actingAs($user)
            ->postJson(route('frontend.events.summary', $pastEvent->slug), $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Tickets are available only for the upcoming event.');
    }

    public function test_event_slug_route_redirects_to_tickets_case_insensitively(): void
    {
        $user = User::factory()->create();

        $event = Event::factory()->create([
            'slug' => 'igi-2027',
            'is_active' => true,
            'is_published' => true,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(6),
        ]);

        $this->actingAs($user)
            ->get(route('frontend.events.show', ['slug' => 'IGI-2027']))
            ->assertRedirect(route('frontend.events.tickets', ['event' => $event->slug]));
    }

    public function test_past_event_slug_route_redirects_to_events_listing_instead_of_ticket_404(): void
    {
        FrontendPageAccessSettings::set(FrontendPageAccessSettings::defaults());

        $user = User::factory()->create();
        $event = Event::factory()->create([
            'slug' => 'wipf2014',
            'is_published' => true,
            'is_active' => true,
            'start_at' => now()->subDays(30),
            'end_at' => now()->subDays(29),
        ]);

        $this->actingAs($user)
            ->get(route('frontend.events.show', ['slug' => strtoupper($event->slug)]))
            ->assertRedirect(route('frontend.events.index'));
    }

    public function test_published_event_is_visible_on_listing_even_if_legacy_active_flag_is_off(): void
    {
        FrontendPageAccessSettings::set(FrontendPageAccessSettings::defaults());

        $user = User::factory()->create();

        $event = Event::factory()->create([
            'title' => 'Legacy Published Event',
            'slug' => 'legacy-published-event',
            'is_published' => true,
            'is_active' => false,
            'start_at' => now()->addDays(7),
            'end_at' => now()->addDays(8),
        ]);

        $this->actingAs($user)
            ->get(route('frontend.events.index'))
            ->assertOk()
            ->assertSee($event->title);
    }

    public function test_events_listing_shows_latest_upcoming_and_past_events_first(): void
    {
        Carbon::setTestNow('2026-06-29 10:00:00');
        FrontendPageAccessSettings::set(FrontendPageAccessSettings::defaults());

        $user = User::factory()->create();

        $pastOlder = Event::factory()->create([
            'title' => 'Past Older Event',
            'start_at' => now()->subDays(14),
            'end_at' => now()->subDays(13),
            'is_active' => true,
            'is_published' => true,
        ]);

        $pastNewer = Event::factory()->create([
            'title' => 'Past Newer Event',
            'start_at' => now()->subDays(7),
            'end_at' => now()->subDays(6),
            'is_active' => true,
            'is_published' => true,
        ]);

        $upcomingOlder = Event::factory()->create([
            'title' => 'Upcoming Older Event',
            'start_at' => now()->addDays(7),
            'end_at' => now()->addDays(8),
            'is_active' => true,
            'is_published' => true,
        ]);

        $upcomingNewer = Event::factory()->create([
            'title' => 'Upcoming Newer Event',
            'start_at' => now()->addDays(12),
            'end_at' => now()->addDays(13),
            'is_active' => true,
            'is_published' => true,
        ]);

        $this->actingAs($user)
            ->get(route('frontend.events.index'))
            ->assertOk()
            ->assertSeeInOrder([
                $upcomingNewer->title,
                $pastNewer->title,
                $pastOlder->title,
            ])
            ->assertDontSee($upcomingOlder->title);

        Carbon::setTestNow();
    }

    public function test_ticket_page_uses_event_specific_hero_copy(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'slug' => 'igi-2027',
            'is_published' => true,
            'is_active' => true,
            'ticket_hero_label' => 'VIP BOOKING',
            'ticket_hero_suffix' => '/ choose your package',
            'ticket_hero_description' => 'Select your preferred pass, review the benefits, and move to secure checkout.',
            'start_at' => now()->addDays(8),
            'end_at' => now()->addDays(9),
        ]);

        EventTicketPlan::factory()->create([
            'event_id' => $event->id,
            'is_active' => true,
            'sale_inr_paise' => 29900,
            'sale_usd_cents' => 499,
        ]);

        $this->actingAs($user)
            ->get(route('frontend.events.tickets', $event->slug))
            ->assertOk()
            ->assertSee('VIP BOOKING')
            ->assertSee('/ choose your package')
            ->assertSee('Select your preferred pass, review the benefits, and move to secure checkout.');
    }

    public function test_ticket_page_hides_inactive_plans(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'is_published' => true,
            'is_active' => true,
            'start_at' => now()->addDays(8),
            'end_at' => now()->addDays(9),
        ]);

        EventTicketPlan::factory()->create([
            'event_id' => $event->id,
            'name' => 'Visible Plan',
            'plan_index' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        EventTicketPlan::factory()->create([
            'event_id' => $event->id,
            'name' => 'Hidden Plan',
            'plan_index' => 2,
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('frontend.events.tickets', $event->slug))
            ->assertOk()
            ->assertSee('Visible Plan')
            ->assertDontSee('Hidden Plan');
    }
}
