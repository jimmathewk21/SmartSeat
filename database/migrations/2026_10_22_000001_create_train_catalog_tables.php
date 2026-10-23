<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->index();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('trains', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('train_route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained()->cascadeOnDelete();
            $table->foreignId('station_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('stop_order');
            $table->unsignedInteger('arrival_offset_minutes')->nullable();
            $table->unsignedInteger('departure_offset_minutes')->nullable();
            $table->timestamps();
            $table->unique(['train_id', 'stop_order']);
            $table->unique(['train_id', 'station_id']);
            $table->index(['station_id', 'train_id']);
        });

        Schema::create('train_coaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained()->cascadeOnDelete();
            $table->string('code', 12);
            $table->string('class_code', 12)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['train_id', 'code']);
            $table->unique(['id', 'train_id']);
        });

        Schema::create('train_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('train_coach_id');
            $table->string('number', 12);
            $table->string('berth_type', 20)->nullable();
            $table->boolean('is_accessible')->default(false)->index();
            $table->timestamps();
            $table->foreign(['train_coach_id', 'train_id'], 'train_seats_coach_train_fk')
                ->references(['id', 'train_id'])->on('train_coaches')->cascadeOnDelete();
            $table->unique(['train_coach_id', 'number']);
            $table->unique(['id', 'train_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('train_seats');
        Schema::dropIfExists('train_coaches');
        Schema::dropIfExists('train_route_stops');
        Schema::dropIfExists('trains');
        Schema::dropIfExists('stations');
    }
};
