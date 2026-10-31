<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Station;
use App\Models\Train;
use App\Models\User;
use App\Services\Intelligence\WaitlistPrediction;
use Illuminate\Support\Facades\Storage;
use Tests\RailwayTestCase;

class ReleaseIntegrationTest extends RailwayTestCase
{
    private function registerPassenger(string $email): User
    {
        $this->post(route('register.store'), ['name' => 'Isolated integration passenger', 'email' => $email,
            'password' => 'JourneyAhead!42', 'password_confirmation' => 'JourneyAhead!42', 'role' => 'administrator'])
            ->assertRedirect(route('dashboard'));
        $user = User::where('email', $email)->firstOrFail();
        $this->assertFalse($user->isAdministrator());
        $this->get(route('administrator.dashboard'))->assertForbidden();

        return $user;
    }

    private function bookThroughPages($trip, int $count, string $mode): Booking
    {
        $this->get(route('bookings.create', ['trip' => $trip, 'count' => $count]))->assertOk();
        $key = array_key_last(session('booking_drafts'));
        $this->post(route('booking-drafts.details', $trip), [
            'draft_key' => $key, 'boarding_order' => 1, 'alighting_order' => 2, 'travel_class' => 'SL',
            'keep_group_together' => 1,
            'passengers' => array_fill(0, $count, ['name' => 'Isolated passenger', 'age' => 65,
                'gender' => 'prefer_not_to_say', 'preferred_berth' => 'lower', 'accessible_seat_required' => 1]),
        ])->assertRedirect(route('booking-drafts.seats', $key));
        $this->get(route('booking-drafts.seats', $key))->assertOk()->assertSee('Recommended seats')
            ->assertSee('Suggestions never replace your selection automatically.');
        $selection = ['mode' => $mode];
        if ($mode === 'reserve') {
            $selection['seats'] = $trip->inventory()->orderBy('id')->pluck('id')->all();
        }
        $this->post(route('booking-drafts.review.store', $key), $selection)
            ->assertRedirect(route('booking-drafts.review', $key));
        $this->get(route('booking-drafts.review', $key))->assertOk()->assertSee('No payment is collected.');
        $token = session('booking_drafts.'.$key.'.review.token');
        $this->post(route('booking-drafts.confirm', $key), ['review_token' => $token])->assertRedirect();
        $booking = Booking::where('submission_key', $key)->firstOrFail();
        $this->post(route('booking-drafts.confirm', $key), ['review_token' => $token])
            ->assertRedirect(route('bookings.show', $booking));
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => $mode === 'reserve' ? 'confirmed' : 'waitlisted']);

        return $booking;
    }

    public function test_admin_configuration_search_group_booking_and_fifo_promotion_work_together(): void
    {
        Storage::fake('local');
        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin, 'web');
        foreach (['AAA', 'BBB'] as $code) {
            $this->post(route('administrator.stations.store'), ['code' => $code, 'name' => 'Isolated station '.$code, 'city' => 'Fixture city'])->assertRedirect();
        }
        $this->post(route('administrator.trains.store'), ['number' => '00001', 'name' => 'Isolated integration train', 'is_active' => 1])->assertRedirect();
        $train = Train::firstOrFail();
        foreach (Station::orderBy('id')->get() as $i => $station) {
            $this->post(route('administrator.trains.stops.store', $train), ['station_code' => $station->code,
                'stop_order' => $i + 1, 'arrival_offset_minutes' => 480 + 120 * $i, 'departure_offset_minutes' => 485 + 120 * $i])->assertRedirect();
        }
        $this->post(route('administrator.trains.coaches.store', $train), ['label' => 'S1', 'travel_class' => 'SL'])->assertRedirect();
        $coach = $train->coaches()->firstOrFail();
        foreach ([1, 2] as $number) {
            $this->post(route('administrator.trains.coaches.seats.store', [$train, $coach]), ['number' => $number,
                'berth_type' => 'lower', 'accessibility_status' => 'accessible'])->assertRedirect();
        }
        $date = now()->addDays(3)->toDateString();
        $this->post(route('administrator.trains.trips.store', $train), ['service_date' => $date])->assertRedirect();
        $trip = $train->trips()->firstOrFail();
        $this->post(route('administrator.inventory.store', $trip))->assertRedirect();
        $this->post(route('administrator.inventory.store', $trip))->assertRedirect();
        $this->assertDatabaseCount('trip_seat_inventory', 2);
        $this->post(route('logout'))->assertRedirect();
        $this->get(route('trains.search', ['origin' => 'AAA', 'destination' => 'BBB', 'journey_date' => $date]))
            ->assertOk()->assertSee('Isolated integration train')->assertSee('2 of 2 seats available');

        $this->registerPassenger('first@example.test');
        $reserved = $this->bookThroughPages($trip, 2, 'reserve');
        $this->post(route('logout'))->assertRedirect();
        $this->registerPassenger('second@example.test');
        $firstWaiting = $this->bookThroughPages($trip, 2, 'waitlist');
        $this->get(route('bookings.show', $firstWaiting))->assertOk()->assertSee(WaitlistPrediction::UNAVAILABLE)
            ->assertDontSee('% estimated chance');
        $this->get(route('bookings.show', $reserved))->assertForbidden();
        $this->post(route('bookings.cancel', $reserved))->assertForbidden();
        $this->post(route('logout'))->assertRedirect();
        $this->registerPassenger('third@example.test');
        $lastWaiting = $this->bookThroughPages($trip, 1, 'waitlist');

        $this->post(route('logout'))->assertRedirect();
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])->assertRedirect();
        // Factory password is used only in the guarded, disposable in-memory fixture.
        $this->get(route('administrator.intelligence.index'))->assertOk()->assertSee(WaitlistPrediction::UNAVAILABLE);
        $this->get(route('bookings.cancellation', $reserved))->assertOk();
        $this->post(route('bookings.cancel', $reserved))->assertRedirect(route('bookings.show', $reserved));
        $this->assertDatabaseHas('bookings', ['id' => $firstWaiting->id, 'status' => 'confirmed']);
        $this->assertDatabaseHas('bookings', ['id' => $lastWaiting->id, 'status' => 'waitlisted']);
        $this->assertDatabaseHas('booking_events', ['booking_id' => $reserved->id, 'actor_id' => $admin->id, 'action' => 'cancelled']);
        $this->post(route('bookings.cancel', $reserved))->assertRedirect();
        $this->assertSame(1, $firstWaiting->events()->where('action', 'promoted')->count());
        $this->post(route('bookings.cancel', $firstWaiting))->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $lastWaiting->id, 'status' => 'confirmed']);
        $this->assertDatabaseCount('bookings', 3);
        $this->assertDatabaseCount('passengers', 5);
        Storage::disk('local')->assertMissing('intelligence/waitlist/active.json');
    }
}
