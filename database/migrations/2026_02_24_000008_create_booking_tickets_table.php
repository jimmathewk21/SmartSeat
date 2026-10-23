<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingTicketsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_tickets')) {
            return;
        }

        Schema::create('booking_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_booking_id');
            $table->string('ticket_code')->unique();
            $table->string('status', 20)->default('issued');
            $table->dateTime('issued_at')->nullable();
            $table->timestamps();

            $table->foreign('event_booking_id')->references('id')->on('event_bookings')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_tickets');
    }
}

