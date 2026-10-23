<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttractionsTable extends Migration
{
    public function up()
    {
        Schema::create('attractions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->text('detail')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('show_on_homepage')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attractions');
    }
}
