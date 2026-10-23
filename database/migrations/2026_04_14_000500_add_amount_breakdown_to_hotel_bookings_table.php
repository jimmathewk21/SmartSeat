<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->unsignedInteger('number_of_nights')->nullable()->after('number_of_rooms');
            $table->decimal('subtotal_inr', 14, 2)->nullable()->after('rate_usd');
            $table->decimal('subtotal_usd', 14, 2)->nullable()->after('subtotal_inr');
            $table->decimal('gst_rate', 5, 2)->nullable()->after('subtotal_usd');
            $table->decimal('gst_amount_inr', 14, 2)->nullable()->after('gst_rate');
            $table->decimal('gst_amount_usd', 14, 2)->nullable()->after('gst_amount_inr');
            $table->decimal('total_inr', 14, 2)->nullable()->after('gst_amount_usd');
            $table->decimal('total_usd', 14, 2)->nullable()->after('total_inr');
        });
    }

    public function down(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'number_of_nights',
                'subtotal_inr',
                'subtotal_usd',
                'gst_rate',
                'gst_amount_inr',
                'gst_amount_usd',
                'total_inr',
                'total_usd',
            ]);
        });
    }
};

