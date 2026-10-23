<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 6)->unique();
            $table->string('name')->nullable();
            $table->string('discount_type', 20)->default('percentage'); // percentage|flat|quantity
            $table->unsignedInteger('discount_percent')->default(0);
            $table->unsignedBigInteger('flat_inr_paise')->default(0);
            $table->unsignedBigInteger('flat_usd_cents')->default(0);
            $table->unsignedInteger('quantity_buy')->default(0);
            $table->unsignedInteger('quantity_pay')->default(0);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};

