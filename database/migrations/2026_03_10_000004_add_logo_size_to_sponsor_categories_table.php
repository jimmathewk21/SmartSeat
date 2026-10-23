<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsor_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('sponsor_categories', 'logo_size')) {
                $table->string('logo_size', 10)->default('md')->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sponsor_categories', function (Blueprint $table) {
            if (Schema::hasColumn('sponsor_categories', 'logo_size')) {
                $table->dropColumn('logo_size');
            }
        });
    }
};
