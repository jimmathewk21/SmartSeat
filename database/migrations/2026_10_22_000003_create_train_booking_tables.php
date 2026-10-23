<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('train_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 32)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('train_trip_id')->constrained('train_trips')->restrictOnDelete();
            $table->string('status', 20)->default('confirmed')->index();
            $table->unsignedSmallInteger('seat_count');
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['train_trip_id', 'status']);
            $table->unique(['id', 'train_trip_id']);
        });

        Schema::create('train_booking_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_booking_id')->constrained('train_bookings')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('gender', 20)->nullable();
            $table->boolean('requires_accessible_seat')->default(false)->index();
            $table->timestamps();
            $table->index(['train_booking_id', 'id']);
        });

        Schema::create('train_booking_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_booking_id')->constrained('train_bookings')->cascadeOnDelete();
            $table->unsignedBigInteger('train_trip_id');
            $table->foreignId('trip_seat_inventory_id')->constrained('trip_seat_inventories')->restrictOnDelete();
            $table->foreignId('train_booking_passenger_id')->nullable()->constrained('train_booking_passengers')->nullOnDelete();
            $table->dateTime('released_at')->nullable()->index();
            $table->timestamps();
            $table->foreign(['train_booking_id', 'train_trip_id'], 'train_booking_seats_booking_trip_fk')
                ->references(['id', 'train_trip_id'])->on('train_bookings')->cascadeOnDelete();
            $table->unique(['train_booking_id', 'trip_seat_inventory_id'], 'train_booking_seat_assignment_uq');
            $table->index(['train_trip_id', 'trip_seat_inventory_id', 'released_at'], 'train_booking_seat_history_idx');
        });

        Schema::table('trip_seat_inventories', function (Blueprint $table) {
            $table->foreign(['current_booking_id', 'train_trip_id'], 'trip_inventory_current_booking_fk')
                ->references(['id', 'train_trip_id'])->on('train_bookings')->restrictOnDelete();
        });

        Schema::create('train_waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('train_trip_id')->constrained('train_trips')->cascadeOnDelete();
            $table->unsignedSmallInteger('seat_count');
            $table->boolean('requires_accessible_seat')->default(false)->index();
            $table->string('status', 20)->default('waiting')->index();
            $table->foreignId('promoted_booking_id')->nullable()->constrained('train_bookings')->nullOnDelete();
            $table->dateTime('promoted_at')->nullable();
            $table->timestamps();
            $table->index(['train_trip_id', 'status', 'created_at', 'id'], 'train_waitlist_order_idx');
            $table->index(['user_id', 'status']);
        });

        Schema::create('train_waitlist_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_waitlist_entry_id')->constrained('train_waitlist_entries')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('gender', 20)->nullable();
            $table->boolean('requires_accessible_seat')->default(false)->index();
            $table->timestamps();
            $table->index(['train_waitlist_entry_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('train_waitlist_passengers');
        Schema::dropIfExists('train_waitlist_entries');
        Schema::table('trip_seat_inventories', function (Blueprint $table) {
            $table->dropForeign('trip_inventory_current_booking_fk');
        });
        Schema::dropIfExists('train_booking_seats');
        Schema::dropIfExists('train_booking_passengers');
        Schema::dropIfExists('train_bookings');
    }
};
