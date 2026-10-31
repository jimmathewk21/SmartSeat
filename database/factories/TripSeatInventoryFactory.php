<?php

namespace Database\Factories;

use App\Models\Coach;
use App\Models\Seat;
use App\Models\TrainTrip;
use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class TripSeatInventoryFactory extends Factory
{
    public function definition(): array
    {
        return ['trip_id' => TrainTrip::factory(),
            'train_id' => fn (array $attributes) => TrainTrip::findOrFail($attributes['trip_id'])->train_id,
            'seat_id' => fn (array $attributes) => Seat::factory()->create([
                'coach_id' => Coach::factory()->create(['train_id' => $attributes['train_id']])->id,
            ])->id, 'status' => 'available'];
    }
}
