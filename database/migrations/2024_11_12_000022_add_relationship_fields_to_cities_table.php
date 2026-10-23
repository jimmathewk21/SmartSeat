<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToCitiesTable extends Migration
{
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable();
            $table->foreign('state_id', 'state_fk_10226442')->references('id')->on('states');
        });
    }
}
