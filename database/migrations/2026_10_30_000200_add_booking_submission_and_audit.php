<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->uuid('submission_key')->nullable();
            $table->char('request_hash', 64)->nullable();
            $table->enum('travel_class', ['SL', '3A', '2A', '1A', 'CC', '2S', 'EC'])->nullable();
            $table->unique(['user_id', 'submission_key'], 'booking_owner_submission_unique');
        });
        Schema::table('waitlist_entries', function (Blueprint $table) {
            $table->index(['trip_id', 'travel_class', 'status', 'created_at', 'id'], 'waitlist_fifo_idx');
        });
        Schema::create('booking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->enum('action', ['created', 'confirmed', 'waitlisted', 'cancelled', 'promoted', 'expired']);
            $table->string('from_status', 12)->nullable();
            $table->enum('to_status', ['pending', 'confirmed', 'waitlisted', 'cancelled']);
            $table->timestamps();
            $table->index(['booking_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_events');
        Schema::table('waitlist_entries', fn (Blueprint $table) => $table->dropIndex('waitlist_fifo_idx'));
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('booking_owner_submission_unique');
            $table->dropColumn(['submission_key', 'request_hash', 'travel_class']);
        });
    }
};
