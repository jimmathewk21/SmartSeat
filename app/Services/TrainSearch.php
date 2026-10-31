<?php

namespace App\Services;

use App\Models\Station;
use App\Models\TrainTrip;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TrainSearch
{
    public function find(array $criteria): LengthAwarePaginator
    {
        $origin = Station::where('code', $criteria['origin'])->value('id');
        $destination = Station::where('code', $criteria['destination'])->value('id');
        $date = CarbonImmutable::parse($criteria['journey_date'], Config::get('smartseat.railway_timezone'));
        $boardingDate = DB::connection()->getDriverName() === 'sqlite'
            ? "date(train_trips.service_date, '+' || CAST(origin_stop.departure_offset_minutes / 1440 AS INTEGER) || ' days')"
            : 'DATE_ADD(train_trips.service_date, INTERVAL FLOOR(origin_stop.departure_offset_minutes / 1440) DAY)';

        $trips = TrainTrip::query()->select('train_trips.*')
            ->addSelect('origin_stop.departure_offset_minutes as boarding_offset', 'destination_stop.arrival_offset_minutes as arrival_offset')
            ->join('trains', 'trains.id', '=', 'train_trips.train_id')
            ->join('train_route_stops as origin_stop', 'origin_stop.train_id', '=', 'trains.id')
            ->join('train_route_stops as destination_stop', 'destination_stop.train_id', '=', 'trains.id')
            ->where('trains.is_active', true)->where('train_trips.status', 'scheduled')
            ->whereBetween('train_trips.service_date', [$date->subDays(30)->toDateString(), $date->toDateString()])
            ->where('origin_stop.station_id', $origin)->where('destination_stop.station_id', $destination)
            ->whereColumn('origin_stop.stop_order', '<', 'destination_stop.stop_order')
            ->whereRaw($boardingDate.' = ?', [$criteria['journey_date']])
            ->with('train:id,number,name')
            ->orderBy('origin_stop.departure_offset_minutes')->orderBy('train_trips.id')
            ->paginate(20)->withQueryString();

        return $trips->through(function (TrainTrip $trip) {
            $midnight = CarbonImmutable::parse($trip->service_date->toDateString(), Config::get('smartseat.railway_timezone'));
            $trip->boarding_at = $midnight->addMinutes((int) $trip->boarding_offset);
            $trip->arrival_at = $midnight->addMinutes((int) $trip->arrival_offset);

            return $trip;
        });
    }

    /** Aggregate inventory for the current page only; never load every seat. */
    public function coaches(array $tripIds): Collection
    {
        return DB::table('trip_seat_inventory as inventory')
            ->join('seats', 'seats.id', '=', 'inventory.seat_id')
            ->join('coaches', 'coaches.id', '=', 'seats.coach_id')
            ->leftJoin('seat_assignments as assignment', function ($join) {
                $join->on('assignment.inventory_id', '=', 'inventory.id')->where('assignment.status', 'active');
            })
            ->whereIn('inventory.trip_id', $tripIds)
            ->select('inventory.trip_id', 'coaches.id', 'coaches.label', 'coaches.travel_class')
            ->selectRaw('COUNT(*) as total_seats')
            ->selectRaw("SUM(CASE WHEN inventory.status = 'available' AND assignment.id IS NULL THEN 1 ELSE 0 END) as available_seats")
            ->groupBy('inventory.trip_id', 'coaches.id', 'coaches.label', 'coaches.travel_class')
            ->orderBy('coaches.label')->get()->groupBy('trip_id');
    }
}
