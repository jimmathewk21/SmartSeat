<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowContactDetailsToSponsorsTable extends Migration
{
    public function up()
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->boolean('show_contact_details')
                ->default(true)
                ->after('show_on_scrollbar');
        });
    }

    public function down()
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn('show_contact_details');
        });
    }
}
