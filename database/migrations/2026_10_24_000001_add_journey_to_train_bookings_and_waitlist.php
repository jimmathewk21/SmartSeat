<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['train_bookings', 'train_waitlist_entries'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('boarding_station_id')->nullable()->after('train_trip_id')->constrained('stations')->restrictOnDelete();
                $table->foreignId('destination_station_id')->nullable()->after('boarding_station_id')->constrained('stations')->restrictOnDelete();
                $table->dateTime('boarding_at')->nullable()->after('destination_station_id');
                $table->dateTime('arrival_at')->nullable()->after('boarding_at');
            });
        }
    }

    public function down(): void
    {
        foreach (['train_waitlist_entries', 'train_bookings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('destination_station_id');
                $table->dropConstrainedForeignId('boarding_station_id');
                $table->dropColumn(['boarding_at', 'arrival_at']);
            });
        }
    }
};
