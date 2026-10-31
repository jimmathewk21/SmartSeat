<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\BookingTestCase;

class BookingWorkflowTest extends BookingTestCase
{
    private function draft(array $journey, ?User $owner = null): string
    {
        $this->actingAs($owner ?? User::factory()->create(), 'web');
        $this->get(route('bookings.create', $journey['trip']))->assertOk()->assertSee('Who is coming along?');

        return array_key_last(session('booking_drafts'));
    }

    private function reviewed(array $journey, string $key, string $mode = 'reserve'): string
    {
        $this->post(route('booking-drafts.details', $journey['trip']), $this->input() + ['draft_key' => $key])
            ->assertRedirect(route('booking-drafts.seats', $key));
        $this->get(route('booking-drafts.seats', $key))->assertOk()->assertSee('A place for everyone.');
        $payload = ['mode' => $mode];
        if ($mode === 'reserve') {
            $payload['seats'] = [$journey['trip']->inventory()->orderBy('id')->value('id')];
        }
        $this->post(route('booking-drafts.review.store', $key), $payload)->assertRedirect(route('booking-drafts.review', $key));
        $this->get(route('booking-drafts.review', $key))->assertOk()->assertSee('Private Passenger 1');

        return session('booking_drafts.'.$key.'.review.token');
    }

    public function test_full_wizard_and_duplicate_submit_use_reviewed_server_data(): void
    {
        $journey = $this->journey();
        $key = $this->draft($journey);
        $token = $this->reviewed($journey, $key);
        $confirmation = ['review_token' => $token, 'user_id' => 9999, 'seats' => [99999], 'passengers' => [['name' => 'Tampered']]];
        $this->post(route('booking-drafts.confirm', $key), $confirmation)->assertRedirect();
        $booking = Booking::firstOrFail();
        $this->assertDatabaseHas('passengers', ['booking_id' => $booking->id, 'name' => 'Private Passenger 1']);
        $this->assertDatabaseMissing('passengers', ['name' => 'Tampered']);
        $this->get(route('bookings.show', $booking))->assertOk()->assertSee('Your reservation is confirmed.')->assertSee($booking->reference)->assertSee('S1 / 1');
        $this->post(route('booking-drafts.confirm', $key), $confirmation)->assertRedirect(route('bookings.show', $booking));
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('seat_assignments', 1);
        $this->get(route('bookings.index'))->assertOk()->assertSee($journey['train']->number);
    }

    public function test_waitlist_review_status_and_cancellation_pages_render(): void
    {
        $journey = $this->journey();
        $this->reserve($journey);
        $owner = User::factory()->create();
        $key = $this->draft($journey, $owner);
        $token = $this->reviewed($journey, $key, 'waitlist');
        $this->post(route('booking-drafts.confirm', $key), ['review_token' => $token])->assertRedirect();
        $waiting = $owner->bookings()->firstOrFail();
        $this->get(route('bookings.show', $waiting))->assertOk()->assertSee('Your group is on the waitlist.')->assertSee('Waitlist request 1');
        $this->get(route('bookings.cancellation', $waiting))->assertOk()->assertSee('Cancel this booking?');
        $this->post(route('bookings.cancel', $waiting))->assertRedirect();
        $this->get(route('bookings.show', $waiting))->assertOk()->assertSee('Cancelled');
        $this->assertDatabaseHas('bookings', ['id' => $waiting->id, 'status' => 'cancelled']);
    }

    public function test_other_users_cannot_read_or_cancel_booking_or_see_passenger_data(): void
    {
        $journey = $this->journey();
        $booking = $this->reserve($journey);
        $this->actingAs(User::factory()->create(), 'web');
        foreach (['bookings.show', 'bookings.cancellation'] as $route) {
            $this->get(route($route, $booking))->assertForbidden()->assertDontSee('Private Passenger');
        }
        $this->post(route('bookings.cancel', $booking))->assertForbidden();
        $this->get(route('bookings.index'))->assertOk()->assertSee('No bookings yet')->assertDontSee($booking->reference);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
    }

    public function test_administrator_can_cancel_with_an_audited_actor(): void
    {
        $journey = $this->journey();
        $booking = $this->reserve($journey);
        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin, 'web')->get(route('bookings.show', $booking))->assertOk();
        $this->post(route('bookings.cancel', $booking))->assertRedirect();
        $this->assertDatabaseHas('booking_events', ['booking_id' => $booking->id, 'actor_id' => $admin->id, 'action' => 'cancelled']);
    }

    public function test_authentication_and_csrf_are_required(): void
    {
        $journey = $this->journey();
        $this->get(route('bookings.create', $journey['trip']))->assertRedirect(route('login'));
        $this->get(route('bookings.index'))->assertRedirect(route('login'));
        $key = $this->draft($journey);
        $this->app['env'] = 'local';
        $this->post(route('booking-drafts.details', $journey['trip']), $this->input() + ['draft_key' => $key])->assertStatus(419);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_drafts_are_owner_bound_and_expire(): void
    {
        $journey = $this->journey();
        $owner = User::factory()->create();
        $key = $this->draft($journey, $owner);
        $this->actingAs(User::factory()->create(), 'web');
        $this->post(route('booking-drafts.details', $journey['trip']), $this->input() + ['draft_key' => $key])->assertNotFound();
        $this->actingAs($owner, 'web');
        $this->travel(31)->minutes();
        $this->get(route('booking-drafts.seats', $key))->assertNotFound();
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_confirmation_requires_review_and_rejects_stale_tokens(): void
    {
        $journey = $this->journey();
        $key = $this->draft($journey);
        $this->post(route('booking-drafts.confirm', $key))->assertStatus(422);
        $oldToken = $this->reviewed($journey, $key);
        $newToken = $this->reviewed($journey, $key);
        $this->assertNotSame($oldToken, $newToken);
        $this->post(route('booking-drafts.confirm', $key), ['review_token' => $oldToken])->assertSessionHasErrors('review_token');
        $this->post(route('booking-drafts.confirm', $key), ['review_token' => (string) Str::uuid()])->assertSessionHasErrors('review_token');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_seat_manipulation_and_stale_availability_cannot_confirm(): void
    {
        $journey = $this->journey();
        $other = $this->journey();
        $key = $this->draft($journey);
        $this->post(route('booking-drafts.details', $journey['trip']), $this->input() + ['draft_key' => $key])->assertRedirect();
        $this->post(route('booking-drafts.review.store', $key), ['mode' => 'reserve', 'seats' => [$other['trip']->inventory()->value('id')]])
            ->assertSessionHasErrors('seats');
        $token = $this->reviewed($journey, $key);
        $this->reserve($journey);
        $this->post(route('booking-drafts.confirm', $key), ['review_token' => $token])->assertSessionHasErrors('seats');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_invalid_passenger_data_and_manipulated_coaches_are_rejected(): void
    {
        $journey = $this->journey();
        $key = $this->draft($journey);
        $details = $this->input();
        $details['passengers'][0]['age'] = -1;
        $this->post(route('booking-drafts.details', $journey['trip']), $details + ['draft_key' => $key])->assertSessionHasErrors('passengers.0.age');
        $this->post(route('booking-drafts.details', $journey['trip']), $this->input() + ['draft_key' => $key])->assertRedirect();
        $this->get(route('booking-drafts.seats', ['draft' => $key, 'coach_ids' => [99999]]))->assertSessionHasErrors('coach_ids.0');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_multiple_passenger_details_and_empty_inventory_screen_render(): void
    {
        $journey = $this->journey();
        $this->actingAs(User::factory()->create(), 'web')->get(route('bookings.create', ['trip' => $journey['trip'], 'count' => 2]))
            ->assertOk()->assertSee('Passenger 2');
        $key = array_key_last(session('booking_drafts'));
        $this->post(route('booking-drafts.details', $journey['trip']), $this->input(2) + ['draft_key' => $key])->assertRedirect();
        $journey['trip']->inventory()->delete();
        $this->get(route('booking-drafts.seats', $key))->assertOk()->assertSee('No seat inventory has been initialized')->assertSee('Join the waitlist.');
    }

    public function test_empty_booking_history_does_not_create_any_records(): void
    {
        $this->actingAs(User::factory()->create(), 'web')->get(route('bookings.index'))->assertOk()->assertSee('No bookings yet');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('passengers', 0);
    }

    public function test_history_is_paginated_and_malformed_coach_keys_do_not_cause_server_errors(): void
    {
        $journey = $this->journey();
        $owner = User::factory()->create();
        $key = $this->draft($journey, $owner);
        $this->post(route('booking-drafts.details', $journey['trip']), $this->input() + ['draft_key' => $key])->assertRedirect();
        $this->get(route('booking-drafts.seats', ['draft' => $key, 'coach_ids' => ['wrong' => $journey['coach']->id]]))->assertSessionHasErrors('coach_ids');
        Booking::factory()->count(21)->create(['user_id' => $owner->id, 'trip_id' => $journey['trip']->id]);
        $this->get(route('bookings.index'))->assertOk()->assertSee('Page 1 of 2');
    }
}
