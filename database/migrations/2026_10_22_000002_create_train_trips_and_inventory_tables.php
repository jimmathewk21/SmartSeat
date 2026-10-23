<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('train_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained()->restrictOnDelete();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->string('status', 20)->default('scheduled')->index();
            $table->timestamps();
            $table->unique(['id', 'train_id']);
            $table->index(['train_id', 'starts_at', 'status']);
        });

        Schema::create('trip_seat_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('train_trip_id');
            $table->unsignedBigInteger('train_id');
            $table->unsignedBigInteger('train_seat_id');
            $table->string('status', 20)->default('available')->index();
            $table->unsignedBigInteger('current_booking_id')->nullable();
            $table->timestamps();
            $table->foreign(['train_trip_id', 'train_id'], 'trip_inventory_trip_train_fk')
                ->references(['id', 'train_id'])->on('train_trips')->cascadeOnDelete();
            $table->foreign(['train_seat_id', 'train_id'], 'trip_inventory_seat_train_fk')
                ->references(['id', 'train_id'])->on('train_seats')->cascadeOnDelete();
            $table->unique(['train_trip_id', 'train_seat_id'], 'trip_inventory_trip_seat_uq');
            $table->index(['train_trip_id', 'status', 'train_seat_id'], 'trip_inventory_availability_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_seat_inventories');
        Schema::dropIfExists('train_trips');
    }
};
