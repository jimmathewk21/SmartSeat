<?php

namespace Database\Factories;

use App\Models\Train;
use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class TrainTripFactory extends Factory
{
    public function definition(): array
    {
        return ['train_id' => Train::factory(), 'service_date' => now()->addDays(3)->toDateString(), 'status' => 'scheduled'];
    }
}
