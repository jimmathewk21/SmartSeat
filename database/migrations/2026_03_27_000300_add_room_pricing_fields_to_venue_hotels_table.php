<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venue_hotels', function (Blueprint $table) {
            $table->string('room_section_heading')->nullable()->after('hotel_description');
            $table->text('room_section_text')->nullable()->after('room_section_heading');
            $table->decimal('gst_rate', 5, 2)->nullable()->after('room_section_text');
        });
    }

    public function down(): void
    {
        Schema::table('venue_hotels', function (Blueprint $table) {
            $table->dropColumn(['room_section_heading', 'room_section_text', 'gst_rate']);
        });
    }
};
