<?php

namespace App\Services\Demo;

use Carbon\CarbonImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Random\Engine\Mt19937;
use Random\Randomizer;

/** Pure, deterministic planning. No container, database, filesystem writes or credentials. */
class RailwayDemoPlan
{
    public const VERSION = 'S27-v1';

    public const DEFAULT_SEED = 27001;

    public const COUNTS = [
        'stations' => 100, 'users' => 105, 'participants' => 105, 'role_user' => 105,
        'trains' => 40, 'train_route_stops' => 200, 'train_coaches' => 80, 'train_seats' => 640,
        'train_trips' => 300, 'trip_seat_inventories' => 4800, 'train_bookings' => 1500,
        'train_booking_passengers' => 1696, 'train_booking_seats' => 1696,
        'train_waitlist_entries' => 150, 'train_waitlist_passengers' => 150,
    ];

    private array $tables;

    private array $pointers;

    private Randomizer $random;

    private CarbonImmutable $anchor;

    private string $timezone;

    private int $bookingNumber;

    public static function ref(string $table, string $key): string
    {
        return '@'.$table.'|'.$key;
    }

    public function build(string $asOf, int $seed = self::DEFAULT_SEED, string $timezone = 'UTC'): array
    {
        if ($seed < 0 || $seed > 2147483647 || ! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $asOf)) {
            throw new InvalidArgumentException('Use a YYYY-MM-DD anchor and a seed from 0 to 2147483647.');
        }
        new DateTimeZone($timezone);
        $this->anchor = CarbonImmutable::createFromFormat('!Y-m-d', $asOf, 'Asia/Kolkata');
        if ($this->anchor->format('Y-m-d') !== $asOf) {
            throw new InvalidArgumentException('The anchor must be a real calendar date.');
        }
        $this->timezone = $timezone;
        $this->random = new Randomizer(new Mt19937($seed));
        $this->tables = array_fill_keys(array_keys(self::COUNTS), []);
        $this->pointers = [];
        $this->bookingNumber = 0;
        $this->accounts();
        $trains = $this->catalog();
        $trips = [];
        foreach ($trains as $trainIndex => $train) {
            for ($round = 0; $round < 6; $round++) {
                $trips[] = [$trainIndex, $train, -210 + $trainIndex * 3 + $round * 14];
            }
        }
        foreach ($trains as $trainIndex => $train) {
            $trips[] = [$trainIndex, $train, 1 + $trainIndex % 5];
        }
        foreach (array_slice($trains, 0, 20) as $trainIndex => $train) {
            $trips[] = [$trainIndex, $train, 14 + $trainIndex % 5];
        }
        $partial = 0;
        $partialBooking = 0;
        $full = 0;
        foreach ($trips as $index => [$trainIndex, $train, $days]) {
            $trip = $this->trip($index, $trainIndex, $train, $days);
            if ($index < 10 || ($index >= 240 && $index < 250)) {
                $this->saturatedTrip($trip, $full++);

                continue;
            }
            if ($partial < 170) {
                $this->booking($trip, [0], $trip['start']->subDays(45), $trip['start']->subDays(40));
            }
            $seat = 0;
            for ($number = 0; $number < ($partial < 140 ? 4 : 3); $number++) {
                $count = $partialBooking++ % 5 === 0 ? 2 : 1;
                $created = $trip['start']->subDays($this->random->getInt(7, 35));
                $created = $created->min($this->anchor->subDay());
                $this->booking($trip, range($seat, $seat + $count - 1), $created);
                $seat += $count;
            }
            $partial++;
        }

        return [
            'version' => self::VERSION, 'seed' => $seed, 'as_of' => $asOf, 'timezone' => $timezone,
            'tables' => $this->tables, 'inventory_pointers' => $this->pointers,
        ];
    }

    public static function fingerprint(array $plan): string
    {
        return hash('sha256', json_encode($plan, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    private function accounts(): void
    {
        $created = $this->stamp($this->anchor->subDays(400));
        foreach (['passenger' => 100, 'owner' => 5] as $kind => $count) {
            for ($index = 1; $index <= $count; $index++) {
                $email = self::email($kind, $index);
                $name = 'Synthetic '.ucfirst($kind).' '.sprintf('%03d', $index).' — '.$this->person()['name'];
                $this->tables['users'][$email] = [
                    'name' => $name, 'email' => $email, 'verification_status' => false,
                    'train_owner_approved_at' => null, 'created_at' => $created, 'updated_at' => $created,
                ];
                $this->tables['participants'][$email] = [
                    'user_id' => self::ref('users', $email), 'name' => $name, 'email' => $email,
                    'is_approved' => false, 'approved_at' => null, 'train_access_enabled' => $kind === 'passenger',
                    'created_at' => $created, 'updated_at' => $created,
                ];
                $this->tables['role_user'][$email] = ['user_id' => self::ref('users', $email), 'role_id' => self::ref('roles', $kind)];
            }
        }
    }

    public static function email(string $kind, int $number): string
    {
        return sprintf('s27.%s.%03d@example.invalid', $kind, $number);
    }

    private function catalog(): array
    {
        $corridors = require dirname(__DIR__, 3).'/database/demo/indian-railway-corridors.php';
        $created = $this->stamp($this->anchor->subDays(400));
        $trains = [];
        foreach ($corridors as $index => [$name, $cities, $states, $legs]) {
            $stationCodes = [];
            foreach ($cities as $stop => $city) {
                $code = sprintf('S27-%03d', $index * 5 + $stop + 1);
                $this->tables['stations'][$code] = [
                    'code' => $code, 'name' => $city.' Demo Junction', 'city' => $city,
                    'state' => $states[$stop] ?? $states[0], 'is_active' => true,
                    'created_at' => $created, 'updated_at' => $created,
                ];
                $stationCodes[] = $code;
            }
            foreach ([false, true] as $reverse) {
                $trainIndex = count($trains);
                $code = 'S27-'.(90001 + $trainIndex);
                $route = $reverse ? array_reverse($stationCodes) : $stationCodes;
                $running = $reverse ? array_reverse($legs) : $legs;
                $this->tables['trains'][$code] = [
                    'code' => $code, 'name' => 'Synthetic '.$name.($reverse ? ' Return ' : ' Link ').(90001 + $trainIndex),
                    'owner_user_id' => self::ref('users', self::email('owner', 1 + intdiv($trainIndex, 8))),
                    'is_active' => true, 'created_at' => $created, 'updated_at' => $created,
                ];
                $offset = 0;
                $stops = [];
                foreach ($route as $stop => $station) {
                    if ($stop > 0) {
                        $offset += $running[$stop - 1];
                    }
                    $arrival = $offset;
                    if ($stop > 0 && $stop < 4) {
                        $offset += 5;
                    }
                    $stops[] = ['station' => $station, 'arrival' => $arrival, 'departure' => $offset];
                    $this->tables['train_route_stops'][$code.':'.$stop] = [
                        'train_id' => self::ref('trains', $code), 'station_id' => self::ref('stations', $station),
                        'stop_order' => $stop + 1, 'arrival_offset_minutes' => $stop === 0 ? null : $arrival,
                        'departure_offset_minutes' => $stop === 4 ? null : $offset,
                        'created_at' => $created, 'updated_at' => $created,
                    ];
                }
                $seats = [];
                foreach (['C1' => 'CC', 'S1' => 'SL'] as $coach => $class) {
                    $coachKey = $code.':'.$coach;
                    $this->tables['train_coaches'][$coachKey] = [
                        'train_id' => self::ref('trains', $code), 'code' => $coach, 'class_code' => $class,
                        'sort_order' => $coach === 'C1' ? 1 : 2, 'created_at' => $created, 'updated_at' => $created,
                    ];
                    for ($seat = 1; $seat <= 8; $seat++) {
                        $seatKey = $coachKey.':'.$seat;
                        $this->tables['train_seats'][$seatKey] = [
                            'train_id' => self::ref('trains', $code), 'train_coach_id' => self::ref('train_coaches', $coachKey),
                            'number' => (string) $seat, 'berth_type' => $class === 'CC' ? ($seat % 2 ? 'window' : 'aisle') : ['lower', 'middle', 'upper'][($seat - 1) % 3],
                            'is_accessible' => $seat === 1, 'created_at' => $created, 'updated_at' => $created,
                        ];
                        $seats[] = $seatKey;
                    }
                }
                $trains[] = ['code' => $code, 'stops' => $stops, 'seats' => $seats, 'duration' => $offset];
            }
        }

        return $trains;
    }

    private function trip(int $index, int $trainIndex, array $train, int $days): array
    {
        $key = sprintf('S27-T%03d', $index + 1);
        $start = $this->anchor->addDays($days)->setTime(6 + $trainIndex % 10, ($trainIndex % 4) * 15);
        $created = $this->stamp($start->subDays(90));
        $end = $start->addMinutes($train['duration']);
        $this->tables['train_trips'][$key] = [
            'train_id' => self::ref('trains', $train['code']), 'starts_at' => $this->stamp($start), 'ends_at' => $this->stamp($end),
            'status' => $days < 0 ? 'completed' : 'scheduled', 'created_at' => $created,
            'updated_at' => $days < 0 ? $this->stamp($end) : $created,
        ];
        foreach ($train['seats'] as $seatIndex => $seat) {
            $this->tables['trip_seat_inventories'][$key.':'.$seatIndex] = [
                'train_trip_id' => self::ref('train_trips', $key), 'train_id' => self::ref('trains', $train['code']),
                'train_seat_id' => self::ref('train_seats', $seat), 'status' => 'available', 'current_booking_id' => null,
                'created_at' => $created, 'updated_at' => $created,
            ];
        }

        return ['key' => $key, 'train' => $train, 'start' => $start];
    }

    private function journey(array $trip): array
    {
        $from = $this->random->getInt(0, 2);
        $to = $this->random->getInt($from + 1, 4);
        $stops = $trip['train']['stops'];

        return [
            'boarding_station_id' => self::ref('stations', $stops[$from]['station']),
            'destination_station_id' => self::ref('stations', $stops[$to]['station']),
            'boarding_at' => $this->stamp($trip['start']->addMinutes($stops[$from]['departure'])),
            'arrival_at' => $this->stamp($trip['start']->addMinutes($stops[$to]['arrival'])),
        ];
    }

    private function booking(array $trip, array $seats, CarbonImmutable $created, ?CarbonImmutable $cancelled = null, ?array $waiting = null): string
    {
        $key = sprintf('S27-B%05d', ++$this->bookingNumber);
        $user = self::ref('users', self::email('passenger', 1 + ($this->bookingNumber - 1) % 100));
        $journey = $waiting['journey'] ?? $this->journey($trip);
        $this->tables['train_bookings'][$key] = [
            'booking_code' => $key, 'user_id' => $waiting['user'] ?? $user, 'train_trip_id' => self::ref('train_trips', $trip['key']),
            ...$journey, 'status' => $cancelled ? 'cancelled' : 'confirmed', 'seat_count' => count($seats),
            'confirmed_at' => $this->stamp($created), 'cancelled_at' => $cancelled ? $this->stamp($cancelled) : null,
            'created_at' => $this->stamp($created), 'updated_at' => $this->stamp($cancelled ?? $created),
        ];
        foreach ($seats as $number => $seatIndex) {
            $person = $waiting['person'] ?? $this->person();
            $person['requires_accessible_seat'] = $waiting['person']['requires_accessible_seat'] ?? ($seatIndex % 8 === 0 && $this->bookingNumber % 3 === 0);
            $passengerKey = $key.':'.$number;
            $this->tables['train_booking_passengers'][$passengerKey] = [
                'train_booking_id' => self::ref('train_bookings', $key), ...$person,
                'created_at' => $this->stamp($created), 'updated_at' => $this->stamp($created),
            ];
            $inventory = $trip['key'].':'.$seatIndex;
            $this->tables['train_booking_seats'][$passengerKey] = [
                'train_booking_id' => self::ref('train_bookings', $key), 'train_trip_id' => self::ref('train_trips', $trip['key']),
                'trip_seat_inventory_id' => self::ref('trip_seat_inventories', $inventory),
                'train_booking_passenger_id' => self::ref('train_booking_passengers', $passengerKey),
                'released_at' => $cancelled ? $this->stamp($cancelled) : null,
                'created_at' => $this->stamp($created), 'updated_at' => $this->stamp($cancelled ?? $created),
            ];
            if ($cancelled === null) {
                $this->pointers[$inventory] = $key;
            }
        }

        return $key;
    }

    private function saturatedTrip(array $trip, int $index): void
    {
        $promotions = $index % 2 === 0 ? 2 : 1;
        $initial = $trip['start']->subDays(21);
        for ($seat = 0; $seat < 16; $seat++) {
            $promotion = $seat - (16 - $promotions);
            $cancelled = $promotion >= 0 ? $trip['start']->subDays(7)->addMinutes($promotion) : null;
            $this->booking($trip, [$seat], $initial->addMinutes($seat), $cancelled);
        }
        for ($number = 0; $number < ($index < 10 ? 8 : 7); $number++) {
            $key = sprintf('S27-W%03d', count($this->tables['train_waitlist_entries']) + 1);
            $created = $trip['start']->subDays(14)->addMinutes($number);
            $person = $this->person();
            $person['requires_accessible_seat'] = $number >= $promotions && $number % 4 === 0;
            $journey = $this->journey($trip);
            $user = self::ref('users', self::email('passenger', 1 + (count($this->tables['train_waitlist_entries']) % 100)));
            $promoted = null;
            $promotedAt = null;
            $updated = $created;
            $status = 'waiting';
            if ($number < $promotions) {
                $updated = $trip['start']->subDays(7)->addMinutes($number);
                $promotedAt = $this->stamp($updated);
                $promoted = $this->booking($trip, [16 - $promotions + $number], $updated, null, compact('journey', 'person', 'user'));
                $status = 'promoted';
            } elseif ($number === $promotions) {
                $updated = $trip['start']->subDays(8);
                $status = 'cancelled';
            }
            $this->tables['train_waitlist_entries'][$key] = [
                'user_id' => $user, 'train_trip_id' => self::ref('train_trips', $trip['key']), ...$journey,
                'seat_count' => 1, 'requires_accessible_seat' => $person['requires_accessible_seat'], 'status' => $status,
                'promoted_booking_id' => $promoted ? self::ref('train_bookings', $promoted) : null, 'promoted_at' => $promotedAt,
                'created_at' => $this->stamp($created), 'updated_at' => $this->stamp($updated),
            ];
            $this->tables['train_waitlist_passengers'][$key] = [
                'train_waitlist_entry_id' => self::ref('train_waitlist_entries', $key), ...$person,
                'created_at' => $this->stamp($created), 'updated_at' => $this->stamp($created),
            ];
        }
    }

    private function person(): array
    {
        $first = ['Aarav', 'Aditi', 'Arjun', 'Ananya', 'Dev', 'Diya', 'Ishaan', 'Isha', 'Kabir', 'Kavya', 'Neel', 'Nisha', 'Rohan', 'Riya', 'Sameer', 'Sana', 'Varun', 'Vidya', 'Vivek', 'Zoya'];
        $last = ['Sharma', 'Patil', 'Nair', 'Rao', 'Das', 'Sen', 'Gupta', 'Mehta', 'Joshi', 'Kulkarni', 'Iyer', 'Menon', 'Reddy', 'Singh', 'Khan', 'Shah', 'Saha', 'Pillai', 'Bose', 'Verma'];

        return [
            'name' => 'Demo '.$first[$this->random->getInt(0, 19)].' '.$last[$this->random->getInt(0, 19)],
            'age' => $this->random->getInt(5, 82), 'gender' => ['female', 'male', 'other'][$this->random->getInt(0, 2)],
            'requires_accessible_seat' => false,
        ];
    }

    private function stamp(CarbonImmutable $time): string
    {
        return $time->setTimezone($this->timezone)->format('Y-m-d H:i:s');
    }
}
