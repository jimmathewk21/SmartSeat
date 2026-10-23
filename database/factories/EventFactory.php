<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . strtolower(Str::random(5)),
            'venue' => fake()->city(),
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(6),
            'description' => fake()->paragraph(),
            'ticket_hero_label' => 'Ticket Booking',
            'ticket_hero_suffix' => '/ reserve your seat',
            'ticket_hero_description' => 'Choose the best registration package for this event, review the included benefits, and continue to a secure checkout experience.',
            'is_published' => true,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
