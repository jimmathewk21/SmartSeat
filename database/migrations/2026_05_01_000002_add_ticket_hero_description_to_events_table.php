<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'ticket_hero_description')) {
                $table->string('ticket_hero_description', 500)->nullable()->after('ticket_hero_suffix');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'ticket_hero_description')) {
                $table->dropColumn('ticket_hero_description');
            }
        });
    }
};
