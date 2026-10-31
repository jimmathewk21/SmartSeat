<?php

namespace Tests\MariaDb;

use App\Support\RailwaySchema;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class Runtime
{
    /** Only the opt-in scripts call this; ordinary tests use the pure target guard only. */
    public static function boot(#[\SensitiveParameter] array $descriptor, bool $requireSchema = true): void
    {
        $target = ApprovedTarget::validate($descriptor); // Before Laravel boot or PDO access.
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) use ($target) {
            $app['config']->set([
                'app.env' => 'testing', 'app.debug' => false, 'cache.default' => 'array',
                'app.key' => 'base64:'.base64_encode(str_repeat('t', 32)),
                'session.driver' => 'array', 'queue.default' => 'sync', 'mail.default' => 'array',
                'logging.default' => 'null',
                'database.default' => 'concurrency',
                'database.connections' => ['concurrency' => [
                    'driver' => 'mariadb', 'host' => $target['host'], 'port' => $target['port'],
                    'database' => $target['database'], 'username' => $target['username'], 'password' => $target['password'],
                    'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true,
                    'isolation_level' => 'REPEATABLE READ',
                ]],
                'smartseat.intelligence.capture_waitlist_history' => false,
                'hashing.bcrypt.rounds' => 4,
            ]);
        });
        $app->make(Kernel::class)->bootstrap();
        $identity = DB::selectOne('SELECT DATABASE() AS name, VERSION() AS version, CONNECTION_ID() AS connection_id');
        if ($identity->name !== ApprovedTarget::DATABASE || stripos($identity->version, 'MariaDB') === false
            || preg_match('/(?:^|-)10\\.4\\./', $identity->version) !== 1) {
            throw new RuntimeException('Disposable MariaDB identity verification failed.');
        }
        $checks = DB::selectOne('SELECT @@foreign_key_checks AS foreign_keys, @@check_constraint_checks AS checks, @@tx_isolation AS isolation_level');
        if ((int) $checks->foreign_keys !== 1 || (int) $checks->checks !== 1 || $checks->isolation_level !== 'REPEATABLE-READ') {
            throw new RuntimeException('Foreign keys, CHECK constraints and REPEATABLE READ are required.');
        }
        if (! $requireSchema) {
            return; // Only migrate.php may provision an already-created, completely empty target.
        }
        foreach (array_merge(['users'], RailwaySchema::TABLES, ['booking_events', 'waitlist_prediction_snapshots']) as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                throw new RuntimeException('Pre-provisioned disposable schema is incomplete. This harness never runs migrations.');
            }
        }
        foreach (DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', ApprovedTarget::DATABASE)->get(['ENGINE']) as $table) {
            if ($table->ENGINE !== 'InnoDB') {
                throw new RuntimeException('All disposable tables must use InnoDB.');
            }
        }
    }

    public static function emptyTarget(): void
    {
        foreach (array_merge(['users'], RailwaySchema::TABLES, ['booking_events', 'waitlist_prediction_snapshots']) as $table) {
            if (DB::table($table)->exists()) {
                throw new RuntimeException('Refusing a nonempty target; fixtures from a previous run must not be reused.');
            }
        }
    }

    public static function directory(): string
    {
        $directory = storage_path('framework/testing/mariadb-concurrency');
        $ancestor = $directory;
        while (! is_dir($ancestor)) {
            $ancestor = dirname($ancestor);
        }
        $base = strtolower(realpath(base_path())).DIRECTORY_SEPARATOR;
        if (! str_starts_with(strtolower(realpath($ancestor)), $base)) {
            throw new RuntimeException('Barrier ancestor resolves outside the workspace.');
        }
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }
        $resolved = realpath($directory);
        if (! $resolved || ! str_starts_with(strtolower($resolved), strtolower(realpath(base_path())).DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Worker files must remain inside the project workspace.');
        }

        return $resolved;
    }

    public static function checkDirectory(string $directory): string
    {
        $resolved = realpath($directory);
        if (! $resolved || ! str_starts_with(strtolower($resolved), strtolower(self::directory()).DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Invalid worker barrier directory.');
        }

        return $resolved;
    }
}
