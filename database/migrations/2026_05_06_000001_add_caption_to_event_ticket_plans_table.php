<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_ticket_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('event_ticket_plans', 'caption')) {
                $table->string('caption')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_ticket_plans', function (Blueprint $table) {
            if (Schema::hasColumn('event_ticket_plans', 'caption')) {
                $table->dropColumn('caption');
            }
        });
    }
};
