<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTicketPlansTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('event_ticket_plans')) {
            return;
        }

        Schema::create('event_ticket_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedTinyInteger('plan_index');
            $table->string('name');
            $table->unsignedBigInteger('price_paise')->default(0);
            $table->text('benefits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('valid_till_text')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'plan_index']);
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_ticket_plans');
    }
}

