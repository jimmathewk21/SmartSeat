<?php

namespace Tests\Feature\Train;

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainCoach;
use App\Models\TrainRouteStop;
use App\Models\TrainSeat;
use App\Models\TrainTrip;
use App\Models\TripSeatInventory;
use App\Models\User;
use App\Services\TrainBookingService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class TrainBookingConcurrencyTest extends TestCase
{
    private array $fixtures = [];
    private array $workers = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('testing', app()->environment());
        $this->assertSame('testing', getenv('APP_ENV'));
        $this->assertSame('mysql', getenv('DB_CONNECTION'));
        $this->assertSame('smartseat_train_test', getenv('DB_DATABASE'));
        $this->assertSame('mysql', config('database.default'));
        $this->assertSame('smartseat_train_test', config('database.connections.mysql.database'));
        $this->assertSame('smartseat_train_test', DB::selectOne('SELECT DATABASE() AS db')->db);
        foreach (['train_trips', 'train_bookings', 'train_booking_seats', 'train_waitlist_entries', 'trip_seat_inventories'] as $table) {
            $engine = DB::table('information_schema.tables')->whereRaw('table_schema = DATABASE()')
                ->where('table_name', $table)->value('engine');
            $this->assertSame('INNODB', strtoupper((string) $engine));
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->workers as $worker) {
            if ($worker->isRunning()) {
                $worker->stop(1);
            }
        }
        foreach (array_reverse($this->fixtures) as $fixture) {
            $this->assertSame('smartseat_train_test', DB::selectOne('SELECT DATABASE() AS db')->db);
            DB::transaction(function () use ($fixture): void {
                $tripId = $fixture['trip_id'];
                $bookingIds = DB::table('train_bookings')->where('train_trip_id', $tripId)->pluck('id');
                $entryIds = DB::table('train_waitlist_entries')->where('train_trip_id', $tripId)->pluck('id');
                DB::table('train_waitlist_passengers')->whereIn('train_waitlist_entry_id', $entryIds)->delete();
                DB::table('train_waitlist_entries')->whereIn('id', $entryIds)->delete();
                DB::table('trip_seat_inventories')->where('train_trip_id', $tripId)
                    ->update(['status' => 'available', 'current_booking_id' => null]);
                DB::table('train_booking_seats')->where('train_trip_id', $tripId)->delete();
                DB::table('train_booking_passengers')->whereIn('train_booking_id', $bookingIds)->delete();
                DB::table('train_bookings')->whereIn('id', $bookingIds)->delete();
                DB::table('trip_seat_inventories')->where('train_trip_id', $tripId)->delete();
                DB::table('train_trips')->where('id', $tripId)->delete();
                DB::table('train_seats')->where('train_id', $fixture['train_id'])->delete();
                DB::table('train_coaches')->where('train_id', $fixture['train_id'])->delete();
                DB::table('train_route_stops')->where('train_id', $fixture['train_id'])->delete();
                DB::table('trains')->where('id', $fixture['train_id'])->delete();
                DB::table('stations')->whereIn('id', [$fixture['from_id'], $fixture['to_id']])->delete();
                DB::table('users')->whereIn('id', $fixture['user_ids'])->delete();
            });
        }
        parent::tearDown();
    }

    public function test_two_processes_cannot_book_one_seat_twice(): void
    {
        $fixture = $this->makeFixture();
        $results = $this->raceForTrip($fixture, [
            ['book', $this->bookPayload($fixture, $fixture['user_ids'][0])],
            ['book', $this->bookPayload($fixture, $fixture['user_ids'][1])],
        ]);

        $this->assertCount(1, array_filter($results, fn ($result) => $result['status'] === 'booked'));
        $this->assertCount(1, array_filter($results, fn ($result) => $result['status'] === 'rejected' && in_array('seats', $result['errors'] ?? [], true)));
        $winner = collect($results)->firstWhere('status', 'booked');
        $this->assertSame(1, DB::table('train_booking_seats')->where('train_trip_id', $fixture['trip_id'])->whereNull('released_at')->count());
        $this->assertDatabaseHas('train_booking_seats', [
            'train_trip_id' => $fixture['trip_id'], 'trip_seat_inventory_id' => $fixture['inventory_id'],
            'train_booking_id' => $winner['booking_id'], 'released_at' => null,
        ]);
        $this->assertSame(1, DB::table('train_bookings')->where('train_trip_id', $fixture['trip_id'])->where('status', 'confirmed')->count());
        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $fixture['inventory_id'], 'status' => 'booked', 'current_booking_id' => $winner['booking_id'],
        ]);
    }

    public function test_cancellation_promotes_waiter_before_competing_booking_can_take_seat(): void
    {
        $fixture = $this->makeFixture();
        $service = app(TrainBookingService::class);
        $owner = User::findOrFail($fixture['user_ids'][0]);
        $waiter = User::findOrFail($fixture['user_ids'][1]);
        $booking = $service->book($owner, $fixture['trip_id'], [$fixture['inventory_id']], [['name' => 'Owner']], $fixture['from_id'], $fixture['to_id']);
        $entry = $service->joinWaitlist($waiter, $fixture['trip_id'], [['name' => 'Waiter']], $fixture['from_id'], $fixture['to_id']);

        $results = $this->raceForTrip($fixture, [
            ['cancel', ['user_id' => $owner->id, 'booking_id' => $booking->id]],
            ['book', $this->bookPayload($fixture, $fixture['user_ids'][2])],
        ]);

        $this->assertCount(1, array_filter($results, fn ($result) => $result['status'] === 'cancelled'));
        $this->assertCount(1, array_filter($results, fn ($result) => $result['status'] === 'rejected' && in_array('seats', $result['errors'] ?? [], true)));
        $promotedId = DB::table('train_waitlist_entries')->where('id', $entry->id)->value('promoted_booking_id');
        $this->assertNotNull($promotedId);
        $this->assertDatabaseHas('train_bookings', ['id' => $booking->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('train_bookings', ['id' => $promotedId, 'status' => 'confirmed']);
        $this->assertDatabaseHas('train_waitlist_entries', ['id' => $entry->id, 'status' => 'promoted']);
        $this->assertDatabaseHas('train_booking_seats', [
            'train_trip_id' => $fixture['trip_id'], 'trip_seat_inventory_id' => $fixture['inventory_id'],
            'train_booking_id' => $promotedId, 'released_at' => null,
        ]);
        $this->assertDatabaseHas('trip_seat_inventories', [
            'id' => $fixture['inventory_id'], 'status' => 'booked', 'current_booking_id' => $promotedId,
        ]);
        $this->assertSame(1, DB::table('train_booking_seats')->where('train_trip_id', $fixture['trip_id'])->whereNull('released_at')->count());
    }

    public function test_booking_paused_after_cutoff_check_must_not_confirm_after_departure(): void
    {
        $fixture = $this->makeFixture();
        $boardingAt = now()->addSeconds(12);
        TrainTrip::query()->whereKey($fixture['trip_id'])->update(['starts_at' => $boardingAt, 'ends_at' => $boardingAt->copy()->addHours(3)]);
        $barrier = $this->makeBarrier();
        try {
            $worker = $this->startWorker($barrier, 'cutoff', 'book', $this->bookPayload($fixture, $fixture['user_ids'][0]), 'inventory');
            $this->waitForMarker($barrier . '/ready-cutoff', [$worker]);
            touch($barrier . '/go');
            $this->waitForMarker($barrier . '/attempt-cutoff', [$worker]);
            $this->assertTrue(now()->lessThan($boardingAt), 'Worker must reach the pause before departure.');
            $deadline = microtime(true) + 20;
            while (now()->lessThanOrEqualTo($boardingAt)) {
                if (microtime(true) >= $deadline) {
                    throw new \RuntimeException('Cutoff clock timed out.');
                }
                usleep(10000);
            }
            touch($barrier . '/resume');
            $result = $this->workerResult($worker);
            $this->assertSame('rejected', $result['status'], 'A booking must not confirm after the selected boarding departure.');
            $this->assertSame(0, DB::table('train_booking_seats')->where('train_trip_id', $fixture['trip_id'])->whereNull('released_at')->count());
            $this->assertDatabaseHas('trip_seat_inventories', ['id' => $fixture['inventory_id'], 'status' => 'available', 'current_booking_id' => null]);
        } finally {
            $this->stopWorker($worker ?? null);
            $this->clearBarrier($barrier);
        }
    }

    public function test_cancellation_crossing_cutoff_rolls_back_seat_release(): void
    {
        $fixture = $this->makeFixture();
        $owner = User::findOrFail($fixture['user_ids'][0]);
        $booking = app(TrainBookingService::class)->book($owner, $fixture['trip_id'], [$fixture['inventory_id']], [['name' => 'Owner']], $fixture['from_id'], $fixture['to_id']);
        $cutoff = now()->addSeconds(12);
        $booking->update(['boarding_at' => $cutoff]);
        $result = $this->cancelAcrossCutoff($owner->id, $booking->id, $cutoff, 'inventory');
        $this->assertSame('rejected', $result['status']);
        $this->assertDatabaseHas('train_bookings', ['id' => $booking->id, 'status' => 'confirmed', 'cancelled_at' => null]);
        $this->assertDatabaseHas('trip_seat_inventories', ['id' => $fixture['inventory_id'], 'status' => 'booked', 'current_booking_id' => $booking->id]);
        $this->assertDatabaseHas('train_booking_seats', ['train_booking_id' => $booking->id, 'released_at' => null]);
    }

    public function test_promotion_crossing_cutoff_rolls_back_entire_cancellation(): void
    {
        $fixture = $this->makeFixture();
        $service = app(TrainBookingService::class);
        $owner = User::findOrFail($fixture['user_ids'][0]);
        $booking = $service->book($owner, $fixture['trip_id'], [$fixture['inventory_id']], [['name' => 'Owner']], $fixture['from_id'], $fixture['to_id']);
        $entry = $service->joinWaitlist(User::findOrFail($fixture['user_ids'][1]), $fixture['trip_id'], [['name' => 'Waiter']], $fixture['from_id'], $fixture['to_id']);
        $cutoff = now()->addSeconds(12);
        $entry->update(['boarding_at' => $cutoff]);
        $result = $this->cancelAcrossCutoff($owner->id, $booking->id, $cutoff, 'promotion');
        $this->assertSame('rejected', $result['status']);
        $this->assertDatabaseHas('train_waitlist_entries', ['id' => $entry->id, 'status' => 'waiting', 'promoted_booking_id' => null]);
        $this->assertDatabaseHas('train_bookings', ['id' => $booking->id, 'status' => 'confirmed']);
        $this->assertSame(1, DB::table('train_bookings')->where('train_trip_id', $fixture['trip_id'])->count());
        $this->assertDatabaseHas('trip_seat_inventories', ['id' => $fixture['inventory_id'], 'status' => 'booked', 'current_booking_id' => $booking->id]);
    }

    private function cancelAcrossCutoff(int $userId, int $bookingId, $cutoff, string $pauseAt): array
    {
        $barrier = $this->makeBarrier();
        try {
            $worker = $this->startWorker($barrier, 'late', 'cancel', ['user_id' => $userId, 'booking_id' => $bookingId], $pauseAt);
            $this->waitForMarker($barrier . '/ready-late', [$worker]);
            touch($barrier . '/go');
            $this->waitForMarker($barrier . '/attempt-late', [$worker]);
            $this->assertTrue(now()->lessThan($cutoff), 'Worker must pause before departure.');
            $deadline = microtime(true) + 20;
            while (now()->lessThanOrEqualTo($cutoff)) {
                if (microtime(true) >= $deadline) {
                    throw new \RuntimeException('Cutoff clock timed out.');
                }
                usleep(10000);
            }
            touch($barrier . '/resume');

            return $this->workerResult($worker);
        } finally {
            $this->stopWorker($worker ?? null);
            $this->clearBarrier($barrier);
        }
    }

    private function makeFixture(): array
    {
        $token = bin2hex(random_bytes(5));
        $fixture = DB::transaction(function () use ($token): array {
            $train = Train::factory()->create(['code' => 'RACE' . $token]);
            $from = Station::factory()->create(['code' => 'A' . substr($token, 0, 8)]);
            $to = Station::factory()->create(['code' => 'B' . substr($token, 0, 8)]);
            TrainRouteStop::create(['train_id' => $train->id, 'station_id' => $from->id, 'stop_order' => 1, 'departure_offset_minutes' => 0]);
            TrainRouteStop::create(['train_id' => $train->id, 'station_id' => $to->id, 'stop_order' => 2, 'arrival_offset_minutes' => 180]);
            $trip = TrainTrip::factory()->for($train)->create(['starts_at' => now()->addHour(), 'ends_at' => now()->addHours(4)]);
            $coach = TrainCoach::factory()->for($train)->create();
            $seat = TrainSeat::factory()->for($train)->for($coach, 'coach')->create();
            $inventory = TripSeatInventory::factory()->for($trip, 'trip')->for($seat, 'seat')->create(['train_id' => $train->id]);
            $users = [];
            for ($index = 0; $index < 3; $index++) {
                $users[] = User::factory()->create(['email' => "race{$index}-{$token}@example.test"])->id;
            }

            return ['train_id' => $train->id, 'trip_id' => $trip->id, 'inventory_id' => $inventory->id,
                'from_id' => $from->id, 'to_id' => $to->id, 'user_ids' => $users];
        });
        $this->fixtures[] = $fixture;

        return $fixture;
    }

    private function bookPayload(array $fixture, int $userId): array
    {
        return ['user_id' => $userId, 'trip_id' => $fixture['trip_id'], 'inventory_id' => $fixture['inventory_id'],
            'boarding_station_id' => $fixture['from_id'], 'destination_station_id' => $fixture['to_id']];
    }

    private function raceForTrip(array $fixture, array $requests): array
    {
        $barrier = $this->makeBarrier();
        $locked = false;
        $workers = [];
        try {
            DB::beginTransaction();
            $locked = true;
            DB::table('train_trips')->where('id', $fixture['trip_id'])->lockForUpdate()->first();
            foreach ($requests as $index => [$action, $payload]) {
                $workers[] = $this->startWorker($barrier, (string) $index, $action, $payload, 'trip');
            }
            foreach ($workers as $index => $worker) {
                $this->waitForMarker($barrier . '/ready-' . $index, [$worker]);
            }
            touch($barrier . '/go');
            foreach ($workers as $index => $worker) {
                $this->waitForMarker($barrier . '/attempt-' . $index, [$worker]);
            }
            DB::commit();
            $locked = false;

            return array_map(fn (Process $worker) => $this->workerResult($worker), $workers);
        } finally {
            if ($locked) {
                DB::rollBack();
            }
            foreach ($workers as $worker) {
                $this->stopWorker($worker);
            }
            $this->clearBarrier($barrier);
        }
    }

    private function startWorker(string $barrier, string $tag, string $action, array $payload, string $pauseAt): Process
    {
        $worker = new Process([PHP_BINARY, base_path('tests/Support/train-concurrency-worker.php'), $action,
            json_encode($payload, JSON_THROW_ON_ERROR), $barrier, $tag, $pauseAt], base_path(), [
            'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'smartseat_train_test',
        ]);
        $worker->setTimeout(45);
        $worker->start();
        $this->workers[] = $worker;

        return $worker;
    }

    private function workerResult(Process $worker): array
    {
        $exit = $worker->wait();
        $result = json_decode(trim($worker->getOutput()), true);
        $this->assertSame(0, $exit, 'Worker failed: ' . $worker->getErrorOutput() . ' ' . $worker->getOutput());
        $this->assertIsArray($result);

        return $result;
    }

    private function waitForMarker(string $path, array $workers): void
    {
        $deadline = microtime(true) + 20;
        while (! is_file($path)) {
            foreach ($workers as $worker) {
                if (! $worker->isRunning()) {
                    throw new \RuntimeException('Worker exited before barrier: ' . $worker->getErrorOutput() . ' ' . $worker->getOutput());
                }
            }
            if (microtime(true) >= $deadline) {
                throw new \RuntimeException('Concurrency barrier timed out: ' . basename($path));
            }
            usleep(10000);
        }
    }

    private function makeBarrier(): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'smartseat-race-' . bin2hex(random_bytes(8));
        if (! mkdir($path, 0700)) {
            throw new \RuntimeException('Could not create concurrency barrier.');
        }

        return $path;
    }

    private function stopWorker(?Process $worker): void
    {
        if ($worker?->isRunning()) {
            $worker->stop(1);
        }
    }

    private function clearBarrier(string $path): void
    {
        foreach (glob($path . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($path);
    }
}
