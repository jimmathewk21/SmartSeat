<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('speakers', function (Blueprint $table) {
            if (! Schema::hasColumn('speakers', 'speaker_role')) {
                $table->string('speaker_role', 32)->default('speaker')->after('gender');
            }
        });
    }

    public function down(): void
    {
        Schema::table('speakers', function (Blueprint $table) {
            if (Schema::hasColumn('speakers', 'speaker_role')) {
                $table->dropColumn('speaker_role');
            }
        });
    }
};
