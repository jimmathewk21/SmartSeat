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
        $service->book($firstUser, $trip->id, [$inventory->id], [['name' => 'First Passenger']]);

        $this->actingAs($secondUser)
            ->from(route('frontend.trains.search'))
            ->post(route('frontend.trains.book', $trip), [
                'inventory_ids' => [$inventory->id],
                'passengers' => [['name' => 'Second Passenger']],
            ])
            ->assertSessionHasErrors('seats');

        $this->assertSame(1, TrainBookingSeat::query()->where('trip_seat_inventory_id', $inventory->id)->whereNull('released_at')->count());
    }

    public function test_trip_inventory_initialization_is_idempotent(): void
    {
        [$train, $trip] = $this->makeTrip();
        $coach = TrainCoach::factory()->for($train)->create(['code' => 'C99']);
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
        app(TrainBookingService::class)->book($owner, $trip->id, [$inventory->id], [['name' => 'Booked Passenger']]);
        $entry = app(TrainBookingService::class)->joinWaitlist($user, $trip->id, [['name' => 'Waiting Passenger']]);

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
        $booking = $service->book($owner, $trip->id, [$inventory->id], [['name' => 'Booked Passenger']]);

        $firstEntry = $service->joinWaitlist($firstWaiter, $trip->id, [['name' => 'First Waiter']]);
        $secondEntry = $service->joinWaitlist($secondWaiter, $trip->id, [['name' => 'Second Waiter']]);
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
        $booking = app(TrainBookingService::class)->book(User::factory()->create(), $otherTrip->id, [$otherInventory->id], [['name' => 'Passenger']]);

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
        $first = $service->book(User::factory()->create(), $trip->id, [$inventory->id], [['name' => 'First Passenger']]);
        $secondSeat = TrainSeat::factory()->for($trip->train)->for($inventory->seat->coach, 'coach')->create();
        $secondInventory = TripSeatInventory::factory()->for($trip, 'trip')->for($secondSeat, 'seat')->create(['train_id' => $trip->train_id]);
        $second = $service->book(User::factory()->create(), $trip->id, [$secondInventory->id], [['name' => 'Second Passenger']]);
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
        $first = $service->book(User::factory()->create(), $trip->id, [$inventory->id], [['name' => 'First Passenger']]);
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
        $booking = app(TrainBookingService::class)->book(User::factory()->create(), $trip->id, [$inventory->id], [['name' => 'Passenger']]);
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
        $booking = app(TrainBookingService::class)->book($owner, $trip->id, [$inventory->id], [['name' => 'Owner Passenger']]);

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
            app(TrainBookingService::class)->book($user, $trip->id, [$inventory->id], [['name' => 'Valid Passenger']]);
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
            $service->book($user, $trip->id, [$inventory->id], [['age' => 28]]);
            $this->fail('Booking requires a passenger name.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('passengers.0.name', $exception->errors());
        }

        try {
            $service->joinWaitlist($user, $trip->id, [['age' => 28]]);
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
        $booking = $service->book($owner, $trip->id, [$inventory->id], [['name' => 'Booked Passenger']]);
        $entry = $service->joinWaitlist($waiter, $trip->id, [['name' => 'Waiting Passenger']]);
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
}
