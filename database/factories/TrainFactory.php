<?php

namespace Database\Factories;

use App\Models\Train;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainFactory extends Factory
{
    protected $model = Train::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('TR####')),
            'name' => fake()->words(2, true) . ' Express',
            'is_active' => true,
        ];
    }
}
