<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorsTable extends Migration
{
    public function up()
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sponsor_category_id');
            $table->string('name');
            $table->string('website_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sponsor_category_id', 'sponsor_category_fk_202602160004')
                ->references('id')
                ->on('sponsor_categories')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sponsors');
    }
}
