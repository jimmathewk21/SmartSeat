<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_media_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('section_title');
            $table->string('item_title');
            $table->string('item_subtitle')->nullable();
            $table->string('image_url')->nullable();
            $table->string('primary_button_text')->nullable();
            $table->string('primary_file_url')->nullable();
            $table->string('secondary_button_text')->nullable();
            $table->string('secondary_file_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_media_items');
    }
};
