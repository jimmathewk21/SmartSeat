<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('world_event_id')->nullable()->after('id');
            $table->string('hotel_name')->nullable()->after('booking_code');
            $table->string('room_name')->nullable()->after('hotel_name');
            $table->decimal('rate_inr', 12, 2)->default(0)->after('room_name');
            $table->decimal('rate_usd', 12, 2)->default(0)->after('rate_inr');

            $table->index('world_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->dropIndex(['world_event_id']);
            $table->dropColumn([
                'world_event_id',
                'hotel_name',
                'room_name',
                'rate_inr',
                'rate_usd',
            ]);
        });
    }
};

