<?php

namespace Tests\Feature\Train;

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainBooking;
use App\Models\TrainBookingPassenger;
use App\Models\TrainBookingSeat;
use App\Models\TrainCoach;
use App\Models\TrainRouteStop;
use App\Models\TrainSeat;
use App\Models\TrainTrip;
use App\Models\TripSeatInventory;
use App\Models\User;
use App\Services\TrainSearchService;
use Database\Seeders\IndianTrainDemoSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IndianTrainDemoSeederTest extends TestCase
{
    private bool $testTransactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDefaultConnection() !== 'mysql'
            || config('database.connections.mysql.database') !== 'smartseat_train_test') {
            $this->markTestSkipped('Requires the separately prepared smartseat_train_test database.');
        }

        if (DB::connection('mysql')->getDatabaseName() !== 'smartseat_train_test') {
            $this->markTestSkipped('The active MySQL database is not smartseat_train_test.');
        }

        DB::connection('mysql')->beginTransaction();
        $this->testTransactionStarted = true;
    }

    protected function tearDown(): void
    {
        if ($this->testTransactionStarted && DB::connection('mysql')->transactionLevel() > 0) {
            DB::connection('mysql')->rollBack();
        }

        parent::tearDown();
    }

    public function test_existing_booking_and_route_references_remain_unchanged(): void
    {
        $legacy = $this->makeBookedLegacyTrain();
        $tablesAndIds = [
            'stations' => [$legacy['origin']->id, $legacy['destination']->id],
            'trains' => [$legacy['train']->id],
            'train_route_stops' => [$legacy['originStop']->id, $legacy['destinationStop']->id],
            'train_coaches' => [$legacy['coach']->id],
            'train_seats' => [$legacy['seat']->id],
            'train_trips' => [$legacy['trip']->id],
            'trip_seat_inventories' => [$legacy['inventory']->id],
            'train_bookings' => [$legacy['booking']->id],
            'train_booking_passengers' => [$legacy['passenger']->id],
            'train_booking_seats' => [$legacy['assignment']->id],
        ];
        $before = $this->snapshot($tablesAndIds);

        $this->runIndianSeeder();

        $this->assertSame($before, $this->snapshot($tablesAndIds));
    }

    public function test_indian_demo_records_are_searchable_and_legacy_catalog_codes_are_filtered(): void
    {
        $this->runIndianSeeder();

        $visibleStations = app(TrainSearchService::class)->stations();
        $visibleStationCodes = $visibleStations->pluck('code')->all();
        $this->assertContains('CSMT', $visibleStationCodes);
        $this->assertContains('PUNE', $visibleStationCodes);
        $this->assertNotContains('SSDEMO01', $visibleStationCodes);
        $this->assertSame(
            ['SS-DEMO-01', 'SS-DEMO-02'],
            config('train.legacy_search_excluded_train_codes')
        );

        $origin = Station::query()->where('code', 'CSMT')->firstOrFail();
        $destination = Station::query()->where('code', 'PUNE')->firstOrFail();
        $date = now()->startOfDay()->addDays((int) config('train.demo_days_ahead', 7))->toDateString();

        // Give the service another active route for the same stations to prove its configured
        // train-code exclusion is applied by the search query itself.
        config()->set('train.legacy_search_excluded_train_codes', [
            ...config('train.legacy_search_excluded_train_codes', []),
            'TEST-LEGACY',
        ]);
        $legacyTrain = Train::query()->create([
            'code' => 'TEST-LEGACY',
            'name' => 'Legacy Fictional Express',
            'is_active' => true,
        ]);
        TrainRouteStop::query()->create([
            'train_id' => $legacyTrain->id,
            'station_id' => $origin->id,
            'stop_order' => 1,
            'departure_offset_minutes' => 0,
        ]);
        TrainRouteStop::query()->create([
            'train_id' => $legacyTrain->id,
            'station_id' => $destination->id,
            'stop_order' => 2,
            'arrival_offset_minutes' => 80,
        ]);
        TrainTrip::query()->create([
            'train_id' => $legacyTrain->id,
            'starts_at' => now()->startOfDay()->addDays((int) config('train.demo_days_ahead', 7))->setTime(8, 0),
            'ends_at' => now()->startOfDay()->addDays((int) config('train.demo_days_ahead', 7))->setTime(9, 20),
            'status' => TrainTrip::STATUS_SCHEDULED,
        ]);

        $results = app(TrainSearchService::class)->search($origin->id, $destination->id, $date);
        $resultTrainCodes = $results->map(fn (TrainTrip $trip) => $trip->train->code)->all();

        $this->assertContains('SS-IN-DEMO-01', $resultTrainCodes);
        $this->assertNotContains('TEST-LEGACY', $resultTrainCodes);
    }

    public function test_rerunning_the_isolated_seeder_does_not_duplicate_demo_records(): void
    {
        $this->runIndianSeeder();
        $before = $this->indianDemoCounts();

        $this->runIndianSeeder();

        $this->assertSame($before, $this->indianDemoCounts());
        $this->assertDatabaseHas('trains', [
            'code' => 'SS-IN-DEMO-01',
            'name' => 'SmartSeat Sahyadri Demo Express (fictional)',
        ]);
        $this->assertDatabaseHas('trains', [
            'code' => 'SS-IN-DEMO-02',
            'name' => 'SmartSeat Deccan Demo Mail (fictional)',
        ]);
    }

    private function makeBookedLegacyTrain(): array
    {
        $origin = Station::query()->create([
            'code' => 'LEGACY01', 'name' => 'Legacy Riverton Central', 'city' => 'Riverton', 'state' => 'Demo State', 'is_active' => true,
        ]);
        $destination = Station::query()->create([
            'code' => 'LEGACY02', 'name' => 'Legacy Lakeview Junction', 'city' => 'Lakeview', 'state' => 'Demo State', 'is_active' => true,
        ]);
        $train = Train::query()->create(['code' => 'LEGACY-KEEP', 'name' => 'Legacy Fictional Express', 'is_active' => true]);
        $originStop = TrainRouteStop::query()->create([
            'train_id' => $train->id, 'station_id' => $origin->id, 'stop_order' => 1, 'departure_offset_minutes' => 0,
        ]);
        $destinationStop = TrainRouteStop::query()->create([
            'train_id' => $train->id, 'station_id' => $destination->id, 'stop_order' => 2, 'arrival_offset_minutes' => 80,
        ]);
        $coach = TrainCoach::query()->create(['train_id' => $train->id, 'code' => 'L1', 'class_code' => 'CC', 'sort_order' => 1]);
        $seat = TrainSeat::query()->create([
            'train_id' => $train->id, 'train_coach_id' => $coach->id, 'number' => '1', 'is_accessible' => false,
        ]);
        $trip = TrainTrip::query()->create([
            'train_id' => $train->id,
            'starts_at' => now()->addDays(20)->setTime(8, 0),
            'ends_at' => now()->addDays(20)->setTime(9, 20),
            'status' => TrainTrip::STATUS_SCHEDULED,
        ]);
        $user = User::factory()->create();
        $booking = TrainBooking::query()->create([
            'booking_code' => 'LEGACY-PRESERVE-' . bin2hex(random_bytes(4)),
            'user_id' => $user->id,
            'train_trip_id' => $trip->id,
            'status' => TrainBooking::STATUS_CONFIRMED,
            'seat_count' => 1,
            'confirmed_at' => now(),
        ]);
        $passenger = TrainBookingPassenger::query()->create([
            'train_booking_id' => $booking->id,
            'name' => 'Preserved Passenger',
            'requires_accessible_seat' => false,
        ]);
        $inventory = TripSeatInventory::query()->create([
            'train_trip_id' => $trip->id,
            'train_id' => $train->id,
            'train_seat_id' => $seat->id,
            'status' => TripSeatInventory::STATUS_BOOKED,
            'current_booking_id' => $booking->id,
        ]);
        $assignment = TrainBookingSeat::query()->create([
            'train_booking_id' => $booking->id,
            'train_trip_id' => $trip->id,
            'trip_seat_inventory_id' => $inventory->id,
            'train_booking_passenger_id' => $passenger->id,
        ]);

        return compact('origin', 'destination', 'train', 'originStop', 'destinationStop', 'coach', 'seat', 'trip', 'booking', 'passenger', 'inventory', 'assignment');
    }

    private function snapshot(array $tablesAndIds): array
    {
        $snapshot = [];

        foreach ($tablesAndIds as $table => $ids) {
            $snapshot[$table] = DB::table($table)->whereIn('id', $ids)->orderBy('id')->get()
                ->map(fn ($row) => (array) $row)->all();
        }

        return $snapshot;
    }

    private function indianDemoCounts(): array
    {
        $trainIds = Train::query()->whereIn('code', ['SS-IN-DEMO-01', 'SS-IN-DEMO-02'])->pluck('id');
        $tripIds = TrainTrip::query()->whereIn('train_id', $trainIds)->pluck('id');

        return [
            'stations' => Station::query()->whereIn('code', ['CSMT', 'PUNE', 'NK', 'NGP'])->count(),
            'trains' => $trainIds->count(),
            'route_stops' => TrainRouteStop::query()->whereIn('train_id', $trainIds)->count(),
            'coaches' => TrainCoach::query()->whereIn('train_id', $trainIds)->count(),
            'seats' => TrainSeat::query()->whereIn('train_id', $trainIds)->count(),
            'trips' => $tripIds->count(),
            'inventory' => TripSeatInventory::query()->whereIn('train_trip_id', $tripIds)->count(),
        ];
    }

    private function runIndianSeeder(): void
    {
        $seeder = new class extends IndianTrainDemoSeeder
        {
            protected function assertDevelopmentDatabase(): void
            {
                // The test itself has verified the dedicated smartseat_train_test connection.
            }
        };

        $seeder->run();
    }
}
