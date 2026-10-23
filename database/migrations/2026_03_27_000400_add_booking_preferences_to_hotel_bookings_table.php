<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->string('country', 120)->nullable()->after('phone');
            $table->string('occupancy', 30)->nullable()->after('country');
            $table->string('bed_size', 80)->nullable()->after('occupancy');
            $table->string('preference', 120)->nullable()->after('bed_size');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'occupancy',
                'bed_size',
                'preference',
            ]);
        });
    }
};
