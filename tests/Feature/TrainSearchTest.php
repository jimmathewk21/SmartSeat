<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\SeatAssignment;
use App\Models\TrainTrip;
use Tests\RailwayTestCase;

class TrainSearchTest extends RailwayTestCase
{
    private function criteria(array $journey, ?string $date = null): array
    {
        return [
            'origin' => $journey['origin']->code, 'destination' => $journey['destination']->code,
            'journey_date' => $date ?? $journey['trip']->service_date->toDateString(),
        ];
    }

    public function test_empty_database_search_renders_without_creating_records(): void
    {
        $this->get(route('trains.search'))->assertOk()->assertSee('Your next chapter awaits.');
        $this->get(route('trains.search', ['origin' => 'NONE', 'destination' => 'EMPTY', 'journey_date' => now()->addDay()->toDateString()]))
            ->assertSessionHasErrors(['origin', 'destination']);
        $this->assertDatabaseCount('stations', 0);
        $this->assertDatabaseCount('trains', 0);
    }

    public function test_search_returns_correct_route_with_coach_and_seat_availability(): void
    {
        $journey = $this->journey();
        $this->get(route('trains.search', $this->criteria($journey)))->assertOk()
            ->assertSee($journey['train']->number)->assertSee($journey['train']->name)
            ->assertSee('S1')->assertSee('1 of 1 seats available')->assertDontSee('Initialize missing inventory');
    }

    public function test_reverse_route_and_wrong_dates_do_not_match(): void
    {
        $journey = $this->journey();
        $criteria = $this->criteria($journey);
        $this->get(route('trains.search', array_replace($criteria, ['origin' => $criteria['destination'], 'destination' => $criteria['origin']])))
            ->assertOk()->assertSee('No matching journeys')->assertDontSee($journey['train']->number);
        $this->get(route('trains.search', $this->criteria($journey, $journey['trip']->service_date->addDay()->toDateString())))
            ->assertOk()->assertSee('No matching journeys');
    }

    public function test_boarding_date_includes_trips_originating_the_previous_day(): void
    {
        $journey = $this->journey(1500);
        $date = $journey['trip']->service_date->addDay()->toDateString();
        $this->get(route('trains.search', $this->criteria($journey, $date)))->assertOk()->assertSee($journey['train']->number)
            ->assertSee($journey['trip']->service_date->addDay()->format('d-M-Y').' 01:00 AM');
        $this->get(route('trains.search', $this->criteria($journey)))->assertOk()->assertSee('No matching journeys');
    }

    public function test_assigned_and_blocked_seats_are_not_available(): void
    {
        $journey = $this->journey();
        $booking = Booking::factory()->create(['trip_id' => $journey['trip']->id]);
        $passenger = Passenger::factory()->create(['booking_id' => $booking->id]);
        $assignment = SeatAssignment::factory()->create([
            'passenger_id' => $passenger->id, 'inventory_id' => $journey['trip']->inventory()->value('id'),
        ]);
        $this->get(route('trains.search', $this->criteria($journey)))->assertOk()->assertSee('0 of 1 seats available');
        $assignment->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        $this->get(route('trains.search', $this->criteria($journey)))->assertOk()->assertSee('1 of 1 seats available');
        $journey['trip']->inventory()->update(['status' => 'blocked']);
        $this->get(route('trains.search', $this->criteria($journey)))->assertOk()->assertSee('0 of 1 seats available');
    }

    public function test_inactive_trains_and_cancelled_trips_are_hidden(): void
    {
        $journey = $this->journey();
        $journey['train']->update(['is_active' => false]);
        $this->get(route('trains.search', $this->criteria($journey)))->assertOk()->assertSee('No matching journeys');
        $journey['train']->update(['is_active' => true]);
        $journey['trip']->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        $this->get(route('trains.search', $this->criteria($journey)))->assertOk()->assertSee('No matching journeys');
    }

    public function test_search_validates_dates_and_distinct_stations(): void
    {
        $journey = $this->journey();
        $this->get(route('trains.search', array_replace($this->criteria($journey), [
            'destination' => $journey['origin']->code, 'journey_date' => 'not-a-date',
        ])))->assertSessionHasErrors(['destination', 'journey_date']);
        $this->get(route('trains.search', ['origin' => ['bad']]))->assertSessionHasErrors(['origin', 'destination', 'journey_date']);
    }

    public function test_uninitialized_inventory_is_not_reported_as_available_seats(): void
    {
        $journey = $this->journey();
        $secondTrip = TrainTrip::factory()->create([
            'train_id' => $journey['train']->id, 'service_date' => $journey['trip']->service_date->addDay(),
        ]);
        $this->get(route('trains.search', $this->criteria($journey, $secondTrip->service_date->toDateString())))
            ->assertOk()->assertSee('Seat inventory has not been initialized')->assertDontSee('1 of 1 seats available');
    }
}
