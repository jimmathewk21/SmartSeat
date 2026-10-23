<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('description');
            $table->string('gallery_url')->nullable()->after('image_url');
            $table->string('brochure_url')->nullable()->after('gallery_url');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'gallery_url', 'brochure_url']);
        });
    }
};
