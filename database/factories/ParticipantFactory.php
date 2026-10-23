<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => fake()->numerify('##########'),
            'is_approved' => false,
            'approved_at' => null,
        ];
    }

    public function forUser(User $user): self
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
