<?php

namespace Tests;

use App\Models\Coach;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainRouteStop;
use App\Models\TrainTrip;
use App\Services\RailwayCatalogue;

abstract class RailwayTestCase extends DatabaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        // Parent checks that ONLY SQLite :memory: exists before any DDL is run.
        $migration = require dirname(__DIR__).'/database/migrations/2026_10_30_000100_create_railway_foundation.php';
        $migration->up();
        (require dirname(__DIR__).'/database/migrations/2026_10_30_000200_add_booking_submission_and_audit.php')->up();
        (require dirname(__DIR__).'/database/migrations/2026_10_30_000300_add_intelligence_foundation.php')->up();

        return $app;
    }

    /** Explicit disposable fixtures, never an application dataset or seeder. */
    protected function journey(int $boardingOffset = 480): array
    {
        $train = Train::factory()->create();
        $origin = Station::factory()->create();
        $destination = Station::factory()->create();
        TrainRouteStop::factory()->create([
            'train_id' => $train->id, 'station_id' => $origin->id,
            'stop_order' => 1, 'arrival_offset_minutes' => $boardingOffset,
            'departure_offset_minutes' => $boardingOffset,
        ]);
        TrainRouteStop::factory()->create([
            'train_id' => $train->id, 'station_id' => $destination->id,
            'stop_order' => 2, 'arrival_offset_minutes' => $boardingOffset + 180,
            'departure_offset_minutes' => $boardingOffset + 185,
        ]);
        $coach = Coach::factory()->create(['train_id' => $train->id]);
        $seat = Seat::factory()->create(['coach_id' => $coach->id]);
        $trip = TrainTrip::factory()->create(['train_id' => $train->id]);
        app(RailwayCatalogue::class)->initializeInventory($trip);

        return compact('train', 'origin', 'destination', 'coach', 'seat', 'trip');
    }
}
