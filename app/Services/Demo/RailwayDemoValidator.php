<?php

namespace App\Services\Demo;

use Carbon\CarbonImmutable;
use RuntimeException;

/** Validate the entire symbolic snapshot before any installation can write rows. */
class RailwayDemoValidator
{
    public function validate(array $plan): void
    {
        $this->check(($plan['version'] ?? null) === RailwayDemoPlan::VERSION, 'Unsupported snapshot version.');
        $tables = $plan['tables'];
        $this->check(array_keys($tables) === array_keys(RailwayDemoPlan::COUNTS), 'Unexpected dataset tables or insertion order.');
        foreach (RailwayDemoPlan::COUNTS as $table => $count) {
            $this->check(count($tables[$table] ?? []) === $count, 'Unexpected row count: '.$table);
            foreach ($tables[$table] as $row) {
                $this->check(array_diff(array_keys($row), RailwayDemoSchema::columns($table)) === [], 'Unsupported schema field: '.$table);
                $required = array_diff(RailwayDemoSchema::columns($table), $table === 'users' ? ['password'] : []);
                $this->check(array_diff($required, array_keys($row)) === [], 'Missing schema field: '.$table);
                foreach ($row as $column => $value) {
                    if (is_string($value) && str_starts_with($value, '@')) {
                        [$parent, $key] = $this->split($value);
                        $this->check($parent === 'roles' ? in_array($key, ['passenger', 'owner'], true) : isset($tables[$parent][$key]), 'Dangling reference: '.$value);
                    }
                }
            }
        }
        foreach ($tables['users'] as $email => $user) {
            $this->check($user['email'] === $email && str_ends_with($email, '@example.invalid') && str_starts_with($user['name'], 'Synthetic '), 'Unsafe account identity.');
            $this->check(! isset($user['password']) && $user['verification_status'] === false && $user['train_owner_approved_at'] === null, 'Account approval or credential leaked into the plan.');
            $participant = $tables['participants'][$email];
            $owner = str_starts_with($email, 's27.owner.');
            $this->check($participant['user_id'] === RailwayDemoPlan::ref('users', $email) && ! $participant['is_approved'] && $participant['approved_at'] === null && $participant['train_access_enabled'] === ! $owner, 'Passenger and event access must stay separate.');
            $this->check($tables['role_user'][$email]['role_id'] === RailwayDemoPlan::ref('roles', $owner ? 'owner' : 'passenger'), 'Incorrect account role.');
        }
        foreach ($tables['stations'] as $key => $station) {
            $this->check($station['code'] === $key && preg_match('/^S27-\d{3}$/D', $key) === 1 && strlen($key) <= 10, 'Invalid synthetic station code.');
        }
        $routes = [];
        $owners = [];
        foreach ($tables['trains'] as $key => $train) {
            $this->check($train['code'] === $key && preg_match('/^S27-9\d{4}$/D', $key) === 1 && str_starts_with($train['name'], 'Synthetic '), 'Invalid synthetic service code.');
            $this->check(str_starts_with($train['owner_user_id'], '@users|s27.owner.'), 'A passenger cannot own a synthetic train.');
            $owners[$train['owner_user_id']] = ($owners[$train['owner_user_id']] ?? 0) + 1;
        }
        $this->check(count($owners) === 5 && array_unique(array_values($owners)) === [8], 'Each pending owner must have eight assigned trains.');
        foreach ($tables['train_route_stops'] as $stop) {
            $routes[$stop['train_id']][] = $stop;
        }
        foreach ($routes as $stops) {
            usort($stops, fn ($a, $b) => $a['stop_order'] <=> $b['stop_order']);
            $this->check(array_column($stops, 'stop_order') === [1, 2, 3, 4, 5] && count(array_unique(array_column($stops, 'station_id'))) === 5, 'Invalid ordered route.');
            $previous = -1;
            foreach ($stops as $index => $stop) {
                $arrival = $stop['arrival_offset_minutes'] ?? 0;
                $departure = $stop['departure_offset_minutes'] ?? $arrival;
                $this->check($arrival > $previous && $departure >= $arrival, 'Non-monotonic route offset.');
                $this->check(($index === 0) === ($stop['arrival_offset_minutes'] === null) && ($index === 4) === ($stop['departure_offset_minutes'] === null), 'Invalid terminal offsets.');
                $previous = $departure;
            }
        }
        foreach ($tables['train_seats'] as $seat) {
            $this->check($this->row($tables, $seat['train_coach_id'])['train_id'] === $seat['train_id'], 'Seat/coach train mismatch.');
        }
        $uniqueInventory = [];
        $tripInventoryCounts = [];
        foreach ($tables['trip_seat_inventories'] as $inventory) {
            $this->check($this->row($tables, $inventory['train_trip_id'])['train_id'] === $inventory['train_id'] && $this->row($tables, $inventory['train_seat_id'])['train_id'] === $inventory['train_id'], 'Cross-train inventory.');
            $this->check($inventory['status'] === 'available' && $inventory['current_booking_id'] === null, 'Inventory must be inserted available before final pointers are set.');
            $unique = $inventory['train_trip_id'].'/'.$inventory['train_seat_id'];
            $this->check(! isset($uniqueInventory[$unique]), 'Duplicate trip/seat inventory.');
            $uniqueInventory[$unique] = true;
            $tripInventoryCounts[$inventory['train_trip_id']] = ($tripInventoryCounts[$inventory['train_trip_id']] ?? 0) + 1;
        }
        $anchor = CarbonImmutable::parse($plan['as_of'], 'Asia/Kolkata')->setTimezone($plan['timezone'])->format('Y-m-d H:i:s');
        $physicalSchedules = [];
        foreach ($tables['train_trips'] as $key => $trip) {
            $this->check($trip['starts_at'] < $trip['ends_at'] && $trip['created_at'] < $trip['starts_at'], 'Invalid trip schedule.');
            $this->check($trip['status'] === ($trip['ends_at'] < $anchor ? 'completed' : 'scheduled'), 'Incorrect historical trip status.');
            $this->check(($tripInventoryCounts[RailwayDemoPlan::ref('train_trips', $key)] ?? 0) === 16, 'Trip does not have exactly sixteen inventory rows.');
            $physicalSchedules[$trip['train_id']][] = [$trip['starts_at'], $trip['ends_at']];
        }
        foreach ($physicalSchedules as $schedule) {
            usort($schedule, fn ($a, $b) => strcmp($a[0], $b[0]));
            $previousEnd = '';
            foreach ($schedule as [$start, $end]) {
                $this->check($previousEnd <= $start, 'Physical train has overlapping trips.');
                $previousEnd = $end;
            }
        }
        $passengers = [];
        foreach ($tables['train_booking_passengers'] as $person) {
            $this->person($person);
            $this->check($person['created_at'] === $this->row($tables, $person['train_booking_id'])['confirmed_at'], 'Passenger predates or postdates allocation.');
            $passengers[$person['train_booking_id']][] = $person;
        }
        $assignments = [];
        $intervals = [];
        $active = [];
        foreach ($tables['train_booking_seats'] as $assignment) {
            $booking = $this->row($tables, $assignment['train_booking_id']);
            $inventory = $this->row($tables, $assignment['trip_seat_inventory_id']);
            $person = $this->row($tables, $assignment['train_booking_passenger_id']);
            $seat = $this->row($tables, $inventory['train_seat_id']);
            $this->check($assignment['train_trip_id'] === $booking['train_trip_id'] && $assignment['train_trip_id'] === $inventory['train_trip_id'] && $person['train_booking_id'] === $assignment['train_booking_id'], 'Cross-trip or cross-booking seat assignment.');
            $this->check(! $person['requires_accessible_seat'] || $seat['is_accessible'], 'Inaccessible seat assigned.');
            $this->check($assignment['created_at'] === $booking['confirmed_at'] && $assignment['released_at'] === $booking['cancelled_at'], 'Seat allocation/release timestamp mismatch.');
            $assignments[$assignment['train_booking_id']][] = $assignment;
            $intervals[$assignment['trip_seat_inventory_id']][] = [$booking['confirmed_at'], $assignment['released_at'], $assignment['train_booking_id']];
            if ($assignment['released_at'] === null) {
                $this->check(! isset($active[$assignment['trip_seat_inventory_id']]), 'Duplicate active seat allocation.');
                $active[$assignment['trip_seat_inventory_id']] = $assignment['train_booking_id'];
            }
        }
        foreach ($tables['train_bookings'] as $key => $booking) {
            $ref = RailwayDemoPlan::ref('train_bookings', $key);
            $this->check(str_starts_with($booking['user_id'], '@users|s27.passenger.'), 'Bookings must belong to synthetic passengers.');
            $this->journey($tables, $routes, $booking, $plan['timezone']);
            $this->check($booking['booking_code'] === $key && strlen($key) <= 32, 'Invalid booking code.');
            $this->check(in_array($booking['status'], ['confirmed', 'cancelled'], true) && ($booking['status'] === 'cancelled') === ($booking['cancelled_at'] !== null), 'Inconsistent booking status.');
            $this->check($booking['created_at'] === $booking['confirmed_at'] && $booking['created_at'] < $booking['boarding_at'] && $booking['created_at'] <= $anchor, 'Invalid booking lead time.');
            $this->check($this->row($tables, $booking['user_id'])['created_at'] <= $booking['created_at'], 'Booking predates its account.');
            if ($booking['cancelled_at'] !== null) {
                $this->check($booking['confirmed_at'] < $booking['cancelled_at'] && $booking['cancelled_at'] < $booking['boarding_at'] && $booking['updated_at'] === $booking['cancelled_at'], 'Cancellation crosses boarding cutoff.');
            }
            $this->check($booking['seat_count'] >= 1 && $booking['seat_count'] <= 6 && count($passengers[$ref] ?? []) === $booking['seat_count'] && count($assignments[$ref] ?? []) === $booking['seat_count'], 'Passenger/seat count mismatch.');
            $this->check(count(array_unique(array_column($assignments[$ref], 'train_booking_passenger_id'))) === $booking['seat_count'], 'Passenger allocated more than once.');
        }
        foreach ($intervals as $reservations) {
            usort($reservations, fn ($a, $b) => strcmp($a[0], $b[0]));
            $end = '';
            foreach ($reservations as [$start, $release]) {
                $this->check($end !== null && $end <= $start, 'Overlapping trip-wide reservation history.');
                $end = $release;
            }
        }
        $this->check(count($active) === count($plan['inventory_pointers']), 'Final inventory count mismatch.');
        foreach ($plan['inventory_pointers'] as $inventory => $booking) {
            $this->check(($active[RailwayDemoPlan::ref('trip_seat_inventories', $inventory)] ?? null) === RailwayDemoPlan::ref('train_bookings', $booking), 'Final inventory pointer mismatch.');
        }
        $waitingPeople = [];
        foreach ($tables['train_waitlist_passengers'] as $person) {
            $this->person($person);
            $waitingPeople[$person['train_waitlist_entry_id']][] = $person;
        }
        $promotedBookings = [];
        $promotionTriggers = [];
        foreach ($tables['train_waitlist_entries'] as $key => $entry) {
            $this->journey($tables, $routes, $entry, $plan['timezone']);
            $people = $waitingPeople[RailwayDemoPlan::ref('train_waitlist_entries', $key)] ?? [];
            $this->check($this->row($tables, $entry['user_id'])['created_at'] <= $entry['created_at'], 'Waitlist predates its account.');
            $this->check(count($people) === $entry['seat_count'] && $entry['requires_accessible_seat'] === in_array(true, array_column($people, 'requires_accessible_seat'), true), 'Invalid waitlist passengers.');
            foreach ($people as $person) {
                $this->check($person['created_at'] === $entry['created_at'], 'Waitlist passenger timestamp mismatch.');
            }
            $this->check($entry['created_at'] < $entry['boarding_at'] && $entry['updated_at'] >= $entry['created_at'] && $entry['updated_at'] <= $anchor, 'Invalid waitlist chronology.');
            // All synthetic waiting requests were made against a fully occupied trip.
            $occupied = 0;
            foreach ($intervals as $inventoryRef => $reservations) {
                if ($this->row($tables, $inventoryRef)['train_trip_id'] !== $entry['train_trip_id']) {
                    continue;
                }
                foreach ($reservations as [$start, $release]) {
                    if ($start <= $entry['created_at'] && ($release === null || $release > $entry['created_at'])) {
                        $occupied++;
                    }
                }
            }
            $this->check($occupied === 16, 'Waitlist was joined while seats were available.');
            if ($entry['status'] === 'promoted') {
                $booking = $this->row($tables, $entry['promoted_booking_id']);
                $this->check(! isset($promotedBookings[$entry['promoted_booking_id']]), 'Booking promoted twice.');
                $promotedBookings[$entry['promoted_booking_id']] = true;
                foreach (['user_id', 'train_trip_id', 'boarding_station_id', 'destination_station_id', 'boarding_at', 'arrival_at', 'seat_count'] as $field) {
                    $this->check($booking[$field] === $entry[$field], 'Promotion changed journey or owner.');
                }
                foreach (['name', 'age', 'gender', 'requires_accessible_seat'] as $field) {
                    $this->check($passengers[$entry['promoted_booking_id']][0][$field] === $people[0][$field], 'Promotion changed passenger details.');
                }
                $this->check($entry['promoted_at'] === $booking['confirmed_at'] && $entry['created_at'] < $entry['promoted_at'] && $entry['promoted_at'] < $entry['boarding_at'], 'Promotion crosses boarding cutoff.');
                $trigger = null;
                $allocation = $assignments[$entry['promoted_booking_id']][0];
                foreach ($intervals[$allocation['trip_seat_inventory_id']] as [, $release, $previousBooking]) {
                    if ($release === $entry['promoted_at']) {
                        $trigger = $previousBooking;
                    }
                }
                $this->check($trigger !== null && ! isset($promotionTriggers[$trigger]), 'Promotion needs exactly one distinct cancellation trigger.');
                $promotionTriggers[$trigger] = true;
                foreach ($tables['train_waitlist_entries'] as $earlier) {
                    if ($earlier['train_trip_id'] !== $entry['train_trip_id'] || $earlier['created_at'] >= $entry['created_at']) {
                        continue;
                    }
                    $this->check(($earlier['status'] === 'promoted' && $earlier['promoted_at'] < $entry['promoted_at']) || ($earlier['status'] === 'cancelled' && $earlier['updated_at'] < $entry['promoted_at']), 'Promotion skipped an earlier eligible request.');
                }
            } else {
                $this->check(in_array($entry['status'], ['waiting', 'cancelled'], true) && $entry['promoted_at'] === null && $entry['promoted_booking_id'] === null, 'False waitlist confirmation.');
                if ($entry['status'] === 'cancelled') {
                    $this->check($entry['updated_at'] < $entry['boarding_at'], 'Synthetic waitlist withdrawal crosses departure.');
                }
            }
        }
        $this->check(count(array_filter($tables['train_bookings'], fn ($r) => $r['status'] === 'cancelled')) === 200, 'Unexpected cancellation total.');
        $this->check(count($promotedBookings) === 30 && count(array_filter($tables['train_waitlist_entries'], fn ($r) => $r['status'] === 'cancelled')) === 20, 'Unexpected waitlist outcomes.');
    }

    private function journey(array $tables, array $routes, array $record, string $timezone): void
    {
        $trip = $this->row($tables, $record['train_trip_id']);
        $stops = $routes[$trip['train_id']];
        $from = array_search($record['boarding_station_id'], array_column($stops, 'station_id'), true);
        $to = array_search($record['destination_station_id'], array_column($stops, 'station_id'), true);
        $this->check($from !== false && $to !== false && $from < $to, 'Reversed or missing journey stop.');
        $start = CarbonImmutable::parse($trip['starts_at'], $timezone);
        $this->check($record['boarding_at'] === $start->addMinutes($stops[$from]['departure_offset_minutes'])->format('Y-m-d H:i:s') && $record['arrival_at'] === $start->addMinutes($stops[$to]['arrival_offset_minutes'])->format('Y-m-d H:i:s') && $record['boarding_at'] < $record['arrival_at'] && $record['arrival_at'] <= $trip['ends_at'], 'Journey snapshot does not match its route.');
    }

    private function person(array $person): void
    {
        $this->check(str_starts_with($person['name'], 'Demo ') && $person['age'] >= 0 && $person['age'] <= 120 && is_bool($person['requires_accessible_seat']), 'Invalid fictional passenger.');
    }

    private function split(string $reference): array
    {
        return explode('|', substr($reference, 1), 2);
    }

    private function row(array $tables, string $reference): array
    {
        [$table, $key] = $this->split($reference);

        return $tables[$table][$key];
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new RuntimeException($message);
        }
    }
}
