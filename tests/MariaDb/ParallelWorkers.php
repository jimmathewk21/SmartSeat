<?php

namespace Tests\MariaDb;

final class ParallelWorkers
{
    public static function run(#[\SensitiveParameter] array $target, array $jobs): array
    {
        ApprovedTarget::validate($target); // Must run before spawning or any database access.

        return WorkerCoordinator::run(__DIR__.'/worker.php', $jobs, ['target' => $target], requireConnections: true);
    }
}
