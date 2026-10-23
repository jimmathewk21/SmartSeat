<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsor_inquiries', function (Blueprint $table) {
            $table->string('rollup_standees_option', 40)->nullable()->after('benefit_rollup_standees');
        });
    }

    public function down(): void
    {
        Schema::table('sponsor_inquiries', function (Blueprint $table) {
            $table->dropColumn('rollup_standees_option');
        });
    }
};
