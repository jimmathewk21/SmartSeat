<?php

namespace Database\Factories;

use App\Models\Station;
use App\Models\Train;
use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class TrainRouteStopFactory extends Factory
{
    public function definition(): array
    {
        return ['train_id' => Train::factory(), 'station_id' => Station::factory(), 'stop_order' => 1, 'arrival_offset_minutes' => 480, 'departure_offset_minutes' => 485];
    }
}
