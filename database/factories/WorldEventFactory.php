<?php

namespace Database\Factories;

use App\Models\WorldEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorldEventFactory extends Factory
{
    protected $model = WorldEvent::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 month', '+12 months');
        $endDate = (clone $startDate)->modify('+2 days');

        return [
            'name' => fake()->unique()->sentence(3),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'days' => null,
            'city' => fake()->city(),
            'country' => fake()->country(),
            'hotel' => fake()->company(),
            'theme' => fake()->sentence(3),
            'website' => fake()->url(),
            'stars' => fake()->numberBetween(0, 5),
            'is_active' => true,
            'is_hotel' => false,
            'hotel_image' => null,
            'hotel_gallery' => [],
            'hotel_address' => null,
            'hotel_description' => null,
            'hotel_rooms' => [],
            'booking_available_from' => null,
            'booking_available_to' => null,
            'direction_from' => null,
            'direction_to' => null,
        ];
    }
}
