<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class TrainFactory extends Factory
{
    public function definition(): array
    {
        return ['number' => fake()->unique()->numerify('#####'), 'name' => 'Isolated test train', 'is_active' => true];
    }
}
