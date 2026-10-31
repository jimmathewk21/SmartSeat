<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingEvent;
use App\Models\Coach;
use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\TrainRouteStop;
use App\Models\User;
use App\Models\WaitlistEntry;
use App\Services\BookingEngine;
use App\Services\RailwayCatalogue;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\BookingTestCase;

class BookingEngineTest extends BookingTestCase
{
    private function invalid(callable $action, string $field): void
    {
        try {
            $action();
            $this->fail('The invalid operation should have been rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }

    public function test_successful_multiple_passenger_booking_has_consistent_assignments_and_audit(): void
    {
        $journey = $this->journey();
        $second = $this->addSeat($journey);
        $first = $journey['trip']->inventory()->orderBy('id')->value('id');
        $booking = $this->reserve($journey, seats: [$second, $first]);
        $this->assertSame('confirmed', $booking->status);
        $this->assertTrue(Str::isUuid($booking->reference));
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('passengers', 2);
        $this->assertDatabaseCount('seat_assignments', 2);
        $this->assertSame([$second, $first], $booking->passengers()->orderBy('id')->get()->map(fn ($person) => $person->assignments()->value('inventory_id'))->all());
        $this->assertSame(['created', 'confirmed'], $booking->events()->orderBy('id')->pluck('action')->all());
        foreach ($booking->passengers as $person) {
            $this->assertDatabaseHas('passengers', ['id' => $person->id, 'trip_id' => $journey['trip']->id, 'status' => 'confirmed']);
            $this->assertDatabaseHas('seat_assignments', ['passenger_id' => $person->id, 'trip_id' => $journey['trip']->id, 'status' => 'active']);
        }
    }

    public function test_duplicate_and_cross_trip_seat_claims_are_rejected_without_partial_records(): void
    {
        $journey = $this->journey();
        $other = $this->journey();
        $this->reserve($journey);
        $this->invalid(fn () => $this->reserve($journey), 'seats');
        $this->invalid(fn () => $this->reserve($other, seats: [$journey['trip']->inventory()->value('id')]), 'seats');
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('passengers', 1);
        $this->assertDatabaseCount('seat_assignments', 1);
    }

    public function test_duplicate_selected_seats_and_wrong_class_cannot_be_used(): void
    {
        $journey = $this->journey();
        $id = $journey['trip']->inventory()->value('id');
        $this->invalid(fn () => $this->reserve($journey, seats: [$id, $id]), 'seats.1');
        $coach = Coach::factory()->create(['train_id' => $journey['train']->id, 'label' => 'A1', 'travel_class' => '3A']);
        $seat = Seat::factory()->create(['coach_id' => $coach->id]);
        app(RailwayCatalogue::class)->initializeInventory($journey['trip']);
        $this->invalid(fn () => $this->reserve($journey, seats: [$journey['trip']->inventory()->where('seat_id', $seat->id)->value('id')]), 'seats');
        $this->assertDatabaseCount('bookings', 0);
    }

    #[DataProvider('invalidPeople')]
    public function test_invalid_passenger_details_create_nothing(array $person): void
    {
        $journey = $this->journey();
        $data = $this->input();
        $data['passengers'][0] = array_replace($data['passengers'][0], $person);
        try {
            app(BookingEngine::class)->book(User::factory()->create(), $journey['trip'], $data, 'reserve', [$journey['trip']->inventory()->value('id')], (string) Str::uuid());
            $this->fail('Invalid passenger must be rejected.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('bookings', 0);
            $this->assertDatabaseCount('passengers', 0);
        }
    }

    public static function invalidPeople(): array
    {
        return [[['name' => '']], [['name' => '   ']], [['name' => []]], [['age' => -1]], [['age' => 121]], [['age' => 'adult']], [['gender' => 'invalid']], [['role' => 'administrator']]];
    }

    public function test_departed_cancelled_and_invalid_route_journeys_are_rejected(): void
    {
        $journey = $this->journey();
        $engine = app(BookingEngine::class);
        $user = User::factory()->create();
        $id = $journey['trip']->inventory()->value('id');
        $data = $this->input();
        $data['alighting_order'] = 99;
        $this->invalid(fn () => $engine->book($user, $journey['trip'], $data, 'reserve', [$id], (string) Str::uuid()), 'journey');
        $this->travelTo(CarbonImmutable::parse($journey['trip']->service_date->toDateString(), 'Asia/Kolkata')->addMinutes(480));
        $this->invalid(fn () => $this->reserve($journey), 'journey');
        $this->travelBack();
        $journey['trip']->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        $this->invalid(fn () => $this->reserve($journey), 'journey');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_second_assignment_failure_rolls_back_the_entire_booking(): void
    {
        $journey = $this->journey();
        $second = $this->addSeat($journey);
        $calls = 0;
        Event::listen('eloquent.creating: '.SeatAssignment::class, function () use (&$calls) {
            if (++$calls === 2) {
                throw new RuntimeException('Injected allocation failure');
            }
        });
        try {
            $this->reserve($journey, seats: [$journey['trip']->inventory()->orderBy('id')->value('id'), $second]);
            $this->fail('Expected injected failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Injected allocation failure', $exception->getMessage());
        }
        foreach (['bookings', 'passengers', 'seat_assignments', 'booking_events'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_duplicate_key_failure_is_reported_without_leaving_a_partial_booking(): void
    {
        $journey = $this->journey();
        Event::listen('eloquent.creating: '.SeatAssignment::class, function () {
            $previous = new PDOException('Simulated duplicate');
            $previous->errorInfo = ['23000', 1062, 'Simulated duplicate'];
            throw new QueryException('sqlite', 'insert into seat_assignments', [], $previous);
        });
        $this->invalid(fn () => $this->reserve($journey), 'booking');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('passengers', 0);
        $this->assertDatabaseCount('booking_events', 0);
    }

    public function test_confirmation_retries_are_idempotent_even_after_cancellation(): void
    {
        $journey = $this->journey();
        $owner = User::factory()->create();
        $key = (string) Str::uuid();
        $first = $this->reserve($journey, $owner, key: $key);
        $second = $this->reserve($journey, $owner, key: $key);
        $this->assertSame($first->id, $second->id);
        app(BookingEngine::class)->cancel($owner, $first);
        $this->assertSame('cancelled', $this->reserve($journey, $owner, key: $key)->status);
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('passengers', 1);
        $this->assertSame(0, SeatAssignment::where('status', 'active')->count());
    }

    public function test_reusing_submission_key_with_changed_details_is_rejected(): void
    {
        $journey = $this->journey();
        $owner = User::factory()->create();
        $key = (string) Str::uuid();
        $this->reserve($journey, $owner, key: $key);
        $details = $this->input();
        $details['passengers'][0]['name'] = 'Different person';
        $this->invalid(fn () => app(BookingEngine::class)->book($owner, $journey['trip'], $details, 'reserve', [$journey['trip']->inventory()->value('id')], $key), 'booking');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_cancellation_is_idempotent_and_promotes_fifo_with_identical_timestamps(): void
    {
        $this->freezeTime();
        $journey = $this->journey();
        $owner = User::factory()->create();
        $reserved = $this->reserve($journey, $owner);
        $first = $this->wait($journey);
        $second = $this->wait($journey);
        $engine = app(BookingEngine::class);
        $engine->cancel($owner, $reserved);
        $this->assertSame('confirmed', $first->refresh()->status);
        $this->assertSame('waitlisted', $second->refresh()->status);
        $this->assertDatabaseHas('waitlist_entries', ['passenger_id' => $first->passengers()->value('id'), 'status' => 'promoted']);
        $engine->cancel($owner, $reserved);
        $this->assertSame('waitlisted', $second->refresh()->status);
        $this->assertSame(1, $reserved->events()->where('action', 'cancelled')->count());
        $this->assertSame(1, SeatAssignment::where('status', 'active')->count());
    }

    public function test_fifo_uses_creation_time_before_unique_id(): void
    {
        $journey = $this->journey();
        $reserved = $this->reserve($journey);
        $first = $this->wait($journey);
        $second = $this->wait($journey);
        WaitlistEntry::where('passenger_id', $second->passengers()->value('id'))->update(['created_at' => now()->subMinute()]);
        app(BookingEngine::class)->cancel($reserved->user, $reserved);
        $this->assertSame('confirmed', $second->refresh()->status);
        $this->assertSame('waitlisted', $first->refresh()->status);
    }

    public function test_group_promotion_is_all_or_nothing_and_never_skips_the_head(): void
    {
        $journey = $this->journey();
        $secondSeat = $this->addSeat($journey);
        $a = $this->reserve($journey);
        $b = $this->reserve($journey, seats: [$secondSeat]);
        $head = $this->wait($journey, 2);
        $later = $this->wait($journey);
        $engine = app(BookingEngine::class);
        $engine->cancel($a->user, $a);
        $this->assertSame('waitlisted', $head->refresh()->status);
        $this->assertSame('waitlisted', $later->refresh()->status);
        $free = $journey['trip']->inventory()->whereDoesntHave('activeAssignment')->value('id');
        $this->invalid(fn () => $this->reserve($journey, seats: [$free]), 'seats');
        $engine->cancel($b->user, $b);
        $this->assertSame('confirmed', $head->refresh()->status);
        $this->assertSame(2, SeatAssignment::whereIn('passenger_id', $head->passengers()->pluck('id'))->where('status', 'active')->count());
        $this->assertSame('waitlisted', $later->refresh()->status);
    }

    public function test_waitlist_cancellation_preserves_history_and_does_not_promote_twice(): void
    {
        $journey = $this->journey();
        $secondSeat = $this->addSeat($journey);
        $reserved = $this->reserve($journey);
        $this->reserve($journey, seats: [$secondSeat]);
        $head = $this->wait($journey, 2);
        $later = $this->wait($journey);
        $engine = app(BookingEngine::class);
        $engine->cancel($reserved->user, $reserved);
        $engine->cancel($head->user, $head);
        $engine->cancel($head->user, $head);
        $this->assertSame('cancelled', $head->refresh()->status);
        $this->assertSame('confirmed', $later->refresh()->status);
        $this->assertSame(2, WaitlistEntry::whereIn('passenger_id', $head->passengers()->pluck('id'))->where('status', 'cancelled')->count());
        $this->assertSame(1, $later->events()->where('action', 'promoted')->count());
    }

    public function test_promotion_failure_rolls_back_cancellation_and_seat_release(): void
    {
        $journey = $this->journey();
        $reserved = $this->reserve($journey);
        $waiting = $this->wait($journey);
        Event::listen('eloquent.creating: '.SeatAssignment::class, fn () => throw new RuntimeException('Injected promotion failure'));
        try {
            app(BookingEngine::class)->cancel($reserved->user, $reserved);
            $this->fail('Promotion should fail.');
        } catch (RuntimeException) {
            $this->assertSame('confirmed', $reserved->refresh()->status);
            $this->assertSame('waitlisted', $waiting->refresh()->status);
            $this->assertSame(1, SeatAssignment::where('status', 'active')->count());
            $this->assertSame(0, BookingEvent::where('action', 'cancelled')->count());
        }
    }

    public function test_departed_waitlist_requests_expire_instead_of_being_promoted(): void
    {
        $journey = $this->journey();
        $reserved = $this->reserve($journey);
        $waiting = $this->wait($journey);
        $this->travelTo(CarbonImmutable::parse($journey['trip']->service_date->toDateString(), 'Asia/Kolkata')->addMinutes(500));
        app(BookingEngine::class)->cancel($reserved->user, $reserved);
        $this->assertSame('cancelled', $waiting->refresh()->status);
        $this->assertSame(1, $waiting->events()->where('action', 'expired')->count());
        $this->assertSame(0, SeatAssignment::where('status', 'active')->count());
    }

    public function test_whole_trip_occupancy_does_not_reuse_seats_on_another_segment(): void
    {
        $journey = $this->journey();
        TrainRouteStop::factory()->create([
            'train_id' => $journey['train']->id, 'stop_order' => 3,
            'arrival_offset_minutes' => 800, 'departure_offset_minutes' => 805,
        ]);
        $this->reserve($journey);
        $details = array_replace($this->input(), ['boarding_order' => 2, 'alighting_order' => 3]);
        $this->invalid(fn () => app(BookingEngine::class)->book(User::factory()->create(), $journey['trip'], $details, 'reserve', [$journey['trip']->inventory()->value('id')], (string) Str::uuid()), 'seats');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_empty_inventory_accepts_waitlist_and_initialization_automatically_promotes(): void
    {
        $journey = $this->journey();
        $journey['trip']->inventory()->delete(); // Disposable test fixture only.
        $waiting = $this->wait($journey);
        $this->assertSame('waitlisted', $waiting->status);
        app(RailwayCatalogue::class)->initializeInventory($journey['trip']);
        $this->assertSame('confirmed', $waiting->refresh()->status);
        $this->assertSame(1, SeatAssignment::where('status', 'active')->count());
        $this->assertSame(0, app(BookingEngine::class)->promoteTrip($journey['trip']));
    }

    public function test_waitlisting_when_enough_seats_exist_is_rejected(): void
    {
        $journey = $this->journey();
        $this->invalid(fn () => $this->wait($journey), 'waitlist');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_submission_uniqueness_is_enforced_by_the_database(): void
    {
        $journey = $this->journey();
        $booking = $this->reserve($journey);
        try {
            Booking::factory()->create([
                'user_id' => $booking->user_id, 'trip_id' => $booking->trip_id,
                'submission_key' => $booking->submission_key,
            ]);
            $this->fail('The database must reject a duplicate owner/submission key.');
        } catch (QueryException) {
            $this->assertDatabaseCount('bookings', 1);
        }
    }

    public function test_groups_are_bounded_and_blocked_inventory_is_not_released_to_waiters(): void
    {
        $journey = $this->journey();
        $this->invalid(fn () => app(BookingEngine::class)->details($this->input(7)), 'passengers');
        $reserved = $this->reserve($journey);
        $waiting = $this->wait($journey);
        $journey['trip']->inventory()->update(['status' => 'blocked']);
        app(BookingEngine::class)->cancel($reserved->user, $reserved);
        $this->assertSame('waitlisted', $waiting->refresh()->status);
        $this->assertDatabaseHas('trip_seat_inventory', ['trip_id' => $journey['trip']->id, 'status' => 'blocked']);
    }

    public function test_groups_that_can_never_fit_the_class_cannot_block_the_waitlist(): void
    {
        $journey = $this->journey();
        $this->reserve($journey);
        $this->invalid(fn () => $this->wait($journey, 2), 'passengers');
        $this->assertDatabaseCount('waitlist_entries', 0);
    }
}
