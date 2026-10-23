<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_booking_delegates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_booking_id');
            $table->unsignedInteger('delegate_index')->default(1);
            $table->string('delegate_name');
            $table->string('designation')->nullable();
            $table->string('company_name')->nullable();
            $table->string('country')->nullable();
            $table->string('email');
            $table->string('mobile')->nullable();
            $table->timestamps();

            $table->foreign('event_booking_id', 'event_booking_delegates_booking_fk')
                ->references('id')->on('event_bookings')
                ->onDelete('cascade');
            $table->index(['event_booking_id', 'delegate_index'], 'event_booking_delegates_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_booking_delegates');
    }
};

