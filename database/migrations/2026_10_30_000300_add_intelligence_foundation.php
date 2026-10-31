<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->enum('accessibility_status', ['unknown', 'accessible', 'not_accessible'])->default('unknown');
        });
        Schema::table('passengers', function (Blueprint $table) {
            $table->enum('preferred_berth', ['any', 'lower', 'middle', 'upper', 'side_lower', 'side_upper', 'chair'])->default('any');
            $table->boolean('accessible_seat_required')->default(false);
        });
        Schema::table('bookings', fn (Blueprint $table) => $table->boolean('keep_group_together')->default(false));
        Schema::create('waitlist_prediction_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('train_id')->constrained()->restrictOnDelete();
            $table->string('travel_class', 2);
            $table->string('feature_version', 40);
            $table->dateTime('observed_at')->index();
            $table->dateTime('boarding_departure_at')->index();
            $table->json('features');
            $table->timestamps();
            $table->index(['train_id', 'travel_class', 'boarding_departure_at'], 'snapshot_history_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_prediction_snapshots');
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn('keep_group_together'));
        Schema::table('passengers', fn (Blueprint $table) => $table->dropColumn(['preferred_berth', 'accessible_seat_required']));
        Schema::table('seats', fn (Blueprint $table) => $table->dropColumn('accessibility_status'));
    }
};
