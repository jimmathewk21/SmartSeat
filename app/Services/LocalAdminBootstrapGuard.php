<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class LocalAdminBootstrapGuard
{
    public function assertAllowed(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('This command is available only when APP_ENV is local.');
        }

        $connectionName = (string) config('database.default');
        $connectionConfig = config("database.connections.{$connectionName}");

        if (
            ! is_array($connectionConfig)
            || ($connectionConfig['driver'] ?? null) !== 'mysql'
            || ($connectionConfig['database'] ?? null) !== 'smartseat_dev'
        ) {
            throw new RuntimeException('The configured connection must target smartseat_dev using the MySQL driver.');
        }

        try {
            $activeDatabase = DB::connection($connectionName)
                ->selectOne('SELECT DATABASE() AS active_database')
                ->active_database ?? null;
        } catch (Throwable) {
            throw new RuntimeException('Could not verify the connected database; no changes were made.');
        }

        if ($activeDatabase !== 'smartseat_dev') {
            throw new RuntimeException('The active connection is not attached to smartseat_dev; no changes were made.');
        }
    }
}
