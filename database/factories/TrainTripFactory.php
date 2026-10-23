<?php

namespace Database\Factories;

use App\Models\Train;
use App\Models\TrainTrip;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainTripFactory extends Factory
{
    protected $model = TrainTrip::class;

    public function definition(): array
    {
        $startsAt = now()->addDays(2)->setTime(8, 0);

        return [
            'train_id' => Train::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHours(4),
            'status' => TrainTrip::STATUS_SCHEDULED,
        ];
    }
}
