<?php

namespace Database\Factories;

use App\Models\Passenger;
use App\Models\TripSeatInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class SeatAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return ['passenger_id' => Passenger::factory(),
            'trip_id' => fn (array $attributes) => Passenger::findOrFail($attributes['passenger_id'])->trip_id,
            'inventory_id' => fn (array $attributes) => TripSeatInventory::factory()->create(['trip_id' => $attributes['trip_id']])->id,
            'status' => 'active'];
    }
}
