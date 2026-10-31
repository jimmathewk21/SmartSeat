<?php

namespace Database\Factories;

use App\Models\Train;
use App\Models\TrainRouteStop;
use App\Models\TrainTrip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** Test fixtures only; never invoked by application seeding. */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return ['reference' => (string) Str::uuid(), 'user_id' => User::factory(),
            'trip_id' => function () {
                $train = Train::factory()->create();
                TrainRouteStop::factory()->create(['train_id' => $train->id]);
                TrainRouteStop::factory()->create(['train_id' => $train->id, 'stop_order' => 2, 'arrival_offset_minutes' => 600, 'departure_offset_minutes' => 605]);

                return TrainTrip::factory()->create(['train_id' => $train->id])->id;
            },
            'train_id' => fn (array $attributes) => TrainTrip::findOrFail($attributes['trip_id'])->train_id,
            'boarding_order' => 1, 'alighting_order' => 2, 'status' => 'pending', 'total_paise' => 0];
    }
}
