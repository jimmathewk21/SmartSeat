<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginActivitiesTable extends Migration
{
    public function up()
    {
        Schema::create('login_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->string('email')->nullable();
            $table->string('status');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('participant_id')->references('id')->on('participants')->nullOnDelete();
            $table->index(['status', 'attempted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_activities');
    }
}
