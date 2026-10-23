<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'ticket_hero_label')) {
                $table->string('ticket_hero_label', 120)->nullable()->after('brochure_url');
            }

            if (! Schema::hasColumn('events', 'ticket_hero_suffix')) {
                $table->string('ticket_hero_suffix', 255)->nullable()->after('ticket_hero_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'ticket_hero_suffix')) {
                $table->dropColumn('ticket_hero_suffix');
            }

            if (Schema::hasColumn('events', 'ticket_hero_label')) {
                $table->dropColumn('ticket_hero_label');
            }
        });
    }
};
