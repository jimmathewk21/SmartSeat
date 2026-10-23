<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgendaTables extends Migration
{
    public function up()
    {
        Schema::create('agenda_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id');
            $table->string('title');
            $table->string('weekday')->nullable();
            $table->date('agenda_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id', 'agenda_day_event_fk_202602270004')
                ->references('id')
                ->on('events')
                ->cascadeOnDelete();
        });

        Schema::create('agenda_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id', 'agenda_room_event_fk_202602270004')
                ->references('id')
                ->on('events')
                ->cascadeOnDelete();
        });

        Schema::create('agenda_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('agenda_day_id');
            $table->unsignedBigInteger('agenda_room_id');
            $table->string('title');
            $table->text('topic_description')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('panel_theme')->nullable();
            $table->string('moderator_name')->nullable();
            $table->unsignedBigInteger('moderator_speaker_id')->nullable();
            $table->string('moderator_ppt_url')->nullable();
            $table->string('agenda_file_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id', 'agenda_item_event_fk_202602270004')
                ->references('id')
                ->on('events')
                ->cascadeOnDelete();
            $table->foreign('agenda_day_id', 'agenda_item_day_fk_202602270004')
                ->references('id')
                ->on('agenda_days')
                ->cascadeOnDelete();
            $table->foreign('agenda_room_id', 'agenda_item_room_fk_202602270004')
                ->references('id')
                ->on('agenda_rooms')
                ->cascadeOnDelete();
            $table->foreign('moderator_speaker_id', 'agenda_item_moderator_speaker_fk_202602270004')
                ->references('id')
                ->on('speakers')
                ->nullOnDelete();
        });

        Schema::create('agenda_item_speaker', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('agenda_item_id');
            $table->unsignedBigInteger('speaker_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('agenda_item_id', 'agenda_item_speaker_item_fk_202602270004')
                ->references('id')
                ->on('agenda_items')
                ->cascadeOnDelete();
            $table->foreign('speaker_id', 'agenda_item_speaker_speaker_fk_202602270004')
                ->references('id')
                ->on('speakers')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agenda_item_speaker');
        Schema::dropIfExists('agenda_items');
        Schema::dropIfExists('agenda_rooms');
        Schema::dropIfExists('agenda_days');
    }
}

