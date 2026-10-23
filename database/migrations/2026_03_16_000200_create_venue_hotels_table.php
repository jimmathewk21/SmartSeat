<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city', 120)->nullable();
            $table->string('country', 120)->nullable();
            $table->unsignedTinyInteger('stars')->default(0);
            $table->string('hotel_image')->nullable();
            $table->json('hotel_gallery')->nullable();
            $table->text('hotel_address')->nullable();
            $table->text('hotel_description')->nullable();
            $table->json('hotel_rooms')->nullable();
            $table->date('booking_available_from')->nullable();
            $table->date('booking_available_to')->nullable();
            $table->string('direction_from')->nullable();
            $table->string('direction_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('source_world_event_id')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('venue_hotel_id')->nullable()->after('world_event_id');
            $table->index('venue_hotel_id');
        });

        if (Schema::hasTable('world_events')) {
            $hotels = DB::table('world_events')
                ->where('is_hotel', true)
                ->get();

            foreach ($hotels as $hotel) {
                $venueHotelId = DB::table('venue_hotels')->insertGetId([
                    'name' => $hotel->hotel ?: $hotel->name,
                    'city' => $hotel->city,
                    'country' => $hotel->country,
                    'stars' => (int) ($hotel->stars ?? 0),
                    'hotel_image' => $hotel->hotel_image,
                    'hotel_gallery' => $hotel->hotel_gallery,
                    'hotel_address' => $hotel->hotel_address,
                    'hotel_description' => $hotel->hotel_description,
                    'hotel_rooms' => $hotel->hotel_rooms,
                    'booking_available_from' => $hotel->booking_available_from,
                    'booking_available_to' => $hotel->booking_available_to,
                    'direction_from' => $hotel->direction_from,
                    'direction_to' => $hotel->direction_to,
                    'is_active' => true,
                    'sort_order' => 0,
                    'source_world_event_id' => $hotel->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('hotel_bookings')
                    ->where('world_event_id', $hotel->id)
                    ->update(['venue_hotel_id' => $venueHotelId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->dropIndex(['venue_hotel_id']);
            $table->dropColumn('venue_hotel_id');
        });

        Schema::dropIfExists('venue_hotels');
    }
};
