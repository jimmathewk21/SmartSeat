<?php

namespace App\Services;

use App\Models\Station;
use App\Models\TrainTrip;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainSearchService
{
    public function stations(): Collection
    {
        return Station::query()
            ->where('is_active', true)
            ->whereNotIn('code', config('train.legacy_search_excluded_station_codes', []))
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'city']);
    }

    public function search(int $fromStationId, int $toStationId, string $date): Collection
    {
        $departureDate = Carbon::parse($date)->startOfDay();
        $nextDate = $departureDate->copy()->addDay();
        $maxOriginOffset = (int) (DB::table('train_route_stops')
            ->where('station_id', $fromStationId)
            ->selectRaw('MAX(COALESCE(departure_offset_minutes, arrival_offset_minutes, 0)) as max_offset')
            ->value('max_offset') ?? 0);

        return TrainTrip::query()
            ->with([
                'train:id,code,name',
                'train.routeStops:id,train_id,station_id,stop_order,arrival_offset_minutes,departure_offset_minutes',
                'train.routeStops.station:id,code,name,city',
                'inventories' => fn ($query) => $query->where('status', 'available')
                    ->with(['seat:id,train_coach_id,number,berth_type,is_accessible', 'seat.coach:id,code,class_code,sort_order'])
                    ->orderBy('train_seat_id'),
            ])
            ->where('status', TrainTrip::STATUS_SCHEDULED)
            // A trip may start on an earlier calendar day and reach this origin on the requested date.
            ->whereBetween('starts_at', [$departureDate->copy()->subMinutes($maxOriginOffset), $nextDate])
            ->whereHas('train', fn ($query) => $query
                ->where('is_active', true)
                ->whereNotIn('code', config('train.legacy_search_excluded_train_codes', [])))
            ->whereExists(function ($query) use ($fromStationId, $toStationId) {
                $query->selectRaw('1')
                    ->from('train_route_stops as origin_stop')
                    ->join('train_route_stops as destination_stop', function ($join) {
                        $join->on('destination_stop.train_id', '=', 'origin_stop.train_id')
                            ->whereColumn('destination_stop.stop_order', '>', 'origin_stop.stop_order');
                    })
                    ->whereColumn('origin_stop.train_id', 'train_trips.train_id')
                    ->where('origin_stop.station_id', $fromStationId)
                    ->where('destination_stop.station_id', $toStationId);
            })
            ->orderBy('starts_at')
            ->get()
            ->map(function (TrainTrip $trip) use ($fromStationId, $toStationId) {
                $stops = $trip->train->routeStops;
                $origin = $stops->firstWhere('station_id', $fromStationId);
                $destination = $stops->firstWhere('station_id', $toStationId);
                $originOffset = (int) ($origin?->departure_offset_minutes ?? $origin?->arrival_offset_minutes ?? 0);
                $destinationOffset = (int) ($destination?->arrival_offset_minutes ?? $destination?->departure_offset_minutes ?? 0);
                $trip->setAttribute('search_departure_at', Carbon::parse($trip->starts_at)->addMinutes($originOffset));
                $trip->setAttribute('search_arrival_at', Carbon::parse($trip->starts_at)->addMinutes($destinationOffset));
                $trip->setAttribute('origin_station', $origin?->station);
                $trip->setAttribute('destination_station', $destination?->station);

                return $trip;
            })
            ->filter(fn (TrainTrip $trip) => $trip->search_departure_at->isSameDay($departureDate) && $trip->search_departure_at->isFuture())
            ->sortBy('search_departure_at')
            ->take(50)
            ->values();
    }
}
