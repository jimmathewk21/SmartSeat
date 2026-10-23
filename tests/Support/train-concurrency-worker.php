<?php

use App\Models\User;
use App\Services\TrainBookingService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[$script, $action, $payloadJson, $barrier, $tag, $pauseAt] = $argv;
$payload = json_decode($payloadJson, true, flags: JSON_THROW_ON_ERROR);
$actual = DB::selectOne('SELECT DATABASE() AS db')->db;
if ($app->environment() !== 'testing' || config('database.default') !== 'mysql'
    || config('database.connections.mysql.database') !== 'smartseat_train_test'
    || $actual !== 'smartseat_train_test'
    || getenv('APP_ENV') !== 'testing' || getenv('DB_CONNECTION') !== 'mysql'
    || getenv('DB_DATABASE') !== 'smartseat_train_test') {
    fwrite(STDERR, "Concurrency worker refused an unexpected database target.\n");
    exit(2);
}

function awaitBarrier(string $path): void
{
    $deadline = microtime(true) + 35;
    while (! is_file($path)) {
        if (microtime(true) >= $deadline) {
            throw new RuntimeException('Concurrency barrier timed out.');
        }
        usleep(10000);
    }
}

file_put_contents($barrier . '/ready-' . $tag, '1');
awaitBarrier($barrier . '/go');

$paused = false;
DB::connection()->beforeExecuting(function (string $sql) use ($barrier, $tag, $pauseAt, &$paused): void {
    $promotion = $pauseAt === 'promotion' && str_starts_with(strtolower($sql), 'update')
        && str_contains(strtolower($sql), 'train_waitlist_entries');
    if ($pauseAt === 'promotion' && ! $promotion) {
        return;
    }
    if ($paused || (! $promotion && ! str_contains(strtolower($sql), 'for update'))) {
        return;
    }
    $target = $promotion ? 'train_waitlist_entries' : ($pauseAt === 'inventory' ? 'trip_seat_inventories' : 'train_trips');
    if (! str_contains(strtolower($sql), $target)) {
        return;
    }
    $paused = true;
    file_put_contents($barrier . '/attempt-' . $tag, '1');
    if ($pauseAt === 'inventory' || $promotion) {
        awaitBarrier($barrier . '/resume');
    }
});

try {
    $service = app(TrainBookingService::class);
    $user = User::query()->findOrFail($payload['user_id']);
    if ($action === 'book') {
        $booking = $service->book(
            $user, $payload['trip_id'], [$payload['inventory_id']],
            [['name' => 'Concurrency Passenger']], $payload['boarding_station_id'], $payload['destination_station_id']
        );
        $result = ['status' => 'booked', 'booking_id' => $booking->id];
    } elseif ($action === 'cancel') {
        $booking = $service->cancel($user, $payload['booking_id']);
        $result = ['status' => 'cancelled', 'booking_id' => $booking->id];
    } else {
        throw new InvalidArgumentException('Unknown concurrency worker action.');
    }
} catch (ValidationException $exception) {
    $result = ['status' => 'rejected', 'errors' => array_keys($exception->errors())];
} catch (Throwable $exception) {
    $result = ['status' => 'error', 'type' => get_class($exception), 'message' => $exception->getMessage()];
}

echo json_encode($result, JSON_THROW_ON_ERROR) . PHP_EOL;
exit($result['status'] === 'error' ? 1 : 0);
