<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainCoach;
use App\Models\TrainRouteStop;
use App\Models\TrainSeat;
use App\Models\TrainTrip;
use App\Services\IndianTrainDemoSeederPreflight;
use App\Services\TrainInventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Adds separate, clearly fictional SmartSeat services for Indian railway demonstrations. */
class IndianTrainDemoSeeder extends Seeder
{
    private const STATIONS = [
        ['code' => 'CSMT', 'name' => 'Chhatrapati Shivaji Maharaj Terminus', 'city' => 'Mumbai', 'state' => 'Maharashtra'],
        ['code' => 'PUNE', 'name' => 'Pune Junction', 'city' => 'Pune', 'state' => 'Maharashtra'],
        ['code' => 'NK', 'name' => 'Nashik Road', 'city' => 'Nashik', 'state' => 'Maharashtra'],
        ['code' => 'NGP', 'name' => 'Nagpur Junction', 'city' => 'Nagpur', 'state' => 'Maharashtra'],
    ];

    private const TRAINS = [
        [
            'code' => 'SS-IN-DEMO-01',
            'name' => 'SmartSeat Sahyadri Demo Express (fictional)',
            'route' => ['CSMT', 'PUNE', 'NK', 'NGP'],
        ],
        [
            'code' => 'SS-IN-DEMO-02',
            'name' => 'SmartSeat Deccan Demo Mail (fictional)',
            'route' => ['NGP', 'NK', 'PUNE', 'CSMT'],
        ],
    ];

    public function run(): void
    {
        $this->assertDevelopmentDatabase();

        DB::transaction(function (): void {
            // This preflight is scoped to the new service keys; the legacy seeder's
            // booking-history preflight remains unchanged and continues to block trip 18.
            app(IndianTrainDemoSeederPreflight::class)->assertSafeToUpdate();

            $stations = collect(self::STATIONS)->mapWithKeys(function (array $attributes): array {
                $station = Station::query()->where('code', $attributes['code'])->lockForUpdate()->first();

                if ($station === null) {
                    $station = Station::query()->create([...$attributes, 'is_active' => true]);
                } elseif (
                    $station->name !== $attributes['name']
                    || $station->city !== $attributes['city']
                    || $station->state !== $attributes['state']
                    || ! $station->is_active
                ) {
                    throw new RuntimeException(
                        "IndianTrainDemoSeeder found station code {$attributes['code']} already in use with different data; no existing station was changed."
                    );
                }

                return [$attributes['code'] => $station];
            });

            foreach (self::TRAINS as $definition) {
                $train = Train::query()->updateOrCreate(
                    ['code' => $definition['code']],
                    ['name' => $definition['name'], 'is_active' => true]
                );

                foreach ($definition['route'] as $index => $stationCode) {
                    $offset = $index * 80;
                    TrainRouteStop::query()->updateOrCreate(
                        ['train_id' => $train->id, 'stop_order' => $index + 1],
                        [
                            'station_id' => $stations[$stationCode]->id,
                            'arrival_offset_minutes' => $index === 0 ? null : $offset,
                            'departure_offset_minutes' => $index === 3 ? null : $offset,
                        ]
                    );
                }

                $coach = TrainCoach::query()->updateOrCreate(
                    ['train_id' => $train->id, 'code' => 'D1'],
                    ['class_code' => 'CC', 'sort_order' => 1]
                );

                foreach (range(1, 8) as $seatNumber) {
                    TrainSeat::query()->updateOrCreate(
                        ['train_coach_id' => $coach->id, 'number' => (string) $seatNumber],
                        [
                            'train_id' => $train->id,
                            'berth_type' => null,
                            'is_accessible' => $seatNumber === 1,
                        ]
                    );
                }

                $daysAhead = (int) config('train.demo_days_ahead', 7);
                $startsAt = now()->startOfDay()->addDays($daysAhead)->setTime(8, 0, 0);
                $trip = TrainTrip::query()->updateOrCreate(
                    ['train_id' => $train->id, 'starts_at' => $startsAt],
                    [
                        'ends_at' => $startsAt->copy()->addMinutes(240),
                        'status' => TrainTrip::STATUS_SCHEDULED,
                    ]
                );

                app(TrainInventoryService::class)->initializeForTrip($trip);
            }
        });
    }

    protected function assertDevelopmentDatabase(): void
    {
        $connectionName = DB::getDefaultConnection();
        $configuredDatabase = config("database.connections.{$connectionName}.database");

        if ($connectionName !== 'mysql' || $configuredDatabase !== 'smartseat_dev') {
            throw new RuntimeException(
                'IndianTrainDemoSeeder is restricted to the mysql connection configured for smartseat_dev.'
            );
        }

        if (DB::connection($connectionName)->getDatabaseName() !== 'smartseat_dev') {
            throw new RuntimeException(
                'IndianTrainDemoSeeder stopped because the connected database is not smartseat_dev.'
            );
        }
    }
}
