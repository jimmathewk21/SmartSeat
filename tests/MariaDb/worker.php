<?php

// PREPARED ONLY. Never loaded by the default PHPUnit suite.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Models\Booking;
use App\Models\SeatAssignment;
use App\Models\TrainTrip;
use App\Models\TripSeatInventory;
use App\Models\User;
use App\Services\BookingEngine;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\MariaDb\Runtime;
use Tests\MariaDb\WorkerChannel;

$transactionStarted = null;
$attempts = 0;
$channel = null;
function workerResult(array $result): void
{
    global $transactionStarted, $channel;
    try {
        $channel->send($result + ['phase' => 'result', 'transaction_started_at' => $transactionStarted, 'finished_at' => microtime(true)]);
    } catch (Throwable) {
        exit(1); // The parent handles disconnects/timeouts without logging payloads.
    }
}

try {
    $channel = WorkerChannel::connect($argv);
    $request = $channel->receive('request');
    Runtime::boot($request['context']['target']);
    $slot = $request['slot'];
    if (! is_int($slot) || $slot < 0 || $slot > 3) {
        throw new RuntimeException('Invalid worker identity.');
    }
    $channel->barrier('ready', 'go', ['connection' => DB::selectOne('SELECT CONNECTION_ID() AS id')->id]);
    $job = $request['job'];
    $parties = $request['parties'];
    if (! is_int($parties) || $parties < 2 || $parties > 4) {
        throw new RuntimeException('Invalid transaction barrier participant count.');
    }
    Event::listen(TransactionBeginning::class, function ($event) use ($channel, $job, &$transactionStarted) {
        if ($transactionStarted !== null || $event->connection->transactionLevel() !== 1) {
            return;
        }
        $transactionStarted = microtime(true);
        $channel->barrier('begun', 'begin');
        if (isset($job['delay_ms'])) {
            usleep(min(1000, max(0, (int) $job['delay_ms'])) * 1000);
        }
    });
    $engine = app(BookingEngine::class);
    if (in_array($job['operation'], ['book_failure', 'book_retry'], true)) {
        Event::listen('eloquent.creating: '.SeatAssignment::class, function () use (&$attempts, $job) {
            $attempts++;
            if ($job['operation'] === 'book_failure' && $attempts === 2) {
                throw new RuntimeException('Injected allocation rollback fixture.');
            }
            if ($job['operation'] === 'book_retry' && $attempts === 1) {
                $previous = new PDOException('Deadlock found during injected retry probe', 40001);
                $previous->errorInfo = ['40001', 1213, 'Injected retry probe'];
                throw new QueryException('concurrency', 'fixture', [], $previous);
            }
        });
    }
    $value = match ($job['operation']) {
        'book', 'book_failure', 'book_retry' => $engine->book(User::findOrFail($job['owner']), TrainTrip::findOrFail($job['trip']), $job['details'], 'reserve', $job['seats'], $job['key'])->reference,
        'cancel' => $engine->cancel(User::findOrFail($job['owner']), Booking::findOrFail($job['booking']))->reference,
        'promote' => $engine->promoteTrip(TrainTrip::findOrFail($job['trip'])),
        'raw_assignment' => DB::transaction(fn () => SeatAssignment::create(['passenger_id' => $job['passenger'], 'inventory_id' => $job['inventory'], 'trip_id' => $job['trip'], 'status' => 'active'])->id),
        'deadlock' => DB::transaction(function () use ($job, $channel, &$attempts) {
            $attempts++;
            TripSeatInventory::whereKey($job['first'])->lockForUpdate()->firstOrFail();
            if ($attempts === 1) {
                $channel->barrier('locked', 'lock');
            }
            TripSeatInventory::whereKey($job['second'])->lockForUpdate()->firstOrFail();

            return 'locked';
        }, 3),
        default => throw new RuntimeException('Unknown prepared scenario.'),
    };
    workerResult(['status' => 'ok', 'value' => $value, 'attempts' => $attempts]);
} catch (ValidationException) {
    workerResult(['status' => 'conflict']);
} catch (QueryException $exception) {
    workerResult(['status' => (int) ($exception->errorInfo[1] ?? 0) === 1062 ? 'constraint_conflict' : 'failed']);
} catch (Throwable $exception) {
    // No exception messages/SQL/bindings or connection credentials are emitted.
    if ($channel === null || $channel->closed()) {
        exit(1);
    }
    workerResult(['status' => $exception->getMessage() === 'Injected allocation rollback fixture.' ? 'injected_failure' : 'failed', 'attempts' => $attempts]);
} finally {
    $channel?->close();
}
