<?php

namespace Database\Factories;

use App\Models\Train;
use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class CoachFactory extends Factory
{
    public function definition(): array
    {
        return ['train_id' => Train::factory(), 'label' => 'S1', 'travel_class' => 'SL'];
    }
}
