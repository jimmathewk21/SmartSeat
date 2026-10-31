<?php

// PREPARED ONLY: separate explicit approval + fresh pre-provisioned schema are mandatory.
// Reads the approved descriptor from stdin. NEVER run this in the default test suite.
require dirname(__DIR__, 2).'/vendor/autoload.php';

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
use App\Services\BookingEngine;
use App\Services\RailwayCatalogue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\MariaDb\ParallelWorkers;
use Tests\MariaDb\Runtime;

function verifyPrepared(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function verifyConstraint(callable $write, int $error): void
{
    try {
        $write();
    } catch (QueryException $exception) {
        verifyPrepared((int) ($exception->errorInfo[1] ?? 0) === $error, 'Unexpected database error during constraint probe.');

        return;
    }
    throw new RuntimeException('The required database constraint did not reject the write.');
}

function fixtureJourney(): array
{
    $train = Train::factory()->create();
    foreach ([1, 2] as $order) {
        TrainRouteStop::factory()->create([
            'train_id' => $train->id, 'station_id' => Station::factory()->create()->id, 'stop_order' => $order,
            'arrival_offset_minutes' => 480 + $order * 180, 'departure_offset_minutes' => 485 + $order * 180,
        ]);
    }
    $coach = Coach::factory()->create(['train_id' => $train->id]);
    foreach ([1, 2] as $number) {
        Seat::factory()->create(['coach_id' => $coach->id, 'number' => $number]);
    }
    $trip = TrainTrip::factory()->create(['train_id' => $train->id]);
    app(RailwayCatalogue::class)->initializeInventory($trip);

    return ['trip' => $trip, 'seats' => $trip->inventory()->orderBy('id')->pluck('id')->all()];
}

function detailsFor(int $count = 1): array
{
    return ['boarding_order' => 1, 'alighting_order' => 2, 'travel_class' => 'SL', 'passengers' => array_map(
        fn ($i) => ['name' => 'Disposable worker fixture '.$i, 'age' => 30, 'gender' => 'other'], range(1, $count)
    )];
}

function jobFor(array $fixture, int $owner, ?string $key = null, ?array $seats = null): array
{
    $seats ??= [$fixture['seats'][0]];

    return ['operation' => 'book', 'owner' => $owner, 'trip' => $fixture['trip']->id,
        'seats' => $seats, 'key' => $key ?? (string) Str::uuid(), 'details' => detailsFor(count($seats))];
}

try {
    $target = json_decode(stream_get_contents(STDIN), true, 16, JSON_THROW_ON_ERROR);
    Runtime::boot($target);
    Runtime::emptyTarget(); // Stops on ANY existing application data; no migration or cleanup.
    $report = [];
    $engine = app(BookingEngine::class);
    $owners = User::factory()->count(3)->create();

    // Actual server enforcement, not DDL string inspection. All fixtures stay on the approved target.
    foreach (['aaa', "AAA\n", 'A1'] as $code) {
        verifyConstraint(fn () => DB::table('stations')->insert(['code' => $code, 'name' => 'Invalid constraint fixture', 'city' => 'Fixture']), 4025);
    }
    $fixture = fixtureJourney();
    verifyConstraint(fn () => DB::table('trip_seat_inventory')->insert([
        'trip_id' => $fixture['trip']->id, 'train_id' => $fixture['trip']->train_id, 'seat_id' => 0, 'status' => 'available',
    ]), 1452);
    $report['foreign_keys_and_checks'] = 'passed';

    $fixture = fixtureJourney();
    $results = ParallelWorkers::run($target, [jobFor($fixture, $owners[0]->id), jobFor($fixture, $owners[1]->id)]);
    verifyPrepared(count(array_filter($results, fn ($r) => $r['status'] === 'ok')) === 1, 'Same-seat race did not produce exactly one winner.');
    verifyPrepared(count(array_filter($results, fn ($r) => $r['status'] === 'conflict')) === 1, 'The losing reservation did not receive conflict feedback.');
    verifyPrepared(SeatAssignment::where('trip_id', $fixture['trip']->id)->where('status', 'active')->count() === 1, 'Same-seat uniqueness failed.');
    $report['same_seat'] = $results;

    $fixture = fixtureJourney();
    $key = (string) Str::uuid();
    $job = jobFor($fixture, $owners[0]->id, $key);
    $results = ParallelWorkers::run($target, [$job, $job]);
    verifyPrepared($results[0]['status'] === 'ok' && $results[1]['status'] === 'ok' && $results[0]['value'] === $results[1]['value'], 'Duplicate submissions did not resolve to one reference.');
    verifyPrepared(Booking::where('user_id', $owners[0]->id)->where('submission_key', $key)->count() === 1, 'Duplicate booking committed.');
    $report['duplicate_submissions'] = $results;

    $fixture = fixtureJourney();
    $reserved = $engine->book($owners[0], $fixture['trip'], detailsFor(2), 'reserve', $fixture['seats'], (string) Str::uuid());
    $waiting = $engine->book($owners[1], $fixture['trip'], detailsFor(2), 'waitlist', [], (string) Str::uuid());
    $results = ParallelWorkers::run($target, [
        ['operation' => 'cancel', 'owner' => $owners[0]->id, 'booking' => $reserved->id],
        ['operation' => 'promote', 'trip' => $fixture['trip']->id],
        ['operation' => 'promote', 'trip' => $fixture['trip']->id],
    ]);
    verifyPrepared(count(array_filter($results, fn ($r) => $r['status'] === 'ok')) === 3, 'A cancellation/promotion worker failed.');
    verifyPrepared($waiting->refresh()->status === 'confirmed' && $waiting->events()->where('action', 'promoted')->count() === 1, 'Concurrent promotion failed or repeated.');
    verifyPrepared($reserved->refresh()->status === 'cancelled', 'Cancellation did not commit.');
    $report['cancellation_and_promoters'] = $results;

    $fixture = fixtureJourney();
    $reserved = $engine->book($owners[0], $fixture['trip'], detailsFor(2), 'reserve', $fixture['seats'], (string) Str::uuid());
    $firstWaiting = $engine->book($owners[1], $fixture['trip'], detailsFor(2), 'waitlist', [], (string) Str::uuid());
    $lastWaiting = $engine->book($owners[2], $fixture['trip'], detailsFor(), 'waitlist', [], (string) Str::uuid());
    $results = ParallelWorkers::run($target, [
        ['operation' => 'cancel', 'owner' => $owners[0]->id, 'booking' => $reserved->id],
        ['operation' => 'promote', 'trip' => $fixture['trip']->id],
    ]);
    verifyPrepared(count(array_filter($results, fn ($r) => $r['status'] === 'ok')) === 2, 'A FIFO worker failed.');
    verifyPrepared($firstWaiting->refresh()->status === 'confirmed' && $lastWaiting->refresh()->status === 'waitlisted', 'A later waitlist group bypassed the oldest group.');
    $report['fifo_ordering'] = $results;

    $fixture = fixtureJourney();
    $jobs = [jobFor($fixture, $owners[0]->id, seats: $fixture['seats']), jobFor($fixture, $owners[1]->id, seats: $fixture['seats'])];
    $jobs[0]['operation'] = 'book_failure';
    $jobs[1]['delay_ms'] = 100;
    $results = ParallelWorkers::run($target, $jobs);
    verifyPrepared($results[0]['status'] === 'injected_failure' && $results[0]['attempts'] === 2 && $results[1]['status'] === 'ok', 'The allocation fault was not exercised as intended.');
    verifyPrepared(Booking::where('trip_id', $fixture['trip']->id)->count() === 1, 'Failed group left a booking.');
    verifyPrepared(Passenger::where('trip_id', $fixture['trip']->id)->count() === 2, 'Partial passengers survived rollback.');
    verifyPrepared(SeatAssignment::where('trip_id', $fixture['trip']->id)->where('status', 'active')->count() === 2, 'Group allocation was partial.');
    $report['allocation_rollback'] = $results;

    $fixture = fixtureJourney();
    $job = jobFor($fixture, $owners[0]->id);
    $job['operation'] = 'book_retry';
    $results = ParallelWorkers::run($target, [$job, $job]);
    verifyPrepared($results[0]['status'] === 'ok' && $results[1]['status'] === 'ok' && $results[0]['value'] === $results[1]['value'], 'Injected retry/deduplication failed.');
    verifyPrepared(max(array_column($results, 'attempts')) >= 2, 'The retry probe was not exercised.');
    $report['injected_engine_retry'] = $results;

    $fixture = fixtureJourney();
    $jobs = [];
    foreach ([0, 1] as $i) {
        $booking = Booking::factory()->create(['user_id' => $owners[$i]->id, 'trip_id' => $fixture['trip']->id]);
        $person = Passenger::factory()->create(['booking_id' => $booking->id]);
        $jobs[] = ['operation' => 'raw_assignment', 'trip' => $fixture['trip']->id, 'inventory' => $fixture['seats'][0], 'passenger' => $person->id];
    }
    $results = ParallelWorkers::run($target, $jobs);
    verifyPrepared(count(array_filter($results, fn ($r) => $r['status'] === 'ok')) === 1
        && count(array_filter($results, fn ($r) => $r['status'] === 'constraint_conflict')) === 1, 'The database unique index did not reject an uncoordinated duplicate.');
    $report['database_uniqueness'] = $results;

    $fixture = fixtureJourney();
    $results = ParallelWorkers::run($target, [
        ['operation' => 'deadlock', 'first' => $fixture['seats'][0], 'second' => $fixture['seats'][1]],
        ['operation' => 'deadlock', 'first' => $fixture['seats'][1], 'second' => $fixture['seats'][0]],
    ]);
    verifyPrepared($results[0]['status'] === 'ok' && $results[1]['status'] === 'ok' && max(array_column($results, 'attempts')) >= 2, 'Genuine lock-cycle retry was not observed.');
    $report['real_lock_cycle_retry'] = $results;

    verifyPrepared(! DB::table('seat_assignments')->select('inventory_id')->where('status', 'active')->groupBy('inventory_id')->havingRaw('COUNT(*) > 1')->exists(), 'Active assignment invariant failed.');
    echo json_encode(['status' => 'passed', 'scenarios' => $report, 'fixtures_retained' => true], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
} catch (Throwable) {
    fwrite(STDERR, "Prepared concurrency suite stopped. Review the approved harness and target without logging credentials. No database cleanup was performed.\n");
    exit(1);
}
