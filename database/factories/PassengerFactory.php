<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/** Test fixtures only; never invoked by application seeding. */
class PassengerFactory extends Factory
{
    public function definition(): array
    {
        return ['booking_id' => Booking::factory(),
            'trip_id' => fn (array $attributes) => Booking::findOrFail($attributes['booking_id'])->trip_id,
            'name' => 'Isolated test passenger', 'age' => 30, 'gender' => 'other', 'status' => 'pending'];
    }
}
