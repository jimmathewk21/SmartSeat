<?php

namespace Tests\Feature\Train;

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainBooking;
use App\Models\TrainBookingPassenger;
use App\Models\TrainBookingSeat;
use App\Models\TrainCoach;
use App\Models\TrainRouteStop;
use App\Models\TrainSeat;
use App\Models\TrainTrip;
use App\Models\TrainWaitlistEntry;
use App\Models\TripSeatInventory;
use App\Models\User;
use App\Services\TrainBookingService;
use App\Services\TrainInventoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TrainBookingWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_search_and_book_an_available_trip_seat(): void
    {
        [$train, $trip, $from, $to, $inventory] = $this->makeTrip();
        $user = User::factory()->create();

        $this->get(route('frontend.trains.search', [
            'from_station_id' => $from->id,
            'to_station_id' => $to->id,
            'date' => $trip->starts_at->toDateString(),
        ]))->assertOk()->assertSee($train->name)->assertSee($from->name)->assertSee($to->name);

        $response = $this->actingAs($user)->post(route('frontend.trains.book', $trip), [
            ...$this->journeyFields($trip),
            'inventory_ids' => [$inventory->id],
            'passengers' => [['name' => 'Riya Passenger', 'age' => 28]],
        ]);

        $response->assertRedirect(route('frontend.trains.bookings'));
        $booking = TrainBooking::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $inventory->id,
            'status' => TripSeatInventory::STATUS_BOOKED,
            'current_booking_id' => $booking->id,
        ]);
        $this->assertDatabaseHas('train_booking_seats', [
            'train_booking_id' => $booking->id,
            'trip_seat_inventory_id' => $inventory->id,
        ]);
        $this->get(route('frontend.trains.bookings'))
            ->assertOk()
            ->assertSee($booking->booking_code)
            ->assertSee('Riya Passenger');
    }

    public function test_sequential_duplicate_booking_attempts_cannot_confirm_the_same_seat(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $service = app(TrainBookingService::class);
        $service->book($firstUser, $trip->id, [$inventory->id], [['name' => 'First Passenger']], ...$this->stationIds($trip));

        $this->actingAs($secondUser)
            ->from(route('frontend.trains.search'))
            ->post(route('frontend.trains.book', $trip), [
                ...$this->journeyFields($trip),
                'inventory_ids' => [$inventory->id],
                'passengers' => [['name' => 'Second Passenger']],
            ])
            ->assertSessionHasErrors('seats');

        $this->assertSame(1, TrainBookingSeat::query()->where('trip_seat_inventory_id', $inventory->id)->whereNull('released_at')->count());
    }

    public function test_trip_inventory_initialization_is_idempotent(): void
    {
        [$train, $trip] = $this->makeTrip();
        $coach = TrainCoach::factory()->for($train)->create(['code' => 'EXTRA']);
        TrainSeat::factory()->for($train)->for($coach, 'coach')->create(['number' => '99']);
        $service = app(TrainInventoryService::class);

        $this->assertSame(1, $service->initializeForTrip($trip));
        $this->assertSame(0, $service->initializeForTrip($trip));
        $this->assertSame(2, TripSeatInventory::query()->where('train_trip_id', $trip->id)->count());
    }

    public function test_waitlist_owner_can_cancel_their_waiting_entry(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $owner = User::factory()->create();
        $user = User::factory()->create();
        app(TrainBookingService::class)->book($owner, $trip->id, [$inventory->id], [['name' => 'Booked Passenger']], ...$this->stationIds($trip));
        $entry = app(TrainBookingService::class)->joinWaitlist($user, $trip->id, [['name' => 'Waiting Passenger']], ...$this->stationIds($trip));

        $this->actingAs($user)
            ->post(route('frontend.trains.waitlist.cancel', $entry))
            ->assertRedirect(route('frontend.trains.bookings'));

        $this->assertDatabaseHas('train_waitlist_entries', [
            'id' => $entry->id,
            'status' => TrainWaitlistEntry::STATUS_CANCELLED,
        ]);
    }

    public function test_accessibility_request_requires_an_accessible_seat(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip(accessible: false);

        $this->actingAs(User::factory()->create())
            ->from(route('frontend.trains.search'))
            ->post(route('frontend.trains.book', $trip), [
                ...$this->journeyFields($trip),
                'inventory_ids' => [$inventory->id],
                'passengers' => [['name' => 'Accessible Passenger', 'requires_accessible_seat' => true]],
            ])
            ->assertSessionHasErrors('seats');

        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $inventory->id,
            'status' => TripSeatInventory::STATUS_AVAILABLE,
            'current_booking_id' => null,
        ]);
    }

    public function test_cancellation_releases_seat_and_promotes_waitlist_in_deterministic_order(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $owner = User::factory()->create();
        $firstWaiter = User::factory()->create();
        $secondWaiter = User::factory()->create();
        $service = app(TrainBookingService::class);
        $booking = $service->book($owner, $trip->id, [$inventory->id], [['name' => 'Booked Passenger']], ...$this->stationIds($trip));

        $firstEntry = $service->joinWaitlist($firstWaiter, $trip->id, [['name' => 'First Waiter']], ...$this->stationIds($trip));
        $secondEntry = $service->joinWaitlist($secondWaiter, $trip->id, [['name' => 'Second Waiter']], ...$this->stationIds($trip));
        $firstEntry->forceFill(['created_at' => now()->subMinute()])->save();
        $secondEntry->forceFill(['created_at' => now()])->save();

        $service->cancel($owner, $booking->id);

        $this->assertDatabaseHas('train_waitlist_entries', [
            'id' => $firstEntry->id,
            'status' => TrainWaitlistEntry::STATUS_PROMOTED,
        ]);
        $this->assertDatabaseHas('train_waitlist_entries', [
            'id' => $secondEntry->id,
            'status' => TrainWaitlistEntry::STATUS_WAITING,
        ]);
        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $inventory->id,
            'status' => TripSeatInventory::STATUS_BOOKED,
            'current_booking_id' => $firstEntry->fresh()->promoted_booking_id,
        ]);
    }

    public function test_booking_seat_cannot_reference_inventory_from_another_trip(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $otherTrip = TrainTrip::factory()->for($trip->train)->create();
        $otherInventory = TripSeatInventory::factory()->for($otherTrip, 'trip')->for($inventory->seat, 'seat')->create(['train_id' => $trip->train_id]);
        $booking = app(TrainBookingService::class)->book(User::factory()->create(), $otherTrip->id, [$otherInventory->id], [['name' => 'Passenger']], ...$this->stationIds($otherTrip));

        try {
            DB::table('train_booking_seats')->insert([
                'train_booking_id' => $booking->id,
                'train_trip_id' => $otherTrip->id,
                'trip_seat_inventory_id' => $inventory->id,
                'train_booking_passenger_id' => $booking->passengers()->first()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->fail('A cross-trip inventory assignment should violate its composite foreign key.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    public function test_booking_seat_cannot_reference_passenger_from_another_booking(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $service = app(TrainBookingService::class);
        $first = $service->book(User::factory()->create(), $trip->id, [$inventory->id], [['name' => 'First Passenger']], ...$this->stationIds($trip));
        $secondSeat = TrainSeat::factory()->for($trip->train)->for($inventory->seat->coach, 'coach')->create();
        $secondInventory = TripSeatInventory::factory()->for($trip, 'trip')->for($secondSeat, 'seat')->create(['train_id' => $trip->train_id]);
        $second = $service->book(User::factory()->create(), $trip->id, [$secondInventory->id], [['name' => 'Second Passenger']], ...$this->stationIds($trip));
        $thirdSeat = TrainSeat::factory()->for($trip->train)->for($inventory->seat->coach, 'coach')->create();
        $unassignedInventory = TripSeatInventory::factory()->for($trip, 'trip')->for($thirdSeat, 'seat')->create(['train_id' => $trip->train_id]);

        try {
            DB::table('train_booking_seats')->insert([
                'train_booking_id' => $first->id,
                'train_trip_id' => $trip->id,
                'trip_seat_inventory_id' => $unassignedInventory->id,
                'train_booking_passenger_id' => $second->passengers()->first()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->fail('A passenger from another booking should violate its composite foreign key.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    public function test_database_prevents_two_active_assignments_for_one_inventory_row(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $service = app(TrainBookingService::class);
        $first = $service->book(User::factory()->create(), $trip->id, [$inventory->id], [['name' => 'First Passenger']], ...$this->stationIds($trip));
        $second = TrainBooking::create([
            'booking_code' => 'TR-SECOND-' . $trip->id,
            'user_id' => User::factory()->create()->id,
            'train_trip_id' => $trip->id,
            'status' => TrainBooking::STATUS_CONFIRMED,
            'seat_count' => 1,
            'confirmed_at' => now(),
        ]);
        $passenger = TrainBookingPassenger::create(['train_booking_id' => $second->id, 'name' => 'Second Passenger']);

        try {
            DB::table('train_booking_seats')->insert([
                'train_booking_id' => $second->id,
                'train_trip_id' => $trip->id,
                'trip_seat_inventory_id' => $inventory->id,
                'train_booking_passenger_id' => $passenger->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->fail('Only one unreleased assignment may reference an inventory row.');
        } catch (QueryException) {
            $this->assertDatabaseHas('train_booking_seats', ['train_booking_id' => $first->id, 'trip_seat_inventory_id' => $inventory->id]);
        }
    }

    public function test_database_rejects_inventory_status_and_booking_pointer_mismatch(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $booking = app(TrainBookingService::class)->book(User::factory()->create(), $trip->id, [$inventory->id], [['name' => 'Passenger']], ...$this->stationIds($trip));
        $inventory->refresh();

        $this->assertSame(TripSeatInventory::STATUS_BOOKED, $inventory->status);
        $this->assertSame((int) $booking->id, (int) $inventory->current_booking_id);

        try {
            $inventory->update(['status' => TripSeatInventory::STATUS_AVAILABLE]);
            $this->fail('A booked inventory row cannot have available status.');
        } catch (QueryException) {
            $this->assertDatabaseHas('trip_seat_inventories', [
                'id' => $inventory->id,
                'status' => TripSeatInventory::STATUS_BOOKED,
                'current_booking_id' => $booking->id,
            ]);
        }

        $available = $this->makeTrip()[4];
        try {
            $available->update(['status' => TripSeatInventory::STATUS_BOOKED]);
            $this->fail('A booked inventory row must have a booking pointer.');
        } catch (QueryException) {
            $this->assertDatabaseHas('trip_seat_inventories', [
                'id' => $available->id,
                'status' => TripSeatInventory::STATUS_AVAILABLE,
                'current_booking_id' => null,
            ]);
        }
    }

    public function test_users_can_only_view_and_cancel_their_own_bookings(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $booking = app(TrainBookingService::class)->book($owner, $trip->id, [$inventory->id], [['name' => 'Owner Passenger']], ...$this->stationIds($trip));

        $this->actingAs($otherUser)->get(route('frontend.trains.bookings'))->assertOk()->assertDontSee($booking->booking_code);
        $this->actingAs($otherUser)->post(route('frontend.trains.cancel', $booking))->assertNotFound();
        $this->assertDatabaseHas('train_bookings', ['id' => $booking->id, 'status' => TrainBooking::STATUS_CONFIRMED]);
    }

    public function test_booking_failure_rolls_back_booking_and_inventory_changes(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $user = User::factory()->create();
        $failureInjected = false;
        DB::listen(function ($query) use ($user, &$failureInjected) {
            $sql = strtolower($query->sql);
            if (! $failureInjected
                && str_starts_with($sql, 'insert into')
                && str_contains($sql, 'train_bookings')
                && in_array((string) $user->id, array_map('strval', $query->bindings), true)) {
                $failureInjected = true;
                throw new \RuntimeException('Simulated booking persistence failure.');
            }
        });

        try {
            app(TrainBookingService::class)->book($user, $trip->id, [$inventory->id], [['name' => 'Valid Passenger']], ...$this->stationIds($trip));
            $this->fail('The injected persistence failure should abort the booking transaction.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated booking persistence failure.', $exception->getMessage());
        }

        $this->assertTrue($failureInjected, 'The failure must occur after the booking INSERT has executed.');
        $this->assertSame(0, TrainBooking::query()->where('user_id', $user->id)->where('train_trip_id', $trip->id)->count());
        $this->assertSame(0, TrainBookingPassenger::query()->whereHas('booking', fn ($query) => $query->where('user_id', $user->id))->count());
        $this->assertSame(0, TrainBookingSeat::query()->where('train_trip_id', $trip->id)->count());
        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $inventory->id,
            'status' => TripSeatInventory::STATUS_AVAILABLE,
            'current_booking_id' => null,
        ]);
    }

    public function test_booking_and_waitlist_reject_passengers_without_names(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $user = User::factory()->create();
        $service = app(TrainBookingService::class);

        try {
            $service->book($user, $trip->id, [$inventory->id], [['age' => 28]], ...$this->stationIds($trip));
            $this->fail('Booking requires a passenger name.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('passengers.0.name', $exception->errors());
        }

        try {
            $service->joinWaitlist($user, $trip->id, [['age' => 28]], ...$this->stationIds($trip));
            $this->fail('Waitlist entries require a passenger name.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('passengers.0.name', $exception->errors());
        }

        $this->assertSame(0, TrainBooking::query()->where('user_id', $user->id)->where('train_trip_id', $trip->id)->count());
        $this->assertSame(0, TrainWaitlistEntry::query()->where('user_id', $user->id)->where('train_trip_id', $trip->id)->count());
        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $inventory->id,
            'status' => TripSeatInventory::STATUS_AVAILABLE,
            'current_booking_id' => null,
        ]);
    }

    public function test_waitlist_promotion_failure_rolls_back_cancellation_and_seat_release(): void
    {
        [, $trip, , , $inventory] = $this->makeTrip();
        $owner = User::factory()->create();
        $waiter = User::factory()->create();
        $service = app(TrainBookingService::class);
        $booking = $service->book($owner, $trip->id, [$inventory->id], [['name' => 'Booked Passenger']], ...$this->stationIds($trip));
        $entry = $service->joinWaitlist($waiter, $trip->id, [['name' => 'Waiting Passenger']], ...$this->stationIds($trip));
        $shouldFail = true;
        DB::listen(function ($query) use ($waiter, &$shouldFail) {
            $sql = strtolower($query->sql);
            if ($shouldFail && str_starts_with($sql, 'insert into') && str_contains($sql, 'train_bookings') && in_array((string) $waiter->id, array_map('strval', $query->bindings), true)) {
                $shouldFail = false;
                throw new \RuntimeException('Simulated promotion booking failure.');
            }
        });

        try {
            $service->cancel($owner, $booking->id);
            $this->fail('Promotion failure should abort cancellation.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated promotion booking failure.', $exception->getMessage());
            $this->assertDatabaseHas('train_bookings', ['id' => $booking->id, 'status' => TrainBooking::STATUS_CONFIRMED]);
            $this->assertDatabaseHas('train_waitlist_entries', ['id' => $entry->id, 'status' => TrainWaitlistEntry::STATUS_WAITING]);
            $this->assertDatabaseHas('trip_seat_inventories', ['id' => $inventory->id, 'status' => TripSeatInventory::STATUS_BOOKED, 'current_booking_id' => $booking->id]);
        }
    }

    public function test_search_finds_trip_that_started_the_previous_day_before_origin_departure(): void
    {
        [$train, $trip, $from, $to] = $this->makeTrip();
        $tripStart = now()->addDays(4)->startOfDay()->setTime(22, 0);
        $trip->update(['starts_at' => $tripStart, 'ends_at' => $tripStart->copy()->addHours(30)]);
        $origin = TrainRouteStop::query()->where('train_id', $train->id)->where('station_id', $from->id)->firstOrFail();
        $origin->update(['departure_offset_minutes' => 240]);
        TrainRouteStop::query()->where('train_id', $train->id)->where('station_id', $to->id)->update(['arrival_offset_minutes' => 420]);
        $requestedDate = $tripStart->copy()->addDays(1)->toDateString();

        $this->get(route('frontend.trains.search', [
            'from_station_id' => $from->id,
            'to_station_id' => $to->id,
            'date' => $requestedDate,
        ]))->assertOk()->assertSee($train->name);
    }

    public function test_intermediate_journey_can_be_booked_after_initial_departure_and_is_shown_in_history(): void
    {
        [$train, $trip, , $b, $c, $d, $inventory] = $this->makeFourStopTrip();
        $user = User::factory()->create();

        $this->get(route('frontend.trains.search', [
            'from_station_id' => $b->id, 'to_station_id' => $d->id,
            'date' => $trip->starts_at->copy()->addMinutes(120)->toDateString(),
        ]))->assertOk()->assertSee($train->name);

        $this->actingAs($user)->post(route('frontend.trains.book', $trip), [
            'boarding_station_id' => $b->id,
            'destination_station_id' => $d->id,
            'inventory_ids' => [$inventory->id],
            'passengers' => [['name' => 'Intermediate Passenger']],
        ])->assertRedirect(route('frontend.trains.bookings'));

        $booking = TrainBooking::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertDatabaseHas('train_bookings', [
            'id' => $booking->id, 'boarding_station_id' => $b->id,
            'destination_station_id' => $d->id,
            'boarding_at' => $trip->starts_at->copy()->addMinutes(120)->toDateTimeString(),
            'arrival_at' => $trip->starts_at->copy()->addMinutes(240)->toDateTimeString(),
        ]);
        $this->get(route('frontend.trains.bookings'))->assertOk()
            ->assertSee('Your journey')->assertSee($b->name)->assertSee($d->name)
            ->assertSee($booking->boarding_at->format('D, j M Y · g:i A'));

        $this->actingAs(User::factory()->create())->from(route('frontend.trains.search'))
            ->post(route('frontend.trains.book', $trip), [
                'boarding_station_id' => $b->id, 'destination_station_id' => $c->id,
                'inventory_ids' => [$inventory->id], 'passengers' => [['name' => 'Other Passenger']],
            ])->assertSessionHasErrors('seats');
        $this->assertSame(1, TrainBookingSeat::query()->where('trip_seat_inventory_id', $inventory->id)->whereNull('released_at')->count());
    }

    public function test_departed_intermediate_station_and_invalid_stops_cannot_book_or_change_inventory(): void
    {
        [, $trip, $a, $b, $c, $d, $inventory] = $this->makeFourStopTrip();
        $other = Station::factory()->create(['code' => 'OTHER' . $trip->id]);
        $b->update(['is_active' => false]);
        $service = app(TrainBookingService::class);
        foreach ([[$b->id, $d->id], [$d->id, $b->id], [$c->id, $c->id], [$other->id, $d->id]] as [$fromId, $toId]) {
            try {
                $service->book(User::factory()->create(), $trip->id, [$inventory->id], [['name' => 'Passenger']], $fromId, $toId);
                $this->fail('An invalid journey must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('stations', $exception->errors());
            }
        }

        $b->update(['is_active' => true]);
        $trip->update(['starts_at' => now()->subHours(3), 'ends_at' => now()->addHour()]);
        try {
            $service->book(User::factory()->create(), $trip->id, [$inventory->id], [['name' => 'Passenger']], $b->id, $d->id);
            $this->fail('Boarding after departure must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('trip', $exception->errors());
        }
        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $inventory->id, 'status' => TripSeatInventory::STATUS_AVAILABLE, 'current_booking_id' => null,
        ]);
        $this->assertSame(0, TrainBooking::query()->where('train_trip_id', $trip->id)->count());
    }

    public function test_search_hides_departed_boarding_station_and_http_forms_require_endpoints(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(12, 0));
        [$train, $trip, , $b, , $d, $inventory] = $this->makeFourStopTrip();
        $trip->update(['starts_at' => now()->subHours(3), 'ends_at' => now()->addHour()]);
        $this->get(route('frontend.trains.search', [
            'from_station_id' => $b->id, 'to_station_id' => $d->id,
            'date' => $trip->starts_at->copy()->addMinutes(120)->toDateString(),
        ]))->assertOk()->assertDontSee($train->name);

        $this->actingAs(User::factory()->create())->from(route('frontend.trains.search'))
            ->post(route('frontend.trains.book', $trip), [
                'inventory_ids' => [$inventory->id], 'passengers' => [['name' => 'Passenger']],
            ])->assertSessionHasErrors(['boarding_station_id', 'destination_station_id']);
        $this->post(route('frontend.trains.waitlist', $trip), [
            'passengers' => [['name' => 'Passenger']],
        ])->assertSessionHasErrors(['boarding_station_id', 'destination_station_id']);
    }

    public function test_intermediate_booking_uses_boarding_cutoff_for_cancellation(): void
    {
        [, $trip, , $b, , $d, $inventory] = $this->makeFourStopTrip();
        $user = User::factory()->create();
        $service = app(TrainBookingService::class);
        $booking = $service->book($user, $trip->id, [$inventory->id], [['name' => 'Passenger']], $b->id, $d->id);
        $service->cancel($user, $booking->id);
        $this->assertDatabaseHas('train_bookings', ['id' => $booking->id, 'status' => TrainBooking::STATUS_CANCELLED]);

        $second = $service->book($user, $trip->id, [$inventory->id], [['name' => 'Passenger']], $b->id, $d->id);
        $this->travelTo($second->boarding_at->copy()->addMinute());
        try {
            $service->cancel($user, $second->id);
            $this->fail('Cancellation after boarding must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('booking', $exception->errors());
        }
        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $inventory->id, 'status' => TripSeatInventory::STATUS_BOOKED, 'current_booking_id' => $second->id,
        ]);
    }

    public function test_waitlist_promotion_skips_expired_boarding_and_preserves_selected_journey(): void
    {
        [, $trip, , $b, $c, $d, $inventory] = $this->makeFourStopTrip();
        $service = app(TrainBookingService::class);
        $owner = User::factory()->create();
        $ownerBooking = $service->book($owner, $trip->id, [$inventory->id], [['name' => 'Owner']], $c->id, $d->id);
        $expired = $service->joinWaitlist(User::factory()->create(), $trip->id, [['name' => 'First Waiter']], $b->id, $d->id);
        $eligible = $service->joinWaitlist(User::factory()->create(), $trip->id, [['name' => 'Second Waiter']], $c->id, $d->id);
        $this->assertDatabaseHas('train_waitlist_entries', [
            'id' => $eligible->id, 'boarding_station_id' => $c->id,
            'destination_station_id' => $d->id,
            'boarding_at' => $eligible->boarding_at->toDateTimeString(),
        ]);

        $this->travelTo($expired->boarding_at->copy()->addMinute());
        $service->cancel($owner, $ownerBooking->id);
        $this->assertDatabaseHas('train_waitlist_entries', ['id' => $expired->id, 'status' => TrainWaitlistEntry::STATUS_WAITING]);
        $this->assertDatabaseHas('train_waitlist_entries', ['id' => $eligible->id, 'status' => TrainWaitlistEntry::STATUS_PROMOTED]);
        $promoted = $eligible->fresh()->promotedBooking;
        $this->assertSame($c->id, $promoted->boarding_station_id);
        $this->assertSame($d->id, $promoted->destination_station_id);
        $this->assertTrue($eligible->boarding_at->equalTo($promoted->boarding_at));
    }

    public function test_legacy_booking_and_waitlist_keep_full_trip_semantics(): void
    {
        [, $trip, $from, $to, $inventory] = $this->makeTrip();
        $service = app(TrainBookingService::class);
        $owner = User::factory()->create();
        $booking = $service->book($owner, $trip->id, [$inventory->id], [['name' => 'Owner']], $from->id, $to->id);
        $booking->forceFill(['boarding_station_id' => null, 'destination_station_id' => null, 'boarding_at' => null, 'arrival_at' => null])->save();
        $entry = $service->joinWaitlist($owner, $trip->id, [['name' => 'Waiter']], $from->id, $to->id);
        $entry->forceFill(['boarding_station_id' => null, 'destination_station_id' => null, 'boarding_at' => null, 'arrival_at' => null])->save();

        $this->actingAs($owner)->get(route('frontend.trains.bookings'))
            ->assertOk()->assertSee('Legacy full-trip schedule')->assertSee('Legacy full-trip waitlist request');
        $service->cancel($owner, $booking->id);
        $this->assertDatabaseHas('train_waitlist_entries', ['id' => $entry->id, 'status' => TrainWaitlistEntry::STATUS_PROMOTED]);
        $this->assertDatabaseHas('train_bookings', [
            'id' => $entry->fresh()->promoted_booking_id,
            'boarding_station_id' => null, 'destination_station_id' => null,
        ]);
    }

    public function test_invalid_waitlist_journey_is_rolled_back(): void
    {
        [, $trip, , $b, $c, $d, $inventory] = $this->makeFourStopTrip();
        $owner = User::factory()->create();
        $service = app(TrainBookingService::class);
        $service->book($owner, $trip->id, [$inventory->id], [['name' => 'Owner']], $c->id, $d->id);
        try {
            $service->joinWaitlist(User::factory()->create(), $trip->id, [['name' => 'Waiter']], $d->id, $b->id);
            $this->fail('A reversed waitlist journey must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('stations', $exception->errors());
        }
        $this->assertSame(0, TrainWaitlistEntry::query()->where('train_trip_id', $trip->id)->count());
    }

    private function makeFourStopTrip(): array
    {
        [$train, $trip, $a, $d, $inventory] = $this->makeTrip();
        TrainRouteStop::query()->where('train_id', $train->id)->where('station_id', $d->id)
            ->update(['stop_order' => 4, 'arrival_offset_minutes' => 240]);
        $b = Station::factory()->create(['code' => 'MIDB' . $train->id]);
        $c = Station::factory()->create(['code' => 'MIDC' . $train->id]);
        TrainRouteStop::create(['train_id' => $train->id, 'station_id' => $b->id, 'stop_order' => 2, 'arrival_offset_minutes' => 110, 'departure_offset_minutes' => 120]);
        TrainRouteStop::create(['train_id' => $train->id, 'station_id' => $c->id, 'stop_order' => 3, 'arrival_offset_minutes' => 170, 'departure_offset_minutes' => 180]);
        $trip->update(['starts_at' => now()->subHour(), 'ends_at' => now()->addHours(3)]);

        return [$train, $trip->fresh(), $a, $b, $c, $d, $inventory];
    }

    private function makeTrip(bool $accessible = true): array
    {
        $train = Train::factory()->create();
        $from = Station::factory()->create(['code' => 'SRC' . $train->id]);
        $to = Station::factory()->create(['code' => 'DST' . $train->id]);
        TrainRouteStop::create(['train_id' => $train->id, 'station_id' => $from->id, 'stop_order' => 1, 'departure_offset_minutes' => 0]);
        TrainRouteStop::create(['train_id' => $train->id, 'station_id' => $to->id, 'stop_order' => 2, 'arrival_offset_minutes' => 180]);
        $trip = TrainTrip::factory()->for($train)->create([
            'starts_at' => now()->addDays(4)->setTime(8, 0),
            'ends_at' => now()->addDays(4)->setTime(11, 0),
        ]);
        $coach = TrainCoach::factory()->for($train)->create();
        $seat = TrainSeat::factory()->for($train)->for($coach, 'coach')->create(['is_accessible' => $accessible]);
        $inventory = TripSeatInventory::factory()->for($trip, 'trip')->for($seat, 'seat')->create(['train_id' => $train->id]);

        return [$train, $trip, $from, $to, $inventory];
    }

    private function stationIds(TrainTrip $trip): array
    {
        return TrainRouteStop::query()->where('train_id', $trip->train_id)
            ->orderBy('stop_order')->pluck('station_id')->pipe(fn ($ids) => [$ids->first(), $ids->last()]);
    }

    private function journeyFields(TrainTrip $trip): array
    {
        [$boarding, $destination] = $this->stationIds($trip);

        return ['boarding_station_id' => $boarding, 'destination_station_id' => $destination];
    }
}
