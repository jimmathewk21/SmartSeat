<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Coach;
use App\Models\Passenger;
use App\Models\Seat;
use App\Models\SeatAssignment;
use App\Models\Station;
use App\Models\Train;
use App\Models\TrainRouteStop;
use App\Models\TrainTrip;
use App\Models\User;
use App\Support\RailwaySchema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\RailwayTestCase;

class RailwayManagementTest extends RailwayTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->administrator()->create(), 'web');
    }

    public function test_empty_admin_catalogues_render_and_do_not_create_data(): void
    {
        foreach (['stations', 'trains', 'trips'] as $resource) {
            $this->get(route('administrator.'.$resource.'.index'))->assertOk();
        }
        foreach (RailwaySchema::TABLES as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_station_creation_validation_duplicates_and_editing(): void
    {
        $this->post(route('administrator.stations.store'), ['code' => 'NDLS', 'name' => 'Test fixture station', 'city' => 'Test city'])
            ->assertRedirect(route('administrator.stations.index'));
        $this->assertDatabaseHas('stations', ['code' => 'NDLS']);
        $this->post(route('administrator.stations.store'), ['code' => 'NDLS', 'name' => 'Duplicate', 'city' => 'City'])->assertSessionHasErrors('code');
        $this->post(route('administrator.stations.store'), ['code' => 'bad1', 'name' => '', 'city' => ''])->assertSessionHasErrors(['code', 'name', 'city']);
        $station = Station::firstOrFail();
        $this->get(route('administrator.stations.edit', $station))->assertOk();
        $this->put(route('administrator.stations.update', $station), ['code' => 'NDLS', 'name' => 'Updated station', 'city' => 'Test city'])
            ->assertRedirect();
        $this->assertDatabaseHas('stations', ['id' => $station->id, 'name' => 'Updated station']);
    }

    public function test_train_numbers_and_validation_preserve_leading_zeroes(): void
    {
        $data = ['number' => '01234', 'name' => 'Explicit test train', 'is_active' => 1];
        $this->post(route('administrator.trains.store'), $data)->assertRedirect();
        $this->assertDatabaseHas('trains', ['number' => '01234']);
        $this->post(route('administrator.trains.store'), $data)->assertSessionHasErrors('number');
        foreach (['1234', '123456', '12A45', ' 1234'] as $number) {
            $this->post(route('administrator.trains.store'), array_replace($data, ['number' => $number]))->assertSessionHasErrors('number');
        }
    }

    public function test_route_ordering_validation_rolls_back_conflicting_stop(): void
    {
        $train = Train::factory()->create();
        $stations = Station::factory()->count(3)->create();
        $route = route('administrator.trains.stops.store', $train);
        $this->post($route, ['station_code' => $stations[0]->code, 'stop_order' => 1, 'arrival_offset_minutes' => 480, 'departure_offset_minutes' => 490])->assertRedirect();
        $this->post($route, ['station_code' => $stations[1]->code, 'stop_order' => 2, 'arrival_offset_minutes' => 1500, 'departure_offset_minutes' => 1510])->assertRedirect();
        $this->post($route, ['station_code' => $stations[2]->code, 'stop_order' => 3, 'arrival_offset_minutes' => 800, 'departure_offset_minutes' => 810])
            ->assertSessionHasErrors('arrival_offset_minutes');
        $this->assertDatabaseCount('train_route_stops', 2);
        $this->assertDatabaseMissing('train_route_stops', ['station_id' => $stations[2]->id]);
        $this->post($route, ['station_code' => $stations[0]->code, 'stop_order' => 3, 'arrival_offset_minutes' => 1600, 'departure_offset_minutes' => 1605])
            ->assertSessionHasErrors();
        $stop = $train->routeStops()->firstOrFail();
        $this->get(route('administrator.trains.stops.edit', [$train, $stop]))->assertOk();
        $this->put(route('administrator.stations.update', $stations[0]), ['code' => $stations[0]->code, 'name' => 'Rename', 'city' => 'City'])
            ->assertSessionHasErrors('code');
    }

    public function test_coach_and_seat_configuration_rejects_conflicts(): void
    {
        $train = Train::factory()->create();
        $coachRoute = route('administrator.trains.coaches.store', $train);
        $this->post($coachRoute, ['label' => 'S1', 'travel_class' => 'SL'])->assertRedirect();
        $this->post($coachRoute, ['label' => 'S1', 'travel_class' => 'SL'])->assertSessionHasErrors('label');
        $this->post($coachRoute, ['label' => 'S2', 'travel_class' => 'INVALID'])->assertSessionHasErrors('travel_class');
        $coach = $train->coaches()->firstOrFail();
        $seatRoute = route('administrator.trains.coaches.seats.store', [$train, $coach]);
        $this->post($seatRoute, ['number' => 1, 'berth_type' => 'lower'])->assertRedirect();
        $this->post($seatRoute, ['number' => 1, 'berth_type' => 'upper'])->assertSessionHasErrors('number');
        $this->post($seatRoute, ['number' => 2, 'berth_type' => 'invalid'])->assertSessionHasErrors('berth_type');
        $seat = $coach->seats()->firstOrFail();
        $this->get(route('administrator.trains.coaches.edit', [$train, $coach]))->assertOk();
        $this->get(route('administrator.trains.coaches.seats.edit', [$train, $coach, $seat]))->assertOk();
        $this->put(route('administrator.trains.coaches.seats.update', [$train, $coach, $seat]), ['number' => 2, 'berth_type' => 'upper'])->assertRedirect();
        $this->assertDatabaseHas('seats', ['id' => $seat->id, 'number' => 2, 'berth_type' => 'upper']);
    }

    public function test_trip_scheduling_requires_complete_configuration_and_unique_dates(): void
    {
        $train = Train::factory()->create();
        $date = now()->addDays(3)->toDateString();
        $route = route('administrator.trains.trips.store', $train);
        $this->post($route, ['service_date' => $date])->assertSessionHasErrors('service_date');
        TrainRouteStop::factory()->create(['train_id' => $train->id]);
        TrainRouteStop::factory()->create(['train_id' => $train->id, 'stop_order' => 2, 'arrival_offset_minutes' => 600, 'departure_offset_minutes' => 605]);
        Seat::factory()->create(['coach_id' => Coach::factory()->create(['train_id' => $train->id])->id]);
        $this->post($route, ['service_date' => $date])->assertRedirect();
        $this->assertDatabaseHas('train_trips', ['train_id' => $train->id, 'service_date' => $date, 'status' => 'scheduled']);
        $this->assertDatabaseCount('trip_seat_inventory', 0);
        $this->post($route, ['service_date' => $date])->assertSessionHasErrors('service_date');
        $this->post($route, ['service_date' => now()->subDay()->toDateString()])->assertSessionHasErrors('service_date');
        $this->get(route('administrator.trains.trips.index', $train))->assertOk()->assertSee('Schedule a trip');
    }

    public function test_inventory_initialization_is_idempotent_and_preserves_state(): void
    {
        extract($this->journey());
        $inventory = $trip->inventory()->firstOrFail();
        $inventory->update(['status' => 'blocked']);
        $originalUpdatedAt = $inventory->updated_at->toDateTimeString();
        $this->travel(1)->minutes();
        $this->post(route('administrator.inventory.store', $trip))->assertRedirect();
        $this->post(route('administrator.inventory.store', $trip))->assertRedirect();
        $this->assertDatabaseCount('trip_seat_inventory', 1);
        $this->assertDatabaseHas('trip_seat_inventory', ['id' => $inventory->id, 'status' => 'blocked', 'updated_at' => $originalUpdatedAt]);
        $this->get(route('administrator.inventory.index', $trip))->assertOk()->assertSee('Blocked');
    }

    public function test_scheduled_catalogue_is_immutable_and_scoped_bindings_reject_other_trains(): void
    {
        extract($this->journey());
        $this->put(route('administrator.trains.update', $train), ['number' => $train->number, 'name' => 'Changed', 'is_active' => 1])->assertSessionHasErrors('catalogue');
        $this->post(route('administrator.trains.coaches.seats.store', [$train, $coach]), ['number' => 2, 'berth_type' => 'upper'])->assertSessionHasErrors('catalogue');
        $this->assertDatabaseMissing('trains', ['id' => $train->id, 'name' => 'Changed']);
        $other = Train::factory()->create();
        $this->get(route('administrator.trains.coaches.seats.index', [$other, $coach]))->assertNotFound();
        $this->put(route('administrator.trains.coaches.seats.update', [$other, $coach, $seat]), ['number' => 3, 'berth_type' => 'upper'])->assertNotFound();
    }

    public function test_cancelled_trip_inventory_initialization_is_rejected(): void
    {
        extract($this->journey());
        $trip->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        $this->post(route('administrator.inventory.store', $trip))->assertSessionHasErrors('inventory');
    }

    public function test_every_nested_management_page_renders_with_empty_configuration(): void
    {
        $train = Train::factory()->create();
        $coach = Coach::factory()->create(['train_id' => $train->id]);
        $trip = TrainTrip::factory()->create(['train_id' => $train->id]);
        foreach ([
            ['administrator.trains.edit', [$train], 'Save changes'],
            ['administrator.trains.stops.index', [$train], 'No route stops configured yet'],
            ['administrator.trains.coaches.index', [$train], 'Coaches & seats'],
            ['administrator.trains.coaches.seats.index', [$train, $coach], 'No seats configured yet'],
            ['administrator.trains.trips.index', [$train], 'Schedule a trip'],
            ['administrator.inventory.index', [$trip], 'No inventory initialized yet'],
        ] as [$route, $arguments, $text]) {
            $this->get(route($route, $arguments))->assertOk()->assertSeeText($text, false);
        }
    }

    public function test_initialization_populates_explicit_seats_and_never_resets_assignments(): void
    {
        extract($this->journey());
        $secondTrip = TrainTrip::factory()->create(['train_id' => $train->id, 'service_date' => $trip->service_date->addDay()]);
        $this->post(route('administrator.inventory.store', $secondTrip))->assertRedirect();
        $this->assertDatabaseHas('trip_seat_inventory', ['trip_id' => $secondTrip->id, 'seat_id' => $seat->id, 'train_id' => $train->id]);
        $passenger = Passenger::factory()->create([
            'booking_id' => Booking::factory()->create(['trip_id' => $trip->id])->id,
        ]);
        $assignment = SeatAssignment::factory()->create([
            'passenger_id' => $passenger->id, 'inventory_id' => $trip->inventory()->value('id'),
        ]);
        $this->post(route('administrator.inventory.store', $trip))->assertRedirect();
        $this->assertDatabaseHas('seat_assignments', ['id' => $assignment->id, 'status' => 'active']);
        $this->assertDatabaseCount('trip_seat_inventory', 2);
    }

    #[DataProvider('administratorRoutes')]
    public function test_passengers_cannot_read_or_modify_administrator_catalogue(string $method, string $name, array $keys): void
    {
        $fixture = $this->journey();
        $fixture['station'] = $fixture['origin'];
        $fixture['routeStop'] = $fixture['train']->routeStops()->firstOrFail();
        $this->actingAs(User::factory()->create(), 'web');
        $arguments = array_map(fn (string $key) => $fixture[$key], $keys);
        $this->call($method, route($name, $arguments))->assertForbidden();
        $this->assertDatabaseCount('stations', 2);
        $this->assertDatabaseCount('trains', 1);
    }

    public static function administratorRoutes(): array
    {
        $routes = [];
        foreach ([
            ['stations', []], ['trains', []], ['trains.stops', ['train']],
            ['trains.coaches', ['train']], ['trains.coaches.seats', ['train', 'coach']],
        ] as [$resource, $keys]) {
            $routes[] = ['GET', 'administrator.'.$resource.'.index', $keys];
            $routes[] = ['POST', 'administrator.'.$resource.'.store', $keys];
        }
        $routes[] = ['POST', 'administrator.trains.trips.store', ['train']];
        $routes[] = ['POST', 'administrator.inventory.store', ['trip']];
        $routes[] = ['GET', 'administrator.inventory.index', ['trip']];
        $routes[] = ['GET', 'administrator.trips.index', []];
        $routes[] = ['GET', 'administrator.trains.trips.index', ['train']];
        foreach ([
            ['stations', ['station']], ['trains', ['train']], ['trains.stops', ['train', 'routeStop']],
            ['trains.coaches', ['train', 'coach']], ['trains.coaches.seats', ['train', 'coach', 'seat']],
        ] as [$resource, $keys]) {
            $routes[] = ['PUT', 'administrator.'.$resource.'.update', $keys];
            $routes[] = ['GET', 'administrator.'.$resource.'.edit', $keys];
        }

        return $routes;
    }

    public function test_guests_are_redirected_and_station_lists_are_paginated(): void
    {
        auth('web')->logout();
        $this->get(route('administrator.stations.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->administrator()->create(), 'web');
        Station::factory()->count(21)->create();
        $this->get(route('administrator.stations.index'))->assertOk()->assertSee('Page 1 of 2');
    }
}
