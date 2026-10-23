<?php

namespace Database\Factories;

use App\Models\Train;
use App\Models\TrainCoach;
use App\Models\TrainSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainSeatFactory extends Factory
{
    protected $model = TrainSeat::class;

    public function definition(): array
    {
        return [
            'train_id' => Train::factory(),
            'train_coach_id' => TrainCoach::factory(),
            'number' => (string) fake()->unique()->numberBetween(1, 72),
            'berth_type' => fake()->randomElement(['lower', 'middle', 'upper', 'side']),
            'is_accessible' => false,
        ];
    }
}
