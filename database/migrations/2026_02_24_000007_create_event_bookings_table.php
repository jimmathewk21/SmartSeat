<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventBookingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('event_bookings')) {
            return;
        }

        Schema::create('event_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('booking_code')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('event_ticket_plan_id');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_paise');
            $table->unsignedBigInteger('total_amount_paise');
            $table->string('currency', 3)->default('INR');
            $table->string('status', 20)->default('pending');
            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->string('razorpay_signature')->nullable();
            $table->text('failure_reason')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->longText('payment_payload')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('set null');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('event_ticket_plan_id')->references('id')->on('event_ticket_plans')->onDelete('restrict');
            $table->index(['event_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_bookings');
    }
}

