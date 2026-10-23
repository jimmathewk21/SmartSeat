<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('world_events', function (Blueprint $table) {
            if (! Schema::hasColumn('world_events', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('stars');
            }
        });
    }

    public function down(): void
    {
        Schema::table('world_events', function (Blueprint $table) {
            if (Schema::hasColumn('world_events', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
