<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_questions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 160)->index();
            $table->text('question');
            $table->longText('answer')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_questions');
    }
};
