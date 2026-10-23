<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    private array $trainTables = [
        'stations',
        'trains',
        'train_route_stops',
        'train_coaches',
        'train_seats',
        'train_trips',
        'trip_seat_inventories',
        'train_bookings',
        'train_booking_passengers',
        'train_booking_seats',
        'train_waitlist_entries',
        'train_waitlist_passengers',
    ];

    public function up(): void
    {
        $vendor = $this->assertSupportedDatabase();
        $this->assertRequiredTablesExist();
        $this->validateExistingRows();

        $migrationState = $this->hardeningState($vendor);
        if ($migrationState['complete']) {
            return;
        }

        if ($migrationState['mismatches'] !== [] && $migrationState['objects'] !== []) {
            throw new RuntimeException(
                'Train hardening migration 000004 found existing objects that do not match the required definitions: '
                . implode('; ', $migrationState['mismatches'])
                . '. Inspect and repair the schema manually; no existing objects or data were changed.'
            );
        }

        if ($migrationState['objects'] !== []) {
            throw new RuntimeException(
                'Train hardening migration 000004 appears partially applied. Found: '
                . implode(', ', $migrationState['objects'])
                . ($migrationState['mismatches'] === [] ? '' : '. Details: ' . implode('; ', $migrationState['mismatches']))
                . '. Follow the recovery procedure in docs/train-booking-system.md before retrying; no rows were deleted.'
            );
        }

        foreach ($this->trainTables as $table) {
            DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
        }

        DB::statement(
            "ALTER TABLE `trip_seat_inventories` "
            . 'ADD CONSTRAINT `trip_inventory_status_pointer_chk` '
            . "CHECK ((`status` = 'available' AND `current_booking_id` IS NULL) "
            . "OR (`status` = 'booked' AND `current_booking_id` IS NOT NULL))"
        );

        Schema::table('train_booking_passengers', function (Blueprint $table) {
            $table->unique(['id', 'train_booking_id'], 'train_booking_passenger_booking_uq');
        });

        Schema::table('trip_seat_inventories', function (Blueprint $table) {
            $table->unique(['id', 'train_trip_id'], 'trip_inventory_id_trip_uq');
        });

        Schema::table('train_booking_seats', function (Blueprint $table) {
            $table->unsignedBigInteger('active_trip_seat_inventory_id')
                ->nullable()
                ->storedAs('CASE WHEN `released_at` IS NULL THEN `trip_seat_inventory_id` ELSE NULL END');
            $table->unique('active_trip_seat_inventory_id', 'train_booking_active_inventory_uq');
            $table->foreign(['trip_seat_inventory_id', 'train_trip_id'], 'train_booking_seat_inventory_trip_fk')
                ->references(['id', 'train_trip_id'])->on('trip_seat_inventories');
            $table->foreign(['train_booking_passenger_id', 'train_booking_id'], 'train_booking_seat_passenger_booking_fk')
                ->references(['id', 'train_booking_id'])->on('train_booking_passengers');
        });

        $this->assertHardeningComplete($vendor);
    }

    public function down(): void
    {
        $vendor = $this->assertSupportedDatabase(requireCheckEnforcement: false);

        Schema::table('train_booking_seats', function (Blueprint $table) {
            $table->dropForeign('train_booking_seat_inventory_trip_fk');
            $table->dropForeign('train_booking_seat_passenger_booking_fk');
            $table->dropUnique('train_booking_active_inventory_uq');
            $table->dropColumn('active_trip_seat_inventory_id');
        });

        Schema::table('trip_seat_inventories', function (Blueprint $table) {
            $table->dropUnique('trip_inventory_id_trip_uq');
        });

        Schema::table('train_booking_passengers', function (Blueprint $table) {
            $table->dropUnique('train_booking_passenger_booking_uq');
        });

        $dropCheck = $vendor === 'mariadb' ? 'DROP CONSTRAINT' : 'DROP CHECK';
        DB::statement("ALTER TABLE `trip_seat_inventories` {$dropCheck} `trip_inventory_status_pointer_chk`");
    }

    private function assertSupportedDatabase(bool $requireCheckEnforcement = true): string
    {
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql') {
            throw new RuntimeException(
                'Train hardening migration 000004 requires MySQL or MariaDB with InnoDB; '
                . 'the configured database driver is [' . $connection->getDriverName() . ']. No schema changes were made.'
            );
        }

        $server = DB::selectOne('SELECT VERSION() AS version, @@version_comment AS version_comment');
        $versionString = (string) $server->version;
        $versionComment = (string) $server->version_comment;
        $serverSignature = strtolower($versionString . ' ' . $versionComment);
        $vendor = str_contains($serverSignature, 'mariadb') ? 'mariadb' : 'mysql';

        if ($vendor === 'mysql' && (! str_contains($serverSignature, 'mysql') || str_contains($serverSignature, 'percona'))) {
            throw new RuntimeException(
                'Train hardening migration 000004 supports Oracle MySQL and MariaDB only. '
                . 'Detected server [' . $versionString . ' / ' . $versionComment . ']. No schema changes were made.'
            );
        }

        preg_match_all('/\d+\.\d+\.\d+/', $versionString, $matches);
        $versionParts = $matches[0] ?? [];
        $normalizedVersion = $versionParts === []
            ? null
            : ($vendor === 'mariadb' ? end($versionParts) : $versionParts[0]);
        $minimumVersion = $vendor === 'mariadb' ? '10.2.1' : '8.0.16';

        if ($normalizedVersion === null || version_compare($normalizedVersion, $minimumVersion, '<')) {
            throw new RuntimeException(
                'Train hardening migration 000004 requires '
                . ($vendor === 'mariadb' ? 'MariaDB 10.2.1+' : 'Oracle MySQL 8.0.16+')
                . ' so the inventory CHECK constraint is enforced. Detected [' . $versionString . ']. '
                . 'Upgrade the server or use a reviewed trigger-based fallback; no schema changes were made.'
            );
        }

        $innodb = DB::table('information_schema.engines')->where('engine', 'InnoDB')->value('support');
        if (! in_array(strtoupper((string) $innodb), ['YES', 'DEFAULT'], true)) {
            throw new RuntimeException('InnoDB is not available on the detected database server. No schema changes were made.');
        }

        if ($vendor === 'mariadb' && $requireCheckEnforcement) {
            $checksEnabled = DB::selectOne('SELECT @@SESSION.check_constraint_checks AS enabled')->enabled ?? null;
            if ((int) $checksEnabled !== 1) {
                throw new RuntimeException(
                    'MariaDB check_constraint_checks is disabled for this session. Enable CHECK enforcement and retry; '
                    . 'no schema changes were made.'
                );
            }
        }

        return $vendor;
    }

    private function assertRequiredTablesExist(): void
    {
        $missing = array_values(array_filter($this->trainTables, fn (string $table) => ! Schema::hasTable($table)));
        if ($missing !== []) {
            throw new RuntimeException(
                'Train hardening migration 000004 requires migrations 000001–000003 first. Missing tables: '
                . implode(', ', $missing) . '. No schema changes were made.'
            );
        }
    }

    private function validateExistingRows(): void
    {
        $invalidInventory = DB::table('trip_seat_inventories')
            ->where(function ($query) {
                $query->whereNotIn('status', ['available', 'booked'])
                    ->orWhere(function ($query) {
                        $query->where('status', 'available')->whereNotNull('current_booking_id');
                    })
                    ->orWhere(function ($query) {
                        $query->where('status', 'booked')->whereNull('current_booking_id');
                    });
            })
            ->orderBy('id')
            ->limit(10)
            ->pluck('id');

        if ($invalidInventory->isNotEmpty()) {
            throw new RuntimeException(
                'Train hardening migration 000004 found invalid inventory status/current-booking pairs '
                . '(inventory IDs: ' . $invalidInventory->implode(', ') . '). Correct these rows so only '
                . 'available/null and booked/non-null pairs remain, then retry. No schema changes were made.'
            );
        }

        $invalidAssignments = DB::table('train_booking_seats as assignment')
            ->leftJoin('train_bookings as booking', 'booking.id', '=', 'assignment.train_booking_id')
            ->leftJoin('trip_seat_inventories as inventory', 'inventory.id', '=', 'assignment.trip_seat_inventory_id')
            ->where(function ($query) {
                $query->whereNull('booking.id')
                    ->orWhereNull('inventory.id')
                    ->orWhereColumn('assignment.train_trip_id', '<>', 'booking.train_trip_id')
                    ->orWhereColumn('inventory.train_trip_id', '<>', 'assignment.train_trip_id');
            })
            ->orderBy('assignment.id')
            ->limit(10)
            ->pluck('assignment.id');

        if ($invalidAssignments->isNotEmpty()) {
            throw new RuntimeException(
                'Train hardening migration 000004 found booking-seat assignments with a missing booking/inventory '
                . 'or a mismatched trip (assignment IDs: ' . $invalidAssignments->implode(', ') . '). Correct those '
                . 'references before retrying. No schema changes were made.'
            );
        }

        $invalidPassengers = DB::table('train_booking_seats as assignment')
            ->leftJoin('train_booking_passengers as passenger', 'passenger.id', '=', 'assignment.train_booking_passenger_id')
            ->whereNotNull('assignment.train_booking_passenger_id')
            ->where(function ($query) {
                $query->whereNull('passenger.id')
                    ->orWhereColumn('passenger.train_booking_id', '<>', 'assignment.train_booking_id');
            })
            ->orderBy('assignment.id')
            ->limit(10)
            ->pluck('assignment.id');

        if ($invalidPassengers->isNotEmpty()) {
            throw new RuntimeException(
                'Train hardening migration 000004 found booking-seat assignments with a missing passenger or a '
                . 'passenger from another booking (assignment IDs: ' . $invalidPassengers->implode(', ') . '). '
                . 'Correct those references before retrying. No schema changes were made.'
            );
        }

        $duplicateActiveAssignments = DB::table('train_booking_seats')
            ->select('trip_seat_inventory_id')
            ->whereNull('released_at')
            ->groupBy('trip_seat_inventory_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('trip_seat_inventory_id')
            ->limit(10)
            ->pluck('trip_seat_inventory_id');

        if ($duplicateActiveAssignments->isNotEmpty()) {
            throw new RuntimeException(
                'Train hardening migration 000004 found inventory rows with more than one unreleased assignment '
                . '(inventory IDs: ' . $duplicateActiveAssignments->implode(', ') . '). Resolve the duplicate '
                . 'assignment history before retrying. No schema changes were made.'
            );
        }

        $invalidCurrentBookings = DB::table('trip_seat_inventories as inventory')
            ->leftJoin('train_bookings as booking', 'booking.id', '=', 'inventory.current_booking_id')
            ->whereNotNull('inventory.current_booking_id')
            ->where(function ($query) {
                $query->whereNull('booking.id')
                    ->orWhereColumn('booking.train_trip_id', '<>', 'inventory.train_trip_id');
            })
            ->orderBy('inventory.id')
            ->limit(10)
            ->pluck('inventory.id');

        if ($invalidCurrentBookings->isNotEmpty()) {
            throw new RuntimeException(
                'Train hardening migration 000004 found inventory rows whose current booking is missing or belongs '
                . 'to another trip (inventory IDs: ' . $invalidCurrentBookings->implode(', ') . '). Correct those '
                . 'references before retrying. No schema changes were made.'
            );
        }

        $bookedWithoutActiveAssignment = DB::table('trip_seat_inventories as inventory')
            ->leftJoin('train_booking_seats as assignment', function ($join) {
                $join->on('assignment.trip_seat_inventory_id', '=', 'inventory.id')
                    ->on('assignment.train_booking_id', '=', 'inventory.current_booking_id')
                    ->whereNull('assignment.released_at');
            })
            ->where('inventory.status', 'booked')
            ->whereNull('assignment.id')
            ->orderBy('inventory.id')->limit(10)->pluck('inventory.id');

        if ($bookedWithoutActiveAssignment->isNotEmpty()) {
            throw new RuntimeException(
                'Train hardening migration 000004 found booked inventory without a matching unreleased assignment '
                . '(inventory IDs: ' . $bookedWithoutActiveAssignment->implode(', ') . '). Review the booking history '
                . 'and repair the pointer or assignment manually; do not delete history. No schema changes were made.'
            );
        }

        $activeAssignmentMismatches = DB::table('train_booking_seats as assignment')
            ->join('trip_seat_inventories as inventory', 'inventory.id', '=', 'assignment.trip_seat_inventory_id')
            ->whereNull('assignment.released_at')
            ->where(function ($query) {
                $query->where('inventory.status', '<>', 'booked')
                    ->orWhereNull('inventory.current_booking_id')
                    ->orWhereColumn('inventory.current_booking_id', '<>', 'assignment.train_booking_id');
            })
            ->orderBy('assignment.id')->limit(10)
            ->get(['assignment.id as assignment_id', 'inventory.id as inventory_id']);

        if ($activeAssignmentMismatches->isNotEmpty()) {
            $ids = $activeAssignmentMismatches->map(fn ($row) => $row->assignment_id . '/' . $row->inventory_id);
            throw new RuntimeException(
                'Train hardening migration 000004 found unreleased assignments inconsistent with inventory status/current booking '
                . '(assignment/inventory IDs: ' . $ids->implode(', ') . '). Review booking history and manually reconcile '
                . 'the active assignment and inventory pointer without deleting history. No schema changes were made.'
            );
        }

        $activeAssignmentsForNonConfirmedBookings = DB::table('train_booking_seats as assignment')
            ->join('train_bookings as booking', 'booking.id', '=', 'assignment.train_booking_id')
            ->whereNull('assignment.released_at')
            ->where('booking.status', '<>', 'confirmed')
            ->orderBy('assignment.id')->limit(10)->pluck('assignment.id');

        if ($activeAssignmentsForNonConfirmedBookings->isNotEmpty()) {
            throw new RuntimeException(
                'Train hardening migration 000004 found unreleased assignments for non-confirmed bookings '
                . '(assignment IDs: ' . $activeAssignmentsForNonConfirmedBookings->implode(', ') . '). Review booking '
                . 'and cancellation history and reconcile manually without deleting records. No schema changes were made.'
            );
        }
    }

    private function hardeningState(string $vendor): array
    {
        $objects = [];
        $mismatches = [];
        $expectedIndexes = [
            ['train_booking_passengers', 'train_booking_passenger_booking_uq', ['id', 'train_booking_id']],
            ['trip_seat_inventories', 'trip_inventory_id_trip_uq', ['id', 'train_trip_id']],
            ['train_booking_seats', 'train_booking_active_inventory_uq', ['active_trip_seat_inventory_id']],
        ];
        foreach ($expectedIndexes as [$table, $name, $columns]) {
            $actual = DB::table('information_schema.statistics')->whereRaw('table_schema = DATABASE()')
                ->where('table_name', $table)->where('index_name', $name)->orderBy('seq_in_index')
                ->get(['column_name', 'non_unique'])->map(fn ($row) => [$row->column_name, (int) $row->non_unique])->all();
            if ($actual !== []) {
                $objects[] = $table . '.' . $name;
                $expected = array_map(fn ($column) => [$column, 0], $columns);
                if ($actual !== $expected) $mismatches[] = $table . '.' . $name . ' has incorrect uniqueness or column order';
            }
        }

        $check = DB::table('information_schema.table_constraints')->whereRaw('constraint_schema = DATABASE()')
            ->where('table_name', 'trip_seat_inventories')->where('constraint_name', 'trip_inventory_status_pointer_chk')->first(['constraint_type']);
        if ($check) {
            $objects[] = 'trip_seat_inventories.trip_inventory_status_pointer_chk';
            $clause = DB::table('information_schema.check_constraints')->whereRaw('constraint_schema = DATABASE()')
                ->where('constraint_name', 'trip_inventory_status_pointer_chk')->value('check_clause');
            if (strtoupper((string) $check->constraint_type) !== 'CHECK' || $this->normalizeSql($clause) !== $this->normalizeSql("((status = 'available' AND current_booking_id IS NULL) OR (status = 'booked' AND current_booking_id IS NOT NULL))")) {
                $mismatches[] = 'trip_inventory_status_pointer_chk has the wrong type or expression';
            }
            if ($vendor === 'mysql') {
                $enforced = DB::table('information_schema.table_constraints')->whereRaw('constraint_schema = DATABASE()')
                    ->where('table_name', 'trip_seat_inventories')->where('constraint_name', 'trip_inventory_status_pointer_chk')->value('enforced');
                if (strtoupper((string) $enforced) !== 'YES') $mismatches[] = 'trip_inventory_status_pointer_chk is not enforced';
            }
        }

        $this->inspectForeignKey($objects, $mismatches, 'train_booking_seats', 'train_booking_seat_inventory_trip_fk', ['trip_seat_inventory_id', 'train_trip_id'], 'trip_seat_inventories', ['id', 'train_trip_id']);
        $this->inspectForeignKey($objects, $mismatches, 'train_booking_seats', 'train_booking_seat_passenger_booking_fk', ['train_booking_passenger_id', 'train_booking_id'], 'train_booking_passengers', ['id', 'train_booking_id']);

        $column = DB::table('information_schema.columns')->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'train_booking_seats')->where('column_name', 'active_trip_seat_inventory_id')->first(['generation_expression', 'extra', 'column_type', 'is_nullable']);
        if ($column) {
            $objects[] = 'train_booking_seats.active_trip_seat_inventory_id';
            $expectedExpression = 'CASE WHEN released_at IS NULL THEN trip_seat_inventory_id ELSE NULL END';
            if (! str_contains(strtolower((string) $column->extra), 'generated') || ! $this->isExpectedUnsignedBigintType($column->column_type) || strtoupper((string) $column->is_nullable) !== 'YES' || $this->normalizeSql($column->generation_expression) !== $this->normalizeSql($expectedExpression)) {
                $mismatches[] = 'active_trip_seat_inventory_id has the wrong generated expression or column properties';
            }
        }

        $engineRows = DB::table('information_schema.tables')
            ->select('table_name', 'engine')
            ->whereRaw('table_schema = DATABASE()')
            ->whereIn('table_name', $this->trainTables)
            ->get();
        $innoDbTables = $engineRows->filter(fn ($row) => strtoupper((string) $row->engine) === 'INNODB')->count();
        $enginesComplete = $innoDbTables === count($this->trainTables);
        if (! $enginesComplete) $mismatches[] = 'one or more train-domain tables are not InnoDB';

        $expectedCount = count($expectedIndexes) + 4;
        $complete = count($objects) === $expectedCount && $mismatches === [] && $enginesComplete;

        return [
            'complete' => $complete,
            'objects' => $objects,
            'mismatches' => $mismatches,
        ];
    }

    private function inspectForeignKey(array &$objects, array &$mismatches, string $table, string $name, array $columns, string $parentTable, array $parentColumns): void
    {
        $namedConstraint = DB::table('information_schema.table_constraints')->whereRaw('constraint_schema = DATABASE()')
            ->where('table_name', $table)->where('constraint_name', $name)->first(['constraint_type']);
        $actual = DB::table('information_schema.key_column_usage')->whereRaw('constraint_schema = DATABASE()')
            ->where('table_name', $table)->where('constraint_name', $name)->whereNotNull('referenced_table_name')
            ->orderBy('ordinal_position')->get(['column_name', 'referenced_table_name', 'referenced_column_name'])
            ->map(fn ($row) => [$row->column_name, $row->referenced_table_name, $row->referenced_column_name])->all();
        if (!$namedConstraint && $actual === []) return;
        $objects[] = $table . '.' . $name;
        $expected = [];
        foreach ($columns as $index => $column) $expected[] = [$column, $parentTable, $parentColumns[$index]];
        $behavior = DB::table('information_schema.referential_constraints')->whereRaw('constraint_schema = DATABASE()')
            ->where('table_name', $table)->where('constraint_name', $name)->first(['update_rule', 'delete_rule']);
        if (strtoupper((string) ($namedConstraint->constraint_type ?? '')) !== 'FOREIGN KEY' || $actual !== $expected || !$behavior || strtoupper($behavior->update_rule) !== 'RESTRICT' || strtoupper($behavior->delete_rule) !== 'RESTRICT') {
            $mismatches[] = $table . '.' . $name . ' has incorrect columns, references, or referential actions';
        }
    }

    private function normalizeSql(?string $sql): string
    {
        return preg_replace('/[\s`()]+/', '', strtolower((string) $sql)) ?? '';
    }

    private function isExpectedUnsignedBigintType(?string $columnType): bool
    {
        $normalizedType = preg_replace('/\(\d+\)/', '', strtolower(trim((string) $columnType)));

        return $normalizedType === 'bigint unsigned';
    }

    private function assertHardeningComplete(string $vendor): void
    {
        $state = $this->hardeningState($vendor);
        if (! $state['complete']) {
            throw new RuntimeException(
                'Train hardening migration 000004 did not install all expected indexes and constraints. '
                . 'Review the partially applied schema and follow docs/train-booking-system.md before retrying.'
            );
        }

        if ($vendor === 'mariadb') {
            $checksEnabled = DB::selectOne('SELECT @@SESSION.check_constraint_checks AS enabled')->enabled ?? null;
            if ((int) $checksEnabled !== 1) {
                throw new RuntimeException('MariaDB CHECK enforcement was disabled while migration 000004 was running.');
            }
        }
    }
};
