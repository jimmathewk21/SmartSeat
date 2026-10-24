<?php

namespace App\Services\Demo;

use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/** Explicit, insert-only installer. Never called by DatabaseSeeder or the planner. */
class RailwayDemoInstaller
{
    public function install(array $plan, string $acknowledgement): array
    {
        RailwayDemoPolicy::assertConfiguration(
            (string) Config::get('app.env'), (string) Config::get('database.default'),
            (string) Config::get('database.connections.mysql.driver'),
            (string) Config::get('database.connections.mysql.database'), $acknowledgement,
        );
        (new RailwayDemoValidator)->validate($plan);
        if ($plan['timezone'] !== Config::get('app.timezone')) {
            throw new RuntimeException('Plan timezone must match the application timezone.');
        }
        $connection = DB::connection('mysql');
        RailwayDemoPolicy::assertConnectedDatabase((string) $connection->selectOne('SELECT DATABASE() AS db')->db);
        if ((int) $connection->selectOne("SELECT GET_LOCK('smartseat_demo:S27-v1', 10) AS acquired")->acquired !== 1) {
            throw new RuntimeException('Another demo installation holds the lock.');
        }
        try {
            $this->assertSchema($connection);

            // No transaction retries: credential-file creation is an external side effect.
            return $connection->transaction(function () use ($connection, $plan): array {
                $fingerprint = RailwayDemoPlan::fingerprint($plan);
                $marker = $connection->table('settings')->where('key', RailwayDemoPolicy::MARKER)->lockForUpdate()->first();
                if ($marker && $marker->deleted_at !== null) {
                    throw new RuntimeException('A retired installation marker exists; manual review is required.');
                }
                if (RailwayDemoPolicy::alreadyInstalled($marker?->value, $fingerprint)) {
                    return ['installed' => false, 'credentials' => null];
                }
                if (CarbonImmutable::now('Asia/Kolkata')->toDateString() !== $plan['as_of']) {
                    throw new RuntimeException('A new installation must use today in Asia/Kolkata as --as-of. Reruns of an installed snapshot remain no-ops.');
                }
                $this->assertUnusedNamespace($connection);
                $ids = ['roles' => $this->roles($connection)];
                [$hashes, $credentials] = $this->credentials($plan);
                foreach (array_keys(RailwayDemoPlan::COUNTS) as $table) {
                    foreach ($plan['tables'][$table] as $key => $attributes) {
                        foreach ($attributes as $column => $value) {
                            if (is_string($value) && str_starts_with($value, '@')) {
                                [$parent, $reference] = explode('|', substr($value, 1), 2);
                                $attributes[$column] = $ids[$parent][$reference] ?? throw new RuntimeException('Unresolved installation reference.');
                            }
                        }
                        if ($table === 'users') {
                            $attributes['password'] = $hashes[$key];
                        }
                        if ($table === 'role_user') {
                            $connection->table($table)->insert($attributes);
                        } else {
                            $ids[$table][$key] = $connection->table($table)->insertGetId($attributes);
                        }
                    }
                }
                foreach ($plan['inventory_pointers'] as $inventory => $booking) {
                    $changed = $connection->table('trip_seat_inventories')->where('id', $ids['trip_seat_inventories'][$inventory])
                        ->where('status', 'available')->whereNull('current_booking_id')->update([
                            'status' => 'booked', 'current_booking_id' => $ids['train_bookings'][$booking],
                            'updated_at' => $plan['tables']['train_bookings'][$booking]['confirmed_at'],
                        ]);
                    if ($changed !== 1) {
                        throw new RuntimeException('New inventory pointer could not be finalized.');
                    }
                }
                if (CarbonImmutable::now('Asia/Kolkata')->toDateString() !== $plan['as_of']) {
                    throw new RuntimeException('Reference date changed during installation. Transaction must roll back.');
                }
                $now = now()->format('Y-m-d H:i:s');
                $connection->table('settings')->insert([
                    'key' => RailwayDemoPolicy::MARKER,
                    'value' => json_encode(['version' => RailwayDemoPlan::VERSION, 'fingerprint' => $fingerprint], JSON_THROW_ON_ERROR),
                    'created_at' => $now, 'updated_at' => $now,
                ]);

                return ['installed' => true, 'credentials' => $credentials];
            }, 1);
        } finally {
            $connection->selectOne("SELECT RELEASE_LOCK('smartseat_demo:S27-v1') AS released");
        }
    }

    private function assertUnusedNamespace(Connection $connection): void
    {
        foreach (['stations' => 'code', 'trains' => 'code', 'train_bookings' => 'booking_code'] as $table => $column) {
            if ($connection->table($table)->where($column, 'like', 'S27-%')->lockForUpdate()->exists()) {
                throw new RuntimeException('Reserved namespace is already in use without a matching marker: '.$table.'. No existing records will be reused or repaired.');
            }
        }
        foreach (['users', 'participants'] as $table) {
            if ($connection->table($table)->where('email', 'like', 's27.%@example.invalid')->lockForUpdate()->exists()) {
                throw new RuntimeException('A synthetic account address already exists in '.$table.'. No account will be overwritten.');
            }
        }
    }

    private function roles(Connection $connection): array
    {
        $ids = [];
        foreach (['passenger' => 'User', 'owner' => 'Train Owner'] as $kind => $title) {
            $roles = $connection->table('roles')->where('title', $title)->orderBy('id')->lockForUpdate()->get(['id', 'title', 'deleted_at'])
                ->map(fn ($row) => (array) $row)->all();
            $permissions = [];
            if ($kind === 'owner' && count($roles) === 1) {
                $permissions = $connection->table('permission_role')->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                    ->where('permission_role.role_id', $roles[0]['id'])->whereNull('permissions.deleted_at')
                    ->orderBy('permissions.id')->lockForUpdate()->pluck('permissions.title')->all();
            }
            $ids[$kind] = RailwayDemoPolicy::assertRole($kind, $roles, $permissions);
        }

        return $ids;
    }

    private function credentials(array $plan): array
    {
        $directory = storage_path('app/private/smartseat-demo/S27-v1');
        $path = $directory.'/credentials.json';
        if (file_exists($path)) {
            throw new RuntimeException('A private credential reference already exists. Review a possible interrupted installation; passwords will not be overwritten.');
        }
        if (! is_dir($directory) && ! mkdir($directory, 0700, true)) {
            throw new RuntimeException('Cannot create private credential directory.');
        }
        $accounts = [];
        $hashes = [];
        foreach ($plan['tables']['users'] as $email => $user) {
            $password = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
            $hashes[$email] = Hash::make($password);
            $owner = str_starts_with($email, 's27.owner.');
            $accounts[] = [
                'name' => $user['name'], 'email' => $email, 'password' => $password,
                'login_path' => $owner ? '/internal/rail-owner/entry' : '/trains/sign-in',
                'owner_approval' => $owner ? 'pending — administrator review required' : null,
            ];
        }
        $payload = json_encode([
            'notice' => 'DEVELOPMENT-ONLY SYNTHETIC ACCOUNTS. Never publish this file.',
            'database' => RailwayDemoPolicy::DATABASE, 'fingerprint' => RailwayDemoPlan::fingerprint($plan), 'accounts' => $accounts,
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $file = fopen($path, 'x');
        if ($file === false) {
            throw new RuntimeException('Cannot exclusively create the private credential reference.');
        }
        try {
            if (! chmod($path, 0600) || fwrite($file, $payload) !== strlen($payload) || ! fflush($file)) {
                throw new RuntimeException('Private credential write failed. Review the retained file before retrying.');
            }
        } finally {
            fclose($file);
        }

        // On any later SQL failure this reference is retained for manual recovery, never rotated or deleted.
        return [$hashes, $path];
    }

    private function assertSchema(Connection $connection): void
    {
        $version = (string) $connection->selectOne('SELECT VERSION() AS version')->version;
        $maria = stripos($version, 'mariadb') !== false;
        preg_match($maria ? '/(\d+\.\d+\.\d+)-MariaDB/i' : '/(\d+\.\d+\.\d+)/', $version, $match);
        if (! isset($match[1]) || version_compare($match[1], $maria ? '10.2.1' : '8.0.16', '<')) {
            throw new RuntimeException('Requires MySQL/MariaDB with enforced CHECK constraints.');
        }
        if ((int) $connection->selectOne('SELECT @@foreign_key_checks AS enabled')->enabled !== 1) {
            throw new RuntimeException('Foreign-key enforcement must be enabled.');
        }
        if ($maria && (int) $connection->selectOne('SELECT @@check_constraint_checks AS enabled')->enabled !== 1) {
            throw new RuntimeException('CHECK enforcement must be enabled.');
        }
        if (! str_contains((string) $connection->selectOne('SELECT @@sql_mode AS mode')->mode, 'STRICT_')) {
            throw new RuntimeException('Strict SQL mode is required.');
        }
        $schema = $connection->getSchemaBuilder();
        foreach (RailwayDemoSchema::COLUMNS as $table => $columns) {
            $required = explode(' ', $columns);
            if (! in_array($table, ['role_user', 'permission_role'], true)) {
                $required[] = 'id';
            }
            if (! $schema->hasTable($table) || ! $schema->hasColumns($table, $required)) {
                throw new RuntimeException('Required migrated schema is missing: '.$table.'. No migration will be run.');
            }
            $engine = $connection->table('information_schema.tables')->where('table_schema', RailwayDemoPolicy::DATABASE)->where('table_name', $table)->value('engine');
            if (strcasecmp((string) $engine, 'InnoDB') !== 0) {
                throw new RuntimeException('All dataset dependencies must already use InnoDB: '.$table);
            }
            foreach ($schema->getForeignKeys($table) as $foreignKey) {
                if ($foreignKey['foreign_schema'] !== RailwayDemoPolicy::DATABASE) {
                    throw new RuntimeException('Cross-database foreign keys are not permitted: '.$table);
                }
            }
        }
        if ($connection->table('information_schema.triggers')->where('trigger_schema', RailwayDemoPolicy::DATABASE)
            ->whereIn('event_object_table', [...array_keys(RailwayDemoPlan::COUNTS), 'settings'])->exists()) {
            throw new RuntimeException('Custom database triggers on dataset tables require a separate review. No triggers will be changed.');
        }
        foreach (RailwayDemoSchema::UNIQUE_KEYS as $table => $keys) {
            $indexes = $schema->getIndexes($table);
            foreach ($keys as $columns) {
                if (! array_filter($indexes, fn ($index) => $index['unique'] && $index['columns'] === $columns)) {
                    throw new RuntimeException('Required unique key missing: '.$table.'('.implode(',', $columns).')');
                }
            }
        }
        foreach (RailwayDemoSchema::FOREIGN_KEYS as $table => $keys) {
            $foreignKeys = $schema->getForeignKeys($table);
            foreach ($keys as [$columns, $parent, $parentColumns]) {
                if (! array_filter($foreignKeys, fn ($fk) => $fk['columns'] === explode(' ', $columns) && $fk['foreign_table'] === $parent && $fk['foreign_columns'] === explode(' ', $parentColumns))) {
                    throw new RuntimeException('Required foreign key missing: '.$table.'('.$columns.')');
                }
            }
        }
        $column = $connection->table('information_schema.columns')->where('table_schema', RailwayDemoPolicy::DATABASE)
            ->where('table_name', 'train_booking_seats')->where('column_name', 'active_trip_seat_inventory_id')
            ->selectRaw('EXTRA AS extra, GENERATION_EXPRESSION AS generation_expression, DATA_TYPE AS data_type, COLUMN_TYPE AS column_type, IS_NULLABLE AS is_nullable')->first();
        if (! $column || $column->data_type !== 'bigint' || stripos($column->column_type, 'unsigned') === false || $column->is_nullable !== 'YES'
            || stripos($column->extra, 'STORED') === false || RailwayDemoSchema::normalizeExpression($column->generation_expression) !== RailwayDemoSchema::normalizeExpression('CASE WHEN released_at IS NULL THEN trip_seat_inventory_id ELSE NULL END')) {
            throw new RuntimeException('Active seat-assignment generated column is missing or incompatible.');
        }
        $check = $connection->table('information_schema.check_constraints as c')->join('information_schema.table_constraints as t', function ($join) {
            $join->on('c.constraint_schema', '=', 't.constraint_schema')->on('c.constraint_name', '=', 't.constraint_name');
        })->where('t.table_schema', RailwayDemoPolicy::DATABASE)->where('t.table_name', 'trip_seat_inventories')
            ->where('t.constraint_name', 'trip_inventory_status_pointer_chk')->value('c.check_clause');
        $expected = "(status = 'available' AND current_booking_id IS NULL) OR (status = 'booked' AND current_booking_id IS NOT NULL)";
        if (! is_string($check) || RailwayDemoSchema::normalizeExpression($check) !== RailwayDemoSchema::normalizeExpression($expected)) {
            throw new RuntimeException('Inventory state CHECK is missing or incompatible.');
        }
        if (! $maria) {
            $enforced = $connection->table('information_schema.table_constraints')->where('constraint_schema', RailwayDemoPolicy::DATABASE)
                ->where('table_name', 'trip_seat_inventories')->where('constraint_name', 'trip_inventory_status_pointer_chk')->value('enforced');
            if ($enforced !== 'YES') {
                throw new RuntimeException('Inventory CHECK is not enforced.');
            }
        }
    }
}
