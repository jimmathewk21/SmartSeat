<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        // Schema::create('users', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('name')->nullable();
        //     $table->string('mobile')->nullable();
        //     $table->string('email')->nullable()->unique();
        //     $table->datetime('email_verified_at')->nullable();
        //     $table->string('password')->nullable();
        //     $table->string('remember_token')->nullable();
        //     $table->string('user_type')->nullable();
        //     $table->string('otp')->nullable();
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            // $table->string('remember_token')->nullable();
            $table->string('mobile')->nullable();
            $table->datetime('email_verified_at')->nullable();
            $table->string('company_name')->nullable();
            // $table->enum('user_type', ['admin', 'client', 'user'])->default('user');
            $table->boolean('verification_status')->default(false);
            $table->string('user_type')->nullable();
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}