<?php

namespace Database\Factories;

use App\Models\Train;
use App\Models\TrainCoach;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainCoachFactory extends Factory
{
    protected $model = TrainCoach::class;

    public function definition(): array
    {
        return [
            'train_id' => Train::factory(),
            'code' => strtoupper(fake()->unique()->bothify('C##')),
            'class_code' => 'SL',
            'sort_order' => 1,
        ];
    }
}
