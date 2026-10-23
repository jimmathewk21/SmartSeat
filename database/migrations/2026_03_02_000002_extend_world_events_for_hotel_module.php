<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('world_events', function (Blueprint $table) {
            $table->boolean('is_hotel')->default(false)->after('stars');
            $table->string('hotel_image')->nullable()->after('is_hotel');
            $table->json('hotel_gallery')->nullable()->after('hotel_image');
            $table->text('hotel_address')->nullable()->after('hotel_gallery');
            $table->text('hotel_description')->nullable()->after('hotel_address');
            $table->json('hotel_rooms')->nullable()->after('hotel_description');
            $table->date('booking_available_from')->nullable()->after('hotel_rooms');
            $table->date('booking_available_to')->nullable()->after('booking_available_from');
            $table->string('direction_from')->nullable()->after('booking_available_to');
            $table->string('direction_to')->nullable()->after('direction_from');
        });
    }

    public function down(): void
    {
        Schema::table('world_events', function (Blueprint $table) {
            $table->dropColumn([
                'is_hotel',
                'hotel_image',
                'hotel_gallery',
                'hotel_address',
                'hotel_description',
                'hotel_rooms',
                'booking_available_from',
                'booking_available_to',
                'direction_from',
                'direction_to',
            ]);
        });
    }
};

