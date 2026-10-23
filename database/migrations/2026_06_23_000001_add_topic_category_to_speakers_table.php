<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('speakers', function (Blueprint $table) {
            if (! Schema::hasColumn('speakers', 'topic_category')) {
                $table->string('topic_category')->nullable()->after('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('speakers', function (Blueprint $table) {
            if (Schema::hasColumn('speakers', 'topic_category')) {
                $table->dropColumn('topic_category');
            }
        });
    }
};
