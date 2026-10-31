<?php

use App\Support\RailwaySchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (RailwaySchema::statements(DB::connection()->getDriverName()) as $statement) {
            DB::statement($statement);
        }
    }

    public function down(): void
    {
        foreach (array_reverse(RailwaySchema::TABLES) as $table) {
            Schema::dropIfExists($table);
        }
    }
};
