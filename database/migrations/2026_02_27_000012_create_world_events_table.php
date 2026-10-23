<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('days')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('hotel')->nullable();
            $table->string('theme')->nullable();
            $table->string('website')->nullable();
            $table->unsignedTinyInteger('stars')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['name', 'start_date'], 'world_events_name_start_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_events');
    }
};

