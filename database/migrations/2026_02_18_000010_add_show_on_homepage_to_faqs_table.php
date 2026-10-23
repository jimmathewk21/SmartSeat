<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowOnHomepageToFaqsTable extends Migration
{
    public function up()
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->boolean('show_on_homepage')->default(false)->after('sort_order');
        });
    }

    public function down()
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn('show_on_homepage');
        });
    }
}

