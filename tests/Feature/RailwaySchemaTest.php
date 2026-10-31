<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Coach;
use App\Models\Passenger;
use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainRouteStop;
use App\Models\TrainTrip;
use App\Models\TripSeatInventory;
use App\Models\WaitlistEntry;
use App\Support\RailwaySchema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\RailwayTestCase;

class RailwaySchemaTest extends RailwayTestCase
{
    private function rejects(callable $write): void
    {
        try {
            $write();
            $this->fail('A database constraint should reject this write.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
    }

    public function test_schema_starts_completely_empty(): void
    {
        foreach (RailwaySchema::TABLES as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_station_codes_and_train_numbers_have_database_constraints(): void
    {
        $station = Station::factory()->create();
        $this->rejects(fn () => Station::factory()->create(['code' => $station->code]));
        foreach (['abc', 'A1', '', 'TOOLONG'] as $code) {
            $this->rejects(fn () => Station::factory()->create(['code' => $code]));
        }
        $train = Train::factory()->create();
        $this->rejects(fn () => Train::factory()->create(['number' => $train->number]));
        foreach (['1234', '123456', 'ABCDE'] as $number) {
            $this->rejects(fn () => Train::factory()->create(['number' => $number]));
        }
    }

    public function test_route_and_coach_and_seat_uniqueness_is_enforced(): void
    {
        extract($this->journey());
        $this->rejects(fn () => TrainRouteStop::factory()->create(['train_id' => $train->id, 'stop_order' => 1]));
        $this->rejects(fn () => TrainRouteStop::factory()->create(['train_id' => $train->id, 'station_id' => $origin->id, 'stop_order' => 3]));
        $this->rejects(fn () => Coach::factory()->create(['train_id' => $train->id, 'label' => $coach->label]));
        $this->rejects(fn () => Seat::factory()->create(['coach_id' => $coach->id, 'number' => $seat->number]));
        $this->rejects(fn () => Seat::factory()->create(['coach_id' => $coach->id, 'number' => 0]));
        $this->rejects(fn () => Seat::factory()->create(['coach_id' => $coach->id, 'number' => 2, 'berth_type' => 'invalid']));
    }

    public function test_inventory_must_match_both_the_trip_train_and_seat_train(): void
    {
        $first = $this->journey();
        $second = $this->journey();
        $this->rejects(fn () => TripSeatInventory::factory()->create([
            'trip_id' => $first['trip']->id, 'train_id' => $first['train']->id, 'seat_id' => $second['seat']->id,
        ]));
        $this->rejects(fn () => TripSeatInventory::factory()->create([
            'trip_id' => $first['trip']->id, 'train_id' => $second['train']->id, 'seat_id' => $second['seat']->id,
        ]));
        $this->rejects(fn () => TripSeatInventory::factory()->create([
            'trip_id' => $first['trip']->id, 'seat_id' => $first['seat']->id,
        ]));
    }

    public function test_assignment_uniqueness_and_cancellation_release_the_active_slot(): void
    {
        extract($this->journey());
        $booking = Booking::factory()->create(['trip_id' => $trip->id]);
        $first = Passenger::factory()->create(['booking_id' => $booking->id]);
        $second = Passenger::factory()->create(['booking_id' => $booking->id]);
        $inventory = $trip->inventory()->firstOrFail();
        $assignment = SeatAssignment::factory()->create(['passenger_id' => $first->id, 'inventory_id' => $inventory->id]);
        $secondSeat = Seat::factory()->create(['coach_id' => $coach->id, 'number' => 2]);
        $secondInventory = TripSeatInventory::factory()->create(['trip_id' => $trip->id, 'seat_id' => $secondSeat->id]);
        $this->rejects(fn () => SeatAssignment::factory()->create(['passenger_id' => $first->id, 'inventory_id' => $secondInventory->id]));
        $this->rejects(fn () => SeatAssignment::factory()->create(['passenger_id' => $second->id, 'inventory_id' => $inventory->id]));
        $assignment->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        SeatAssignment::factory()->create(['passenger_id' => $second->id, 'inventory_id' => $inventory->id]);
        $this->assertDatabaseHas('seat_assignments', ['id' => $assignment->id, 'status' => 'cancelled']);
        $this->assertSame(1, $inventory->assignments()->where('status', 'active')->count());
    }

    public function test_passenger_and_inventory_cannot_refer_to_different_trips(): void
    {
        $first = $this->journey();
        $second = $this->journey();
        $passenger = Passenger::factory()->create(['booking_id' => Booking::factory()->create(['trip_id' => $first['trip']->id])->id]);
        $this->rejects(fn () => SeatAssignment::factory()->create([
            'passenger_id' => $passenger->id, 'inventory_id' => $second['trip']->inventory()->value('id'),
        ]));
        $this->rejects(fn () => Passenger::factory()->create([
            'booking_id' => $passenger->booking_id, 'trip_id' => $second['trip']->id,
        ]));
    }

    public function test_booking_stations_must_follow_the_route_and_belong_to_the_train(): void
    {
        extract($this->journey());
        $this->rejects(fn () => Booking::factory()->create(['trip_id' => $trip->id, 'boarding_order' => 2, 'alighting_order' => 1]));
        $this->rejects(fn () => Booking::factory()->create(['trip_id' => $trip->id, 'boarding_order' => 1, 'alighting_order' => 99]));
        $this->rejects(fn () => Booking::factory()->create(['trip_id' => $trip->id, 'total_paise' => -1]));
    }

    public function test_waitlist_order_and_active_membership_are_unique(): void
    {
        $first = Passenger::factory()->create();
        $second = Passenger::factory()->create(['booking_id' => $first->booking_id]);
        $entry = WaitlistEntry::factory()->create(['passenger_id' => $first->id]);
        $this->rejects(fn () => WaitlistEntry::factory()->create(['passenger_id' => $second->id]));
        $this->rejects(fn () => WaitlistEntry::factory()->create(['passenger_id' => $first->id, 'position' => 2]));
        $entry->update(['status' => 'promoted', 'promoted_at' => now()]);
        WaitlistEntry::factory()->create(['passenger_id' => $first->id, 'position' => 2]);
        $this->assertDatabaseHas('waitlist_entries', ['id' => $entry->id, 'status' => 'promoted']);
        $this->assertSame(1, $first->waitlistEntries()->where('status', 'waiting')->count());
    }

    public function test_cancellation_requires_a_timestamp_and_transactions_roll_back(): void
    {
        $assignment = SeatAssignment::factory()->create();
        $this->rejects(fn () => $assignment->update(['status' => 'cancelled']));
        try {
            DB::transaction(function () use ($assignment) {
                $assignment->refresh()->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                throw new RuntimeException('Simulated failure');
            });
        } catch (RuntimeException) {
            $this->assertDatabaseHas('seat_assignments', ['id' => $assignment->id, 'status' => 'active', 'cancelled_at' => null]);
        }
    }

    public function test_booking_relationships_and_foreign_key_delete_restrictions(): void
    {
        $passenger = Passenger::factory()->create();
        $booking = $passenger->booking;
        $this->assertTrue($booking->train->is($booking->trip->train));
        $this->assertTrue($booking->user->bookings()->first()->is($booking));
        $this->assertTrue($booking->passengers()->first()->is($passenger));
        $this->rejects(fn () => $booking->delete());
        $this->rejects(fn () => $booking->trip->delete());
    }

    public function test_trip_dates_are_unique_and_invalid_states_are_rejected(): void
    {
        $trip = TrainTrip::factory()->create();
        $this->rejects(fn () => TrainTrip::factory()->create(['train_id' => $trip->train_id, 'service_date' => $trip->service_date]));
        $this->rejects(fn () => $trip->update(['status' => 'invalid']));
    }

    public function test_route_offsets_and_waitlist_positions_have_check_constraints(): void
    {
        foreach ([
            ['stop_order' => 0], ['arrival_offset_minutes' => -1],
            ['arrival_offset_minutes' => 500, 'departure_offset_minutes' => 499],
            ['departure_offset_minutes' => 44640],
        ] as $attributes) {
            $this->rejects(fn () => TrainRouteStop::factory()->create($attributes));
        }
        $this->rejects(fn () => WaitlistEntry::factory()->create(['position' => 0]));
    }
}
