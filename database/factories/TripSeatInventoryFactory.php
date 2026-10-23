<?php

namespace Database\Factories;

use App\Models\TrainSeat;
use App\Models\TrainTrip;
use App\Models\TripSeatInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripSeatInventoryFactory extends Factory
{
    protected $model = TripSeatInventory::class;

    public function definition(): array
    {
        return [
            'train_trip_id' => TrainTrip::factory(),
            'train_id' => fn (array $attributes) => TrainTrip::query()->find($attributes['train_trip_id'])->train_id,
            'train_seat_id' => TrainSeat::factory(),
            'status' => TripSeatInventory::STATUS_AVAILABLE,
        ];
    }
}
