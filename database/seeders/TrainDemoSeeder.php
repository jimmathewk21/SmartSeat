<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainCoach;
use App\Models\TrainRouteStop;
use App\Models\TrainSeat;
use App\Models\TrainTrip;
use App\Services\TrainInventoryService;
use App\Services\TrainDemoSeederPreflight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TrainDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->assertDevelopmentDatabase();

        DB::transaction(function (): void {
            // This locked read-only preflight must finish before the first updateOrCreate or inventory insert.
            app(TrainDemoSeederPreflight::class)->assertSafeToUpdate();

            $stations = collect([
                ['code' => 'CSMT', 'name' => 'Chhatrapati Shivaji Maharaj Terminus', 'city' => 'Mumbai', 'state' => 'Maharashtra'],
                ['code' => 'PUNE', 'name' => 'Pune Junction', 'city' => 'Pune', 'state' => 'Maharashtra'],
                ['code' => 'NK', 'name' => 'Nashik Road', 'city' => 'Nashik', 'state' => 'Maharashtra'],
                ['code' => 'NGP', 'name' => 'Nagpur Junction', 'city' => 'Nagpur', 'state' => 'Maharashtra'],
            ])->mapWithKeys(function (array $attributes): array {
                $code = $attributes['code'];
                $station = Station::updateOrCreate(
                    ['code' => $code],
                    [...$attributes, 'is_active' => true]
                );

                return [$code => $station];
            });

            $trainDefinitions = [
                [
                    'code' => 'SS-DEMO-01',
                    'name' => 'SmartSeat Maharashtra Demo Express (fictional)',
                    'route' => ['CSMT', 'PUNE', 'NK', 'NGP'],
                ],
                [
                    'code' => 'SS-DEMO-02',
                    'name' => 'SmartSeat Deccan Demo Mail (fictional)',
                    'route' => ['NGP', 'NK', 'PUNE', 'CSMT'],
                ],
            ];

            foreach ($trainDefinitions as $definition) {
                $train = Train::updateOrCreate(
                    ['code' => $definition['code']],
                    ['name' => $definition['name'], 'is_active' => true]
                );

                foreach ($definition['route'] as $index => $stationCode) {
                    $offset = $index * 80;
                    TrainRouteStop::updateOrCreate(
                        ['train_id' => $train->id, 'stop_order' => $index + 1],
                        [
                            'station_id' => $stations[$stationCode]->id,
                            'arrival_offset_minutes' => $index === 0 ? null : $offset,
                            'departure_offset_minutes' => $index === 3 ? null : $offset,
                        ]
                    );
                }

                $coach = TrainCoach::updateOrCreate(
                    ['train_id' => $train->id, 'code' => 'D1'],
                    ['class_code' => 'CC', 'sort_order' => 1]
                );

                foreach (range(1, 8) as $seatNumber) {
                    TrainSeat::updateOrCreate(
                        ['train_coach_id' => $coach->id, 'number' => (string) $seatNumber],
                        [
                            'train_id' => $train->id,
                            'berth_type' => null,
                            'is_accessible' => $seatNumber === 1,
                        ]
                    );
                }

                // The configured offset keeps the trip searchable soon after seeding.
                $daysAhead = (int) config('train.demo_days_ahead', 7);
                $startsAt = now()->startOfDay()->addDays($daysAhead)->setTime(8, 0, 0);
                $trip = TrainTrip::updateOrCreate(
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
                'TrainDemoSeeder is restricted to the mysql connection configured for smartseat_dev.'
            );
        }

        $actualDatabase = DB::connection($connectionName)->getDatabaseName();

        if ($actualDatabase !== 'smartseat_dev') {
            throw new RuntimeException(
                'TrainDemoSeeder stopped because the connected database is not smartseat_dev.'
            );
        }
    }
}
