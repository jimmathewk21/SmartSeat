<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationToEventsTable extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'location')) {
                $table->string('location')->nullable()->after('venue');
            }
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
}
