<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpeakersTable extends Migration
{
    public function up()
    {
        Schema::create('speakers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id', 'speaker_event_fk_202602160006')
                ->references('id')
                ->on('events')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('speakers');
    }
}
