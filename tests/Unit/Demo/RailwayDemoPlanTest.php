<?php

namespace Tests\Unit\Demo;

use App\Services\Demo\RailwayDemoPlan;
use App\Services\Demo\RailwayDemoValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** These tests use arrays only. They never boot Laravel, invoke Artisan or connect. */
class RailwayDemoPlanTest extends TestCase
{
    private static array $plan;

    public static function setUpBeforeClass(): void
    {
        self::$plan = (new RailwayDemoPlan)->build('2026-10-24');
    }

    public function test_exact_sizes_outcomes_and_inventory_totals_are_consistent(): void
    {
        (new RailwayDemoValidator)->validate(self::$plan);
        $tables = self::$plan['tables'];
        foreach (RailwayDemoPlan::COUNTS as $table => $count) {
            self::assertCount($count, $tables[$table]);
        }
        self::assertSame(['completed' => 240, 'scheduled' => 60], array_count_values(array_column($tables['train_trips'], 'status')));
        $statuses = array_count_values(array_column($tables['train_bookings'], 'status'));
        self::assertSame(1300, $statuses['confirmed']);
        self::assertSame(200, $statuses['cancelled']);
        $waitlist = array_count_values(array_column($tables['train_waitlist_entries'], 'status'));
        self::assertSame(100, $waitlist['waiting']);
        self::assertSame(30, $waitlist['promoted']);
        self::assertSame(20, $waitlist['cancelled']);
        self::assertCount(1496, self::$plan['inventory_pointers']);
        self::assertSame(3304, count($tables['trip_seat_inventories']) - count(self::$plan['inventory_pointers']));
    }

    public function test_seed_anchor_and_timezone_are_repeatable_without_global_random_state(): void
    {
        $builder = new RailwayDemoPlan;
        $first = $builder->build('2026-10-24');
        self::assertSame(RailwayDemoPlan::fingerprint(self::$plan), RailwayDemoPlan::fingerprint($first));
        self::assertSame(RailwayDemoPlan::fingerprint($first), RailwayDemoPlan::fingerprint($builder->build('2026-10-24')));
        self::assertNotSame(RailwayDemoPlan::fingerprint($first), RailwayDemoPlan::fingerprint($builder->build('2026-10-24', 1234)));
        self::assertNotSame(RailwayDemoPlan::fingerprint($first), RailwayDemoPlan::fingerprint($builder->build('2026-10-25')));
        $india = $builder->build('2026-10-24', RailwayDemoPlan::DEFAULT_SEED, 'Asia/Kolkata');
        (new RailwayDemoValidator)->validate($india);
        self::assertSame('06:00:00', substr($india['tables']['train_trips']['S27-T001']['starts_at'], 11));
        self::assertSame('00:30:00', substr($first['tables']['train_trips']['S27-T001']['starts_at'], 11));
    }

    public function test_accounts_are_fictional_unapproved_and_have_no_plan_credentials(): void
    {
        $tables = self::$plan['tables'];
        foreach ($tables['users'] as $email => $user) {
            self::assertStringEndsWith('@example.invalid', $email);
            self::assertFalse($user['verification_status']);
            self::assertNull($user['train_owner_approved_at']);
            self::assertArrayNotHasKey('password', $user);
            self::assertFalse($tables['participants'][$email]['is_approved']);
            self::assertSame(str_starts_with($email, 's27.passenger.'), $tables['participants'][$email]['train_access_enabled']);
        }
        self::assertArrayNotHasKey('roles', $tables);
        self::assertArrayNotHasKey('permissions', $tables);
        self::assertArrayNotHasKey('permission_role', $tables);
    }

    public function test_intermediate_journeys_group_sizes_and_accessibility_are_present(): void
    {
        $tables = self::$plan['tables'];
        $firstStops = [];
        foreach ($tables['train_route_stops'] as $stop) {
            if ($stop['stop_order'] === 1) {
                $firstStops[$stop['train_id']] = $stop['station_id'];
            }
        }
        $intermediate = array_filter($tables['train_bookings'], function ($booking) use ($tables, $firstStops) {
            $trip = $tables['train_trips'][explode('|', $booking['train_trip_id'], 2)[1]];

            return $booking['boarding_station_id'] !== $firstStops[$trip['train_id']];
        });
        self::assertGreaterThan(500, count($intermediate));
        self::assertCount(196, array_filter($tables['train_bookings'], fn ($r) => $r['seat_count'] === 2));
        self::assertNotEmpty(array_filter($tables['train_booking_passengers'], fn ($r) => $r['requires_accessible_seat']));
        self::assertGreaterThan(60, count(array_unique(array_column($tables['train_booking_passengers'], 'age'))));
    }

    public function test_invalid_calendar_anchor_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new RailwayDemoPlan)->build('2026-02-30');
    }

    public function test_duplicate_active_seat_is_rejected(): void
    {
        $plan = self::$plan;
        $keys = array_keys($plan['tables']['train_booking_seats']);
        $plan['tables']['train_booking_seats'][$keys[1]]['trip_seat_inventory_id'] = $plan['tables']['train_booking_seats'][$keys[0]]['trip_seat_inventory_id'];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Duplicate active seat');
        (new RailwayDemoValidator)->validate($plan);
    }

    public function test_inaccessible_assignment_is_rejected(): void
    {
        $plan = self::$plan;
        $keys = array_keys($plan['tables']['train_booking_passengers']);
        $plan['tables']['train_booking_passengers'][$keys[1]]['requires_accessible_seat'] = true;
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Inaccessible seat');
        (new RailwayDemoValidator)->validate($plan);
    }

    public function test_cross_booking_passenger_is_rejected(): void
    {
        $plan = self::$plan;
        $keys = array_keys($plan['tables']['train_booking_seats']);
        $plan['tables']['train_booking_seats'][$keys[1]]['train_booking_passenger_id'] = $plan['tables']['train_booking_seats'][$keys[0]]['train_booking_passenger_id'];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cross-trip or cross-booking');
        (new RailwayDemoValidator)->validate($plan);
    }

    public function test_cancellation_at_boarding_is_rejected(): void
    {
        $plan = self::$plan;
        foreach ($plan['tables']['train_bookings'] as $key => &$booking) {
            if ($booking['status'] !== 'cancelled') {
                continue;
            }
            $booking['cancelled_at'] = $booking['boarding_at'];
            $booking['updated_at'] = $booking['boarding_at'];
            $plan['tables']['train_booking_seats'][$key.':0']['released_at'] = $booking['boarding_at'];
            break;
        }
        unset($booking);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cancellation crosses boarding cutoff');
        (new RailwayDemoValidator)->validate($plan);
    }

    public function test_waitlist_cannot_start_before_the_trip_fills(): void
    {
        $plan = self::$plan;
        $key = array_key_first($plan['tables']['train_waitlist_entries']);
        $tripKey = explode('|', $plan['tables']['train_waitlist_entries'][$key]['train_trip_id'], 2)[1];
        $early = \Carbon\CarbonImmutable::parse($plan['tables']['train_trips'][$tripKey]['starts_at'], 'UTC')->subDays(22)->format('Y-m-d H:i:s');
        $plan['tables']['train_waitlist_entries'][$key]['created_at'] = $early;
        $plan['tables']['train_waitlist_passengers'][$key]['created_at'] = $early;
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Waitlist was joined while seats were available');
        (new RailwayDemoValidator)->validate($plan);
    }

    public function test_promotion_cannot_change_booking_owner(): void
    {
        $plan = self::$plan;
        $entry = reset($plan['tables']['train_waitlist_entries']);
        $key = explode('|', $entry['promoted_booking_id'], 2)[1];
        $plan['tables']['train_bookings'][$key]['user_id'] = '@users|s27.passenger.100@example.invalid';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Promotion changed journey or owner');
        (new RailwayDemoValidator)->validate($plan);
    }

    public function test_reversed_journey_is_rejected(): void
    {
        $plan = self::$plan;
        $key = array_key_first($plan['tables']['train_bookings']);
        $booking = &$plan['tables']['train_bookings'][$key];
        [$booking['boarding_station_id'], $booking['destination_station_id']] = [$booking['destination_station_id'], $booking['boarding_station_id']];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Reversed or missing journey stop');
        (new RailwayDemoValidator)->validate($plan);
    }
}
