<?php

// Pure transport fixture: no Laravel, Composer bootstrap, PDO, SQL or database configuration.
require dirname(__DIR__).'/MariaDb/WorkerChannel.php';

use Tests\MariaDb\WorkerChannel;

$channel = null;
try {
    $channel = WorkerChannel::connect($argv);
    $request = $channel->receive('request');
    $job = $request['job'];
    $behavior = $job['behavior'] ?? 'normal';
    if ($behavior === 'exit') {
        exit(7);
    }
    if ($behavior === 'hang') {
        sleep(10);
    }
    if ($behavior === 'stderr') {
        fwrite(STDERR, 'private-diagnostic-marker');
        sleep(10);
    }
    if ($behavior === 'wrong-phase') {
        $channel->send(['phase' => 'unexpected']);
        sleep(10);
    }
    usleep((int) ($job['ready_delay_ms'] ?? 0) * 1000);
    $ready = isset($job['connection']) ? ['connection' => $job['connection']] : [];
    $channel->barrier('ready', 'go', $ready);
    if ($behavior === 'hang-at-begin') {
        sleep(10);
    }
    $started = microtime(true);
    $channel->barrier('begun', 'begin');
    $workStarted = microtime(true);
    if ($job['lock_barrier'] ?? false) {
        $channel->barrier('locked', 'lock');
    }
    usleep(20000);
    $channel->send([
        'phase' => 'result', 'status' => 'ok', 'pid' => getmypid(), 'slot' => $request['slot'],
        'transaction_started_at' => $started, 'work_started_at' => $workStarted, 'finished_at' => microtime(true),
        'context_hash' => hash('sha256', json_encode($request['context'], JSON_THROW_ON_ERROR)),
    ]);
    if ($behavior === 'hang-after-result') {
        sleep(10);
    }
} catch (Throwable) {
    exit(1);
} finally {
    $channel?->close();
}
