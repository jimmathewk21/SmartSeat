<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorInquiriesTable extends Migration
{
    public function up()
    {
        Schema::create('sponsor_inquiries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company_name');
            $table->string('category')->nullable();
            $table->string('website_url')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sponsor_inquiries');
    }
}

