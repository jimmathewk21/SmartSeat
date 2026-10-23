<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE settings ALTER COLUMN value TYPE TEXT');
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement('ALTER TABLE settings ALTER COLUMN value TEXT');
            return;
        }

        DB::statement('ALTER TABLE settings MODIFY value TEXT');
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE settings ALTER COLUMN value TYPE VARCHAR(255)');
            return;
        }

        if ($driver === 'sqlsrv') {
            DB::statement('ALTER TABLE settings ALTER COLUMN value VARCHAR(255)');
            return;
        }

        DB::statement('ALTER TABLE settings MODIFY value VARCHAR(255)');
    }
};
