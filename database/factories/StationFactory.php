<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class StationFactory extends Factory
{
    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->lexify('?????')), 'name' => fake()->city().' Station', 'city' => fake()->city()];
    }
}
