<?php

namespace App\Services;

use App\Models\Coach;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainRouteStop;
use App\Models\TrainTrip;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RailwayCatalogue
{
    public function saveStation(array $data, ?Station $station = null): Station
    {
        return DB::transaction(function () use ($data, $station) {
            if ($station) {
                $station = Station::whereKey($station->id)->lockForUpdate()->firstOrFail();
                if ($station->routeStops()->exists()) {
                    $this->reject('code', 'This station is in a route and cannot be changed.');
                }
            }

            $station ??= new Station;
            $station->fill($data)->save();

            return $station;
        });
    }

    public function saveTrain(array $data, ?Train $train = null): Train
    {
        return DB::transaction(function () use ($data, $train) {
            $train = $train ? $this->editableTrain($train) : new Train;
            $train->fill($data)->save();

            return $train;
        });
    }

    public function saveStop(Train $train, array $data, ?TrainRouteStop $stop = null): void
    {
        DB::transaction(function () use ($train, $data, $stop) {
            $train = $this->editableTrain($train);
            // Lock the station too, so a concurrent rename cannot race route creation.
            $station = Station::where('code', $data['station_code'])->lockForUpdate()->firstOrFail();
            unset($data['station_code']);
            $data['station_id'] = $station->id;
            $stop ??= new TrainRouteStop;
            $stop->fill($data);
            $stop->train()->associate($train);
            $stop->save();

            $previousDeparture = -1;
            foreach ($train->routeStops()->orderBy('stop_order')->get() as $routeStop) {
                if ($routeStop->arrival_offset_minutes < $previousDeparture) {
                    $this->reject('arrival_offset_minutes', 'Stop times must increase in route order, including overnight day offsets.');
                }
                $previousDeparture = $routeStop->departure_offset_minutes;
            }
        });
    }

    public function saveCoach(Train $train, array $data, ?Coach $coach = null): void
    {
        DB::transaction(function () use ($train, $data, $coach) {
            $train = $this->editableTrain($train);
            if (! $coach && $train->coaches()->count() >= 30) {
                $this->reject('label', 'A train can contain at most 30 coaches.');
            }
            $coach ??= new Coach;
            $coach->fill($data);
            $coach->train()->associate($train);
            $coach->save();
        });
    }

    public function saveSeat(Train $train, Coach $coach, array $data, ?Seat $seat = null): void
    {
        DB::transaction(function () use ($train, $coach, $data, $seat) {
            $train = $this->editableTrain($train);
            abort_unless($coach->train_id === $train->id, 404);
            $seat ??= new Seat;
            $seat->fill($data);
            $seat->coach()->associate($coach);
            $seat->train()->associate($train);
            $seat->save();
        });
    }

    public function schedule(Train $train, array $data): TrainTrip
    {
        return DB::transaction(function () use ($train, $data) {
            $train = Train::whereKey($train->id)->lockForUpdate()->firstOrFail();
            if (! $train->is_active || $train->routeStops()->count() < 2 || ! $train->seats()->exists()) {
                $this->reject('service_date', 'Scheduling requires an active train with at least two route stops and configured seats.');
            }

            return $train->trips()->create($data);
        });
    }

    public function initializeInventory(TrainTrip $trip): int
    {
        return DB::transaction(function () use ($trip) {
            // All catalogue/scheduling/inventory operations lock the train first.
            Train::whereKey($trip->train_id)->lockForUpdate()->firstOrFail();
            $trip = TrainTrip::whereKey($trip->id)->lockForUpdate()->firstOrFail();
            if ($trip->status !== 'scheduled') {
                $this->reject('inventory', 'Cancelled trips cannot be initialized.');
            }
            $created = 0;
            Seat::where('train_id', $trip->train_id)
                ->whereDoesntHave('inventories', fn ($query) => $query->where('trip_id', $trip->id))
                ->chunkById(100, function ($seats) use ($trip, &$created) {
                    $now = now();
                    $rows = $seats->map(fn (Seat $seat) => [
                        'trip_id' => $trip->id, 'train_id' => $trip->train_id,
                        'seat_id' => $seat->id, 'status' => 'available',
                        'created_at' => $now, 'updated_at' => $now,
                    ])->all();
                    DB::table('trip_seat_inventory')->insert($rows);
                    $created += count($rows);
                });

            app(BookingEngine::class)->promoteTrip($trip);

            return $created;
        }, (int) Config::get('smartseat.booking.transaction_attempts'));
    }

    private function editableTrain(Train $train): Train
    {
        $train = Train::whereKey($train->id)->lockForUpdate()->firstOrFail();
        if ($train->trips()->exists()) {
            $this->reject('catalogue', 'This train has scheduled trips. Its catalogue is frozen to preserve journey and booking records.');
        }

        return $train;
    }

    private function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
