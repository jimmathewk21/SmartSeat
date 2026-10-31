<?php

namespace Database\Factories;

use App\Models\Coach;
use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class SeatFactory extends Factory
{
    public function definition(): array
    {
        return ['coach_id' => Coach::factory(), 'train_id' => fn (array $attributes) => Coach::findOrFail($attributes['coach_id'])->train_id, 'number' => 1, 'berth_type' => 'lower'];
    }
}
