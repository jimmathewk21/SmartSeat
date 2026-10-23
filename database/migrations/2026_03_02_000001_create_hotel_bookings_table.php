<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedInteger('number_of_guests')->default(1);
            $table->unsignedInteger('number_of_rooms')->default(1);
            $table->text('special_requests')->nullable();
            $table->string('status', 30)->default('new');
            $table->timestamps();

            $table->index(['check_in_date', 'check_out_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_bookings');
    }
};

