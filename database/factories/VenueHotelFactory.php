<?php

namespace Database\Factories;

use App\Models\VenueHotel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VenueHotelFactory extends Factory
{
    protected $model = VenueHotel::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Hotel',
            'city' => fake()->city(),
            'country' => fake()->country(),
            'stars' => fake()->numberBetween(3, 5),
            'hotel_image' => 'images/misc/1.webp',
            'hotel_gallery' => ['images/misc/1.webp'],
            'hotel_address' => fake()->address(),
            'hotel_description' => fake()->paragraph(),
            'hotel_rooms' => [[
                'name' => 'Deluxe Room',
                'size' => '41 sqm / 440 sqf',
                'image' => 'images/misc/1.webp',
                'benefits' => ['Complimentary breakfast', 'Complimentary Wi-Fi'],
                'rate_inr' => 13000,
                'rate_usd' => 178,
                'badge' => '',
            ]],
            'booking_available_from' => now()->toDateString(),
            'booking_available_to' => now()->addMonth()->toDateString(),
            'direction_from' => 'Airport',
            'direction_to' => 'Hotel',
            'is_active' => true,
            'sort_order' => 0,
            'source_world_event_id' => null,
        ];
    }
}
