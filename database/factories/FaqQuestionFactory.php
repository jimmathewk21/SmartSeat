<?php

namespace Database\Factories;

use App\Models\FaqQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqQuestionFactory extends Factory
{
    protected $model = FaqQuestion::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'question' => fake()->sentence(12),
            'answer' => null,
            'status' => FaqQuestion::STATUS_PENDING,
            'answered_at' => null,
        ];
    }

    public function answered(): static
    {
        return $this->state(fn () => [
            'status' => FaqQuestion::STATUS_ANSWERED,
            'answer' => fake()->paragraph(),
            'answered_at' => now(),
        ]);
    }
}
