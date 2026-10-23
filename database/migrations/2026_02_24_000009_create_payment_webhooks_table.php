<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentWebhooksTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payment_webhooks')) {
            return;
        }

        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('provider', 30);
            $table->string('event_name')->nullable();
            $table->string('event_id')->nullable();
            $table->string('entity_id')->nullable();
            $table->string('payload_hash', 64)->nullable()->index();
            $table->boolean('signature_valid')->default(false);
            $table->boolean('processed')->default(false);
            $table->longText('payload');
            $table->timestamps();

            $table->unique(['provider', 'event_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_webhooks');
    }
}

