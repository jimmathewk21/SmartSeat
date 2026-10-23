<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class TrainDemoSeederPreflight
{
    private const TRAIN_CODES = ['SS-DEMO-01', 'SS-DEMO-02'];
    // Include retired fictional station keys so existing reserved records remain preflighted.
    private const STATION_CODES = ['SSDEMO01', 'SSDEMO02', 'SSDEMO03', 'SSDEMO04', 'CSMT', 'PUNE', 'NK', 'NGP'];

    /** Inspect and lock every reserved demo record before the seeder writes anything. */
    public function assertSafeToUpdate(): void
    {
        $trainIds = DB::table('trains')->whereIn('code', self::TRAIN_CODES)
            ->orderBy('id')->lockForUpdate()->pluck('id')->all();
        $stationIds = DB::table('stations')->whereIn('code', self::STATION_CODES)
            ->orderBy('id')->lockForUpdate()->pluck('id')->all();
        $tripIds = $trainIds === [] ? [] : DB::table('train_trips')->whereIn('train_id', $trainIds)
            ->orderBy('id')->lockForUpdate()->pluck('id')->all();
        $coachIds = $trainIds === [] ? [] : DB::table('train_coaches')->whereIn('train_id', $trainIds)
            ->orderBy('id')->lockForUpdate()->pluck('id')->all();
        $seatIds = $trainIds === [] ? [] : DB::table('train_seats')->whereIn('train_id', $trainIds)
            ->orderBy('id')->lockForUpdate()->pluck('id')->all();

        $routeStops = ($trainIds === [] && $stationIds === []) ? collect() : DB::table('train_route_stops')
            ->when($trainIds !== [], fn ($query) => $query->whereIn('train_id', $trainIds))
            ->when($stationIds !== [], fn ($query) => $query->orWhereIn('station_id', $stationIds))
            ->orderBy('id')->lockForUpdate()->get(['id', 'train_id', 'station_id']);

        $bookings = $tripIds === [] ? collect() : DB::table('train_bookings')
            ->whereIn('train_trip_id', $tripIds)->orderBy('id')->lockForUpdate()->get(['id']);
        $bookingIds = $bookings->pluck('id')->all();
        $waitlist = $tripIds === [] ? collect() : DB::table('train_waitlist_entries')
            ->whereIn('train_trip_id', $tripIds)->orderBy('id')->lockForUpdate()->get(['id']);
        $waitlistIds = $waitlist->pluck('id')->all();

        $inventory = ($tripIds === [] && $trainIds === [] && $seatIds === []) ? collect() : DB::table('trip_seat_inventories')
            ->when($tripIds !== [], fn ($query) => $query->whereIn('train_trip_id', $tripIds))
            ->when($trainIds !== [], fn ($query) => $query->orWhereIn('train_id', $trainIds))
            ->when($seatIds !== [], fn ($query) => $query->orWhereIn('train_seat_id', $seatIds))
            ->orderBy('id')->lockForUpdate()->get(['id', 'status', 'current_booking_id']);
        $inventoryIds = $inventory->pluck('id')->all();

        $assignments = ($tripIds === [] && $bookingIds === [] && $inventoryIds === []) ? collect() : DB::table('train_booking_seats')
            ->when($tripIds !== [], fn ($query) => $query->whereIn('train_trip_id', $tripIds))
            ->when($bookingIds !== [], fn ($query) => $query->orWhereIn('train_booking_id', $bookingIds))
            ->when($inventoryIds !== [], fn ($query) => $query->orWhereIn('trip_seat_inventory_id', $inventoryIds))
            ->orderBy('id')->lockForUpdate()->get(['id']);
        $bookingPassengers = $bookingIds === [] ? collect() : DB::table('train_booking_passengers')
            ->whereIn('train_booking_id', $bookingIds)->orderBy('id')->lockForUpdate()->get(['id']);
        $waitlistPassengers = $waitlistIds === [] ? collect() : DB::table('train_waitlist_passengers')
            ->whereIn('train_waitlist_entry_id', $waitlistIds)->orderBy('id')->lockForUpdate()->get(['id']);

        // Reserved stations must not be shared by unrelated train routes that an upsert could rename/deactivate.
        $sharedStationRoutes = ($stationIds === [] || $trainIds === []) ? 0 : DB::table('train_route_stops')
            ->join('trains', 'trains.id', '=', 'train_route_stops.train_id')
            ->whereIn('train_route_stops.station_id', $stationIds)
            ->whereNotIn('trains.id', $trainIds)->count();

        $unsafeInventory = $inventory->filter(
            fn ($row) => $row->status !== 'available' || $row->current_booking_id !== null
        )->count();

        self::assertSafeCounts([
            'bookings' => $bookings->count(),
            'waitlist entries' => $waitlist->count(),
            'booking-seat assignments' => $assignments->count(),
            'booking passengers' => $bookingPassengers->count(),
            'waitlist passengers' => $waitlistPassengers->count(),
            'booked or inconsistent inventory rows' => $unsafeInventory,
            'route stops using reserved stations from other trains' => $sharedStationRoutes,
        ]);
    }

    /** Pure policy check so unsafe-state handling can be tested without a database. */
    public static function assertSafeCounts(array $counts): void
    {
        $unsafe = array_filter($counts, static fn (int $count): bool => $count > 0);

        if ($unsafe === []) {
            return;
        }

        $summary = implode(', ', array_map(
            static fn (string $kind, int $count): string => "{$count} {$kind}",
            array_keys($unsafe),
            array_values($unsafe),
        ));

        throw new RuntimeException(
            'TrainDemoSeeder stopped before writing because reserved demo data has related booking history or shared records: '
            .$summary.'. Review the listed train-only records and preserve their history before rerunning.'
        );
    }
}
