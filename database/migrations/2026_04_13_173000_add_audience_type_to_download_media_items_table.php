<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('download_media_items', function (Blueprint $table) {
            $table->string('audience_type', 30)->default('delegate')->after('section_title')->index();
        });
    }

    public function down(): void
    {
        Schema::table('download_media_items', function (Blueprint $table) {
            $table->dropColumn('audience_type');
        });
    }
};
