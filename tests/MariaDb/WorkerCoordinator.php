<?php

namespace Tests\MariaDb;

use RuntimeException;

/** Process/transport coordination only. This class never boots Laravel or opens a database. */
final class WorkerCoordinator
{
    public static function run(string $worker, array $jobs, #[\SensitiveParameter] array $context = [], float $timeout = 60, bool $requireConnections = false): array
    {
        $root = realpath(dirname(__DIR__, 2));
        $worker = realpath($worker);
        if (! $worker || ! str_starts_with(strtolower($worker), strtolower($root).DIRECTORY_SEPARATOR)
            || ! array_is_list($jobs) || count($jobs) < 2 || count($jobs) > 4
            || ! is_finite($timeout) || $timeout <= 0 || $timeout > 120) {
            throw new RuntimeException('Invalid worker group configuration.');
        }
        $directory = self::directory().DIRECTORY_SEPARATOR.bin2hex(random_bytes(12));
        if (! mkdir($directory, 0700)) {
            throw new RuntimeException('Unable to create the worker output directory.');
        }
        $server = null;
        $processes = $channels = $owners = $states = $results = $tokens = $pids = $exits = $connections = $finishedAt = $exitObservedAt = [];
        $deadline = WorkerChannel::clock() + $timeout;
        $started = $begun = $locked = false;
        try {
            $server = @stream_socket_server('tcp://127.0.0.1:0', $code, $error);
            if (! is_resource($server) || ! stream_set_blocking($server, false)) {
                throw new RuntimeException('Unable to start the loopback worker coordinator.');
            }
            $endpoint = 'tcp://'.stream_socket_get_name($server, false);
            foreach ($jobs as $slot => $job) {
                $tokens[$slot] = bin2hex(random_bytes(32));
                if (file_put_contents($directory.'/'.$slot.'-input', '') === false) {
                    throw new RuntimeException('Unable to create empty worker input.');
                }
                // No anonymous pipes. Secrets are sent only after the authenticated socket handshake.
                $processes[$slot] = @proc_open([
                    PHP_BINARY, '-d', 'zend.exception_ignore_args=1', '-d', 'log_errors=0', '-d', 'display_errors=stderr',
                    $worker, $endpoint, $tokens[$slot], (string) $slot, (string) $timeout,
                ], [
                    0 => ['file', $directory.'/'.$slot.'-input', 'r'],
                    1 => ['file', $directory.'/'.$slot.'-stdout', 'w'],
                    2 => ['file', $directory.'/'.$slot.'-stderr', 'w'],
                ], $unused, $root, null, ['bypass_shell' => true]);
                if (! is_resource($processes[$slot])) {
                    throw new RuntimeException('Unable to create an independent worker.');
                }
                $status = proc_get_status($processes[$slot]);
                $pids[$slot] = $status['pid'];
                $states[$slot] = 'hello';
            }
            if (count(array_unique($pids)) !== count($jobs) || in_array(getmypid(), $pids, true)) {
                throw new RuntimeException('Independent worker processes were not observed.');
            }
            while (true) {
                if (WorkerChannel::clock() > $deadline) {
                    throw new RuntimeException('Parallel worker coordination timed out.');
                }
                while (($socket = @stream_socket_accept($server, 0)) !== false) {
                    if (count($channels) >= count($jobs)) {
                        fclose($socket);
                        throw new RuntimeException('Unexpected worker connection.');
                    }
                    $channels[] = new WorkerChannel($socket, $deadline);
                }
                foreach ($channels as $id => $channel) {
                    foreach ($channel->poll() as $message) {
                        if (! isset($owners[$id])) {
                            $slot = $message['slot'] ?? null;
                            if ($message['phase'] !== 'hello' || ! is_int($slot) || ! isset($states[$slot])
                                || $states[$slot] !== 'hello' || ($message['pid'] ?? null) !== $pids[$slot]
                                || ! is_string($message['token'] ?? null) || ! hash_equals($tokens[$slot], $message['token'])) {
                                throw new RuntimeException('Worker identity handshake failed.');
                            }
                            $owners[$id] = $slot;
                            $states[$slot] = 'ready';
                            $channel->queue(['phase' => 'request', 'context' => $context, 'job' => $jobs[$slot], 'slot' => $slot, 'parties' => count($jobs)]);

                            continue;
                        }
                        $slot = $owners[$id];
                        if ($message['phase'] === 'ready' && $states[$slot] === 'ready') {
                            $states[$slot] = 'waiting-go';
                            if ($requireConnections) {
                                $connection = $message['connection'] ?? null;
                                if ((! is_int($connection) && (! is_string($connection) || ! ctype_digit($connection))) || (int) $connection <= 0) {
                                    throw new RuntimeException('Invalid worker database connection identity.');
                                }
                                $connections[$slot] = (string) $connection;
                            }
                        } elseif ($message['phase'] === 'begun' && $started && $states[$slot] === 'begun') {
                            $states[$slot] = 'waiting-begin';
                        } elseif ($message['phase'] === 'locked' && $begun && $states[$slot] === 'working' && count($jobs) === 2) {
                            $states[$slot] = 'waiting-lock';
                        } elseif ($message['phase'] === 'result' && $begun && $states[$slot] === 'working') {
                            if (! isset($message['transaction_started_at'], $message['finished_at'])
                                || ! is_numeric($message['transaction_started_at']) || ! is_numeric($message['finished_at'])
                                || ! is_finite((float) $message['transaction_started_at']) || ! is_finite((float) $message['finished_at'])
                                || $message['transaction_started_at'] > $message['finished_at']) {
                                throw new RuntimeException('Invalid worker overlap evidence.');
                            }
                            $results[$slot] = array_replace($message, ['pid' => $pids[$slot]]);
                            $states[$slot] = 'done';
                            $finishedAt[$slot] = WorkerChannel::clock();
                        } else {
                            throw new RuntimeException('Worker sent an unexpected coordination phase.');
                        }
                    }
                    if ($channel->closed() && (! isset($owners[$id]) || ! isset($results[$owners[$id]]))) {
                        throw new RuntimeException('Worker disconnected before a complete result.');
                    }
                }
                if (! $started && count(array_filter($states, fn ($state) => $state === 'waiting-go')) === count($jobs)) {
                    if ($requireConnections && count(array_unique($connections)) !== count($jobs)) {
                        throw new RuntimeException('Independent database connections were not observed.');
                    }
                    self::release($channels, 'go');
                    $states = array_fill(0, count($jobs), 'begun');
                    $started = true;
                }
                if (! $begun && count(array_filter($states, fn ($state) => $state === 'waiting-begin')) === count($jobs)) {
                    self::release($channels, 'begin');
                    $states = array_fill(0, count($jobs), 'working');
                    $begun = true;
                }
                if (! $locked && count($jobs) === 2 && count(array_filter($states, fn ($state) => $state === 'waiting-lock')) === 2) {
                    self::release($channels, 'lock');
                    $states = array_fill(0, 2, 'working');
                    $locked = true;
                }
                foreach ($processes as $slot => $process) {
                    foreach (['stdout', 'stderr'] as $output) {
                        clearstatcache(true, $directory.'/'.$slot.'-'.$output);
                        $size = @filesize($directory.'/'.$slot.'-'.$output);
                        if ($size === false) {
                            throw new RuntimeException('Unable to inspect worker output.');
                        }
                        if ($size > 0) {
                            throw new RuntimeException('Worker wrote unexpected output; content suppressed.');
                        }
                    }
                    if (! isset($exits[$slot])) {
                        $status = proc_get_status($process);
                        if (! $status['running']) {
                            $exits[$slot] = $status['exitcode']; // Cache before another PHP 8.2 status poll.
                            $exitObservedAt[$slot] = WorkerChannel::clock();
                            if ($status['exitcode'] !== 0) {
                                throw new RuntimeException('Worker exited unsuccessfully.');
                            }
                        }
                    }
                    if (isset($exits[$slot]) && ! isset($results[$slot]) && WorkerChannel::clock() - $exitObservedAt[$slot] > 0.1) {
                        throw new RuntimeException('Worker exited without a complete result.');
                    }
                    if (isset($results[$slot]) && ! isset($exits[$slot]) && WorkerChannel::clock() - $finishedAt[$slot] > 1) {
                        throw new RuntimeException('Worker did not exit after its result.');
                    }
                }
                if (count($results) === count($jobs) && count($exits) === count($jobs)) {
                    break;
                }
                usleep(5000);
            }
            ksort($results);
            if (max(array_column($results, 'transaction_started_at')) > min(array_column($results, 'finished_at'))) {
                throw new RuntimeException('Overlapping transactions were not observed.');
            }

            return array_values($results);
        } finally {
            foreach ($channels as $channel) {
                $channel->close();
            }
            if (is_resource($server)) {
                fclose($server);
            }
            // Terminate first, poll with a shared deadline, and never proc_close a running child.
            foreach ($processes as $process) {
                if (is_resource($process) && proc_get_status($process)['running']) {
                    proc_terminate($process);
                }
            }
            $stopDeadline = WorkerChannel::clock() + 2;
            $running = [];
            do {
                $running = [];
                foreach ($processes as $slot => $process) {
                    if (is_resource($process) && proc_get_status($process)['running']) {
                        $running[$slot] = true;
                        proc_terminate($process, 9);
                    }
                }
                if ($running !== []) {
                    usleep(10000);
                }
            } while ($running !== [] && WorkerChannel::clock() < $stopDeadline);
            foreach ($processes as $slot => $process) {
                if (is_resource($process) && ! isset($running[$slot])) {
                    proc_close($process);
                }
            }
            if ($running !== []) {
                throw new RuntimeException('Unable to stop a worker within the shutdown deadline.');
            }
            foreach (array_keys($jobs) as $slot) {
                foreach (['input', 'stdout', 'stderr'] as $name) {
                    if (is_file($directory.'/'.$slot.'-'.$name)) {
                        if (! unlink($directory.'/'.$slot.'-'.$name)) {
                            throw new RuntimeException('Unable to remove stopped worker output.');
                        }
                    }
                }
            }
            if (! rmdir($directory)) {
                throw new RuntimeException('Unable to remove the stopped worker directory.');
            }
        }
    }

    private static function release(array $channels, string $phase): void
    {
        foreach ($channels as $channel) {
            $channel->queue(['phase' => $phase]);
        }
    }

    public static function directory(): string
    {
        $root = realpath(dirname(__DIR__, 2));
        $directory = $root.'/storage/framework/testing/mariadb-concurrency';
        $ancestor = $directory;
        while (! is_dir($ancestor)) {
            $ancestor = dirname($ancestor);
        }
        $prefix = strtolower($root).DIRECTORY_SEPARATOR;
        if (! str_starts_with(strtolower(realpath($ancestor)), $prefix)) {
            throw new RuntimeException('Worker output ancestor resolves outside the workspace.');
        }
        if (! is_dir($directory) && ! mkdir($directory, 0700, true)) {
            throw new RuntimeException('Unable to create the worker output directory.');
        }
        $resolved = realpath($directory);
        if (! $resolved || ! str_starts_with(strtolower($resolved), $prefix)) {
            throw new RuntimeException('Worker output must remain inside the workspace.');
        }

        return $resolved;
    }
}
