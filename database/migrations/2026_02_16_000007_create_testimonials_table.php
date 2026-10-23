<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestimonialsTable extends Migration
{
    public function up()
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->year('year')->nullable();
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id', 'testimonial_event_fk_202602160007')
                ->references('id')
                ->on('events')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('testimonials');
    }
}
