<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class IndianTrainDemoSeederPreflight
{
    private const TRAIN_CODES = ['SS-IN-DEMO-01', 'SS-IN-DEMO-02'];

    /** Lock and check only the isolated Indian demo services before they can be updated. */
    public function assertSafeToUpdate(): void
    {
        $trainIds = DB::table('trains')->whereIn('code', self::TRAIN_CODES)
            ->orderBy('id')->lockForUpdate()->pluck('id')->all();
        $tripIds = $trainIds === [] ? [] : DB::table('train_trips')->whereIn('train_id', $trainIds)
            ->orderBy('id')->lockForUpdate()->pluck('id')->all();
        $seatIds = $trainIds === [] ? [] : DB::table('train_seats')->whereIn('train_id', $trainIds)
            ->orderBy('id')->lockForUpdate()->pluck('id')->all();

        $bookings = $tripIds === [] ? collect() : DB::table('train_bookings')->whereIn('train_trip_id', $tripIds)
            ->orderBy('id')->lockForUpdate()->get(['id']);
        $bookingIds = $bookings->pluck('id')->all();
        $waitlist = $tripIds === [] ? collect() : DB::table('train_waitlist_entries')->whereIn('train_trip_id', $tripIds)
            ->orderBy('id')->lockForUpdate()->get(['id']);
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

        $unsafeInventory = $inventory->filter(
            fn ($row) => $row->status !== 'available' || $row->current_booking_id !== null
        )->count();

        $unsafe = [
            'bookings' => $bookings->count(),
            'waitlist entries' => $waitlist->count(),
            'booking-seat assignments' => $assignments->count(),
            'booking passengers' => $bookingPassengers->count(),
            'waitlist passengers' => $waitlistPassengers->count(),
            'booked or inconsistent inventory rows' => $unsafeInventory,
        ];
        $unsafe = array_filter($unsafe, static fn (int $count): bool => $count > 0);

        if ($unsafe !== []) {
            $summary = implode(', ', array_map(
                static fn (string $kind, int $count): string => "{$count} {$kind}",
                array_keys($unsafe),
                array_values($unsafe),
            ));

            throw new RuntimeException(
                'IndianTrainDemoSeeder stopped before writing because its reserved demo services have related history: '
                .$summary.'. Preserve the records and review the demo keys before rerunning.'
            );
        }
    }
}
