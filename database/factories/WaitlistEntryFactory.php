<?php

namespace Database\Factories;

use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class WaitlistEntryFactory extends Factory
{
    public function definition(): array
    {
        return ['passenger_id' => Passenger::factory(),
            'trip_id' => fn (array $attributes) => Passenger::findOrFail($attributes['passenger_id'])->trip_id,
            'travel_class' => 'SL', 'position' => 1, 'status' => 'waiting'];
    }
}
