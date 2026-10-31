<?php

namespace Tests;

use App\Models\Seat;
use App\Models\User;
use App\Services\BookingEngine;
use App\Services\RailwayCatalogue;
use Illuminate\Support\Str;

abstract class BookingTestCase extends RailwayTestCase
{
    protected function input(int $count = 1): array
    {
        return [
            'boarding_order' => 1, 'alighting_order' => 2, 'travel_class' => 'SL',
            'passengers' => array_map(fn ($i) => ['name' => 'Private Passenger '.$i, 'age' => 25 + $i, 'gender' => 'other'], range(1, $count)),
        ];
    }

    protected function addSeat(array $journey, int $number = 2): int
    {
        $seat = Seat::factory()->create(['coach_id' => $journey['coach']->id, 'number' => $number]);
        app(RailwayCatalogue::class)->initializeInventory($journey['trip']);

        return $journey['trip']->inventory()->where('seat_id', $seat->id)->value('id');
    }

    protected function reserve(array $journey, ?User $owner = null, ?array $seats = null, ?string $key = null)
    {
        $owner ??= User::factory()->create();
        $seats ??= [$journey['trip']->inventory()->orderBy('id')->value('id')];

        return app(BookingEngine::class)->book($owner, $journey['trip'], $this->input(count($seats)), 'reserve', $seats, $key ?? (string) Str::uuid());
    }

    protected function wait(array $journey, int $count = 1, ?User $owner = null)
    {
        return app(BookingEngine::class)->book($owner ?? User::factory()->create(), $journey['trip'], $this->input($count), 'waitlist', [], (string) Str::uuid());
    }
}
