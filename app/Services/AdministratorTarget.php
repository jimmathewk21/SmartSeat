<?php

namespace App\Services;

use App\Support\AdministratorDatabaseName;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class AdministratorTarget
{
    /**
     * Verify an explicitly approved local database without modifying it.
     * Call only from the direct CLI invocation: php artisan app:create-administrator.
     * This service is intentionally not invoked during application boot or seeding.
     */
    public function verify(string $expectedDatabase): void
    {
        if (Config::get('app.env') !== 'local') {
            throw new RuntimeException('Administrator bootstrap requires the local environment.');
        }

        $suffixes = Config::get('smartseat.administrator_bootstrap.permitted_database_suffixes', []);

        if (! is_array($suffixes) || ! AdministratorDatabaseName::allows($expectedDatabase, $suffixes)) {
            throw new RuntimeException('The administrator database name is not explicitly approved.');
        }

        $name = Config::get('database.default');
        $connections = Config::get('database.connections', []);

        if (! is_string($name) || $name === '' || ! is_array($connections)) {
            throw new RuntimeException('A configured database connection is required.');
        }

        $this->verifyConfiguration($connections[$name] ?? null, $expectedDatabase);

        // Fail closed outside a direct invocation of the bootstrap command.
        if (! app()->runningInConsole() || ($_SERVER['argv'][1] ?? null) !== 'app:create-administrator') {
            throw new RuntimeException('Database verification requires the explicit administrator bootstrap command.');
        }

        try {
            $connection = DB::connection($name);

            // Also check the effective configuration in case a connection was already resolved.
            $this->verifyConfiguration($connection->getConfig(), $expectedDatabase);

            $result = $connection->selectOne('SELECT DATABASE() AS database_name', [], false);

            if (($result->database_name ?? null) !== $expectedDatabase) {
                throw new RuntimeException('The effective database does not match the approved database.');
            }
        } catch (Throwable $exception) {
            throw new RuntimeException('Administrator database verification failed.', 0, $exception);
        }
    }

    private function verifyConfiguration(mixed $configuration, string $expectedDatabase): void
    {
        if (! is_array($configuration)
            || ($configuration['database'] ?? null) !== $expectedDatabase
            || ! in_array($configuration['driver'] ?? null, ['mysql', 'mariadb'], true)
            || ! in_array($configuration['host'] ?? null, ['localhost', '127.0.0.1', '::1'], true)
            || ! in_array($configuration['url'] ?? null, [null, ''], true)
            || ! in_array($configuration['unix_socket'] ?? null, [null, ''], true)
            || array_key_exists('read', $configuration)
            || array_key_exists('write', $configuration)) {
            throw new RuntimeException('Administrator bootstrap requires a matching local MySQL/MariaDB connection without URLs, sockets or read/write splits.');
        }
    }
}
