<?php

namespace App\Services;

use App\Models\TrainSeat;
use App\Models\TrainTrip;
use Illuminate\Support\Facades\DB;

class TrainInventoryService
{
    /** Create any missing trip-seat inventory rows; safe to call more than once. */
    public function initializeForTrip(TrainTrip $trip): int
    {
        $now = now();
        $rows = TrainSeat::query()
            ->where('train_id', $trip->train_id)
            ->orderBy('id')
            ->get(['id'])
            ->map(fn (TrainSeat $seat) => [
                'train_trip_id' => $trip->id,
                'train_id' => $trip->train_id,
                'train_seat_id' => $seat->id,
                'status' => 'available',
                'current_booking_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($rows === []) {
            return 0;
        }

        return DB::table('trip_seat_inventories')->insertOrIgnore($rows);
    }
}
