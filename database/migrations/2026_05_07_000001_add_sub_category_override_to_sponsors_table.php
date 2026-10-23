<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            if (! Schema::hasColumn('sponsors', 'use_sub_category_text')) {
                $table->boolean('use_sub_category_text')->default(false)->after('sub_category');
            }

            if (! Schema::hasColumn('sponsors', 'sub_category_text')) {
                $table->string('sub_category_text')->nullable()->after('use_sub_category_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            if (Schema::hasColumn('sponsors', 'sub_category_text')) {
                $table->dropColumn('sub_category_text');
            }

            if (Schema::hasColumn('sponsors', 'use_sub_category_text')) {
                $table->dropColumn('use_sub_category_text');
            }
        });
    }
};
