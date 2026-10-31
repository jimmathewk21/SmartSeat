<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\MariaDb\ParallelWorkers;
use Tests\MariaDb\WorkerChannel;
use Tests\MariaDb\WorkerCoordinator;

class WorkerCoordinationTest extends TestCase
{
    private function runWorkers(array $jobs, array $context = [], float $timeout = 5, bool $requireConnections = false): array
    {
        return WorkerCoordinator::run(dirname(__DIR__).'/Fixtures/coordination-worker.php', $jobs, $context, $timeout, $requireConnections);
    }

    private function assertNoRunFiles(): void
    {
        $this->assertSame([], glob(WorkerCoordinator::directory().DIRECTORY_SEPARATOR.'*'));
    }

    public function test_separate_windows_processes_exchange_context_and_synchronize_all_barriers_without_a_database(): void
    {
        $context = ['private-canary' => str_repeat('socket-only-', 5000)];
        $results = $this->runWorkers([
            ['ready_delay_ms' => 100, 'lock_barrier' => true],
            ['ready_delay_ms' => 10, 'lock_barrier' => true],
        ], $context);
        $this->assertCount(2, array_unique(array_column($results, 'pid')));
        $this->assertNotContains(getmypid(), array_column($results, 'pid'));
        $this->assertSame([0, 1], array_column($results, 'slot'));
        foreach ($results as $result) {
            $this->assertSame('ok', $result['status']);
            $this->assertSame(hash('sha256', json_encode($context)), $result['context_hash']);
            $this->assertGreaterThanOrEqual(max(array_column($results, 'transaction_started_at')), $result['work_started_at']);
        }
        $this->assertLessThanOrEqual(min(array_column($results, 'finished_at')), max(array_column($results, 'transaction_started_at')));
        $this->assertNoRunFiles();
    }

    public function test_four_workers_synchronize_and_repeated_runs_do_not_reuse_stale_coordination_state(): void
    {
        foreach ([4, 2] as $count) {
            $results = $this->runWorkers(array_fill(0, $count, []));
            $this->assertCount($count, array_unique(array_column($results, 'pid')));
            $this->assertSame(array_fill(0, $count, 'ok'), array_column($results, 'status'));
            $this->assertNoRunFiles();
        }
    }

    #[DataProvider('failures')]
    public function test_faults_fail_with_bounded_waits_and_clean_up_all_workers(string $behavior, string $expected, float $timeout): void
    {
        $before = WorkerChannel::clock();
        try {
            $this->runWorkers([['behavior' => $behavior], []], timeout: $timeout);
            $this->fail('Faulty worker must fail the run.');
        } catch (RuntimeException $exception) {
            $this->assertMatchesRegularExpression('/'.$expected.'/', $exception->getMessage());
            $this->assertStringNotContainsString('private-diagnostic-marker', $exception->getMessage());
        }
        $this->assertLessThan($timeout + 3, WorkerChannel::clock() - $before);
        $this->assertNoRunFiles(); // Cleanup runs only after all child processes are observed stopped.
    }

    public static function failures(): array
    {
        return [
            'early crash' => ['exit', '(exited|disconnected)', 3],
            'missing readiness' => ['hang', 'timed out', 0.5],
            'missing transaction participant' => ['hang-at-begin', 'timed out', 0.5],
            'stderr is sanitized' => ['stderr', 'unexpected output', 3],
            'invalid phase' => ['wrong-phase', 'unexpected coordination phase', 3],
            'result without exit' => ['hang-after-result', 'did not exit', 3],
        ];
    }

    public function test_duplicate_connection_identity_is_rejected_without_opening_a_database(): void
    {
        // Deliberately identical protocol metadata, not actual database connections.
        try {
            $this->runWorkers([['connection' => 1], ['connection' => 1]], requireConnections: true);
            $this->fail('Duplicate connection IDs must fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Independent database connections were not observed.', $exception->getMessage());
        }
        $this->assertNoRunFiles();
    }

    public function test_unapproved_production_target_is_rejected_before_spawning(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ParallelWorkers::run(['database' => 'smartseat_dev'], [[], []]);
    }

    public function test_oversized_parent_request_fails_and_stops_the_worker_group(): void
    {
        try {
            $this->runWorkers([[], []], ['private-canary' => str_repeat('x', WorkerChannel::MAX_FRAME_BYTES)]);
            $this->fail('Oversized outbound requests must fail before transmission.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Worker message exceeds its size limit.', $exception->getMessage());
        }
        $this->assertNoRunFiles();
    }

    private function socketPair(): array
    {
        $server = stream_socket_server('tcp://127.0.0.1:0', $code, $error);
        $client = stream_socket_client('tcp://'.stream_socket_get_name($server, false), $code, $error, 1);
        $peer = stream_socket_accept($server, 1);
        fclose($server);

        return [$client, new WorkerChannel($peer, WorkerChannel::clock() + 1)];
    }

    public function test_transport_handles_partial_frames_without_blocking(): void
    {
        [$client, $channel] = $this->socketPair();
        try {
            fwrite($client, '{"phase":');
            $this->assertSame([], $channel->poll());
            fwrite($client, '"ready"}'."\n");
            $this->assertSame(['phase' => 'ready'], $channel->receive('ready'));
        } finally {
            fclose($client);
            $channel->close();
        }
    }

    #[DataProvider('badFrames')]
    public function test_transport_rejects_malformed_oversized_and_truncated_frames(string $frame, string $expected): void
    {
        [$client, $channel] = $this->socketPair();
        try {
            stream_set_blocking($client, false);
            $offset = 0;
            $deadline = WorkerChannel::clock() + 1;
            while ($offset < strlen($frame)) {
                $written = fwrite($client, substr($frame, $offset, 8192));
                $this->assertNotFalse($written);
                $offset += $written;
                $channel->poll();
                if (WorkerChannel::clock() > $deadline) {
                    $this->fail('Test frame producer timed out.');
                }
            }
            fclose($client);
            $channel->receive('ready');
            $this->fail('Invalid transport frame must fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString($expected, $exception->getMessage());
        } finally {
            if (is_resource($client)) {
                fclose($client);
            }
            $channel->close();
        }
    }

    public static function badFrames(): array
    {
        return [
            'malformed' => ["{invalid}\n", 'Malformed worker message'],
            'truncated' => ['{"phase":', 'incomplete message'],
            'oversized' => [str_repeat('x', WorkerChannel::MAX_FRAME_BYTES + 1), 'size limit'],
        ];
    }
}
