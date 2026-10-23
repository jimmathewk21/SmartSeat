<?php

namespace Tests\Feature\Train;

use App\Models\TrainBooking;
use App\Models\TrainBookingPassenger;
use App\Models\TrainBookingSeat;
use App\Models\TrainTrip;
use App\Models\User;
use Database\Seeders\TrainDemoSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrainDemoSeederSafetyTest extends TestCase
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

    public function test_booking_on_reserved_trip_aborts_before_trip_or_catalog_changes(): void
    {
        $this->runDemoSeeder();
        $trip = TrainTrip::query()->whereHas('train', fn ($query) => $query->where('code', 'SS-DEMO-01'))->firstOrFail();
        $trip->update(['status' => TrainTrip::STATUS_COMPLETED]);
        $station = DB::table('stations')->where('code', 'CSMT')->firstOrFail();
        DB::table('stations')->where('id', $station->id)->update(['name' => 'Preserve this station label']);
        $user = User::factory()->create();
        TrainBooking::query()->create([
            'booking_code' => 'DEMO-PREFLIGHT-BOOKING',
            'user_id' => $user->id,
            'train_trip_id' => $trip->id,
            'status' => TrainBooking::STATUS_CONFIRMED,
            'seat_count' => 1,
            'confirmed_at' => now(),
        ]);

        try {
            $this->runDemoSeeder();
            $this->fail('A reserved trip with booking history must abort the seeder.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('bookings', $exception->getMessage());
        }

        $this->assertDatabaseHas('train_trips', ['id' => $trip->id, 'status' => TrainTrip::STATUS_COMPLETED]);
        $this->assertDatabaseHas('stations', ['id' => $station->id, 'name' => 'Preserve this station label']);
    }

    public function test_existing_reserved_inventory_and_assignment_history_abort_mutation(): void
    {
        $this->runDemoSeeder();
        $trip = TrainTrip::query()->whereHas('train', fn ($query) => $query->where('code', 'SS-DEMO-01'))->firstOrFail();
        $user = User::factory()->create();
        $booking = TrainBooking::query()->create([
            'booking_code' => 'DEMO-PREFLIGHT-ASSIGNMENT',
            'user_id' => $user->id,
            'train_trip_id' => $trip->id,
            'status' => TrainBooking::STATUS_CONFIRMED,
            'seat_count' => 1,
            'confirmed_at' => now(),
        ]);
        $passenger = TrainBookingPassenger::query()->create([
            'train_booking_id' => $booking->id,
            'name' => 'Demo Passenger',
            'requires_accessible_seat' => false,
        ]);
        $inventory = $trip->inventories()->firstOrFail();
        $inventory->update(['status' => 'booked', 'current_booking_id' => $booking->id]);
        TrainBookingSeat::query()->create([
            'train_booking_id' => $booking->id,
            'train_trip_id' => $trip->id,
            'trip_seat_inventory_id' => $inventory->id,
            'train_booking_passenger_id' => $passenger->id,
        ]);

        try {
            $this->runDemoSeeder();
            $this->fail('Existing inventory and assignment history must abort the seeder.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('booking-seat assignments', $exception->getMessage());
            $this->assertStringContainsString('booked or inconsistent inventory rows', $exception->getMessage());
        }

        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $inventory->id,
            'status' => 'booked',
            'current_booking_id' => $booking->id,
        ]);
        $this->assertDatabaseHas('train_booking_seats', [
            'train_booking_id' => $booking->id,
            'trip_seat_inventory_id' => $inventory->id,
        ]);
    }

    public function test_clean_rerun_does_not_duplicate_reserved_demo_records(): void
    {
        $this->runDemoSeeder();
        $this->assertDatabaseHas('stations', [
            'code' => 'CSMT',
            'name' => 'Chhatrapati Shivaji Maharaj Terminus',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
        ]);
        $this->assertDatabaseHas('stations', ['code' => 'PUNE', 'city' => 'Pune', 'state' => 'Maharashtra']);
        $this->assertDatabaseHas('stations', ['code' => 'NK', 'city' => 'Nashik', 'state' => 'Maharashtra']);
        $this->assertDatabaseHas('stations', ['code' => 'NGP', 'city' => 'Nagpur', 'state' => 'Maharashtra']);
        $this->assertDatabaseHas('trains', ['code' => 'SS-DEMO-01', 'name' => 'SmartSeat Maharashtra Demo Express (fictional)']);
        $this->assertDatabaseHas('trains', ['code' => 'SS-DEMO-02', 'name' => 'SmartSeat Deccan Demo Mail (fictional)']);
        $tables = [
            'stations', 'trains', 'train_route_stops', 'train_coaches',
            'train_seats', 'train_trips', 'trip_seat_inventories',
        ];
        $countsBefore = $this->counts($tables);

        $this->runDemoSeeder();

        $this->assertSame($countsBefore, $this->counts($tables));
    }

    private function runDemoSeeder(): void
    {
        $seeder = new class extends TrainDemoSeeder
        {
            protected function assertDevelopmentDatabase(): void
            {
                // The test itself has already verified the dedicated connection and database name.
            }
        };

        $seeder->run();
    }

    private function counts(array $tables): array
    {
        return collect($tables)->mapWithKeys(
            fn (string $table): array => [$table => DB::table($table)->count()]
        )->all();
    }
}
