<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrontendVisibilityFlagsToSponsorsTable extends Migration
{
    public function up()
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->boolean('show_website_url')->default(true)->after('show_contact_details');
            $table->boolean('show_linkedin_url')->default(true)->after('show_website_url');
            $table->boolean('show_bio')->default(true)->after('show_linkedin_url');
            $table->boolean('show_advert')->default(true)->after('show_bio');
        });
    }

    public function down()
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn([
                'show_website_url',
                'show_linkedin_url',
                'show_bio',
                'show_advert',
            ]);
        });
    }
}
