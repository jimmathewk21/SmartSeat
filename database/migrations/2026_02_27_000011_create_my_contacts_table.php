<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_contacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('picture_url')->nullable();
            $table->string('gender')->nullable();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('company_name')->nullable();
            $table->string('official_email')->unique();
            $table->string('personal_email')->nullable();
            $table->string('alternate_email')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('country')->nullable();
            $table->string('category')->nullable();
            $table->string('role')->nullable();
            $table->string('event_name')->nullable();
            $table->boolean('is_subscribed')->default(true);
            $table->unsignedTinyInteger('stars')->default(0);
            $table->string('linkedin_profile')->nullable();
            $table->boolean('is_whitelisted')->default(false);
            $table->boolean('is_blacklisted')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_contacts');
    }
};

