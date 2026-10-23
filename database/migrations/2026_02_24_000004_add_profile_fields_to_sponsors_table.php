<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToSponsorsTable extends Migration
{
    public function up()
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->string('linkedin_url')->nullable()->after('website_url');
            $table->string('contact_name')->nullable()->after('show_on_homepage');
            $table->string('contact_designation')->nullable()->after('contact_name');
            $table->string('contact_mobile')->nullable()->after('contact_designation');
            $table->string('contact_email')->nullable()->after('contact_mobile');
            $table->string('advert_url')->nullable()->after('contact_email');
            $table->string('brochure_url')->nullable()->after('advert_url');
            $table->string('sponsor_video_url')->nullable()->after('brochure_url');
        });
    }

    public function down()
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn([
                'linkedin_url',
                'contact_name',
                'contact_designation',
                'contact_mobile',
                'contact_email',
                'advert_url',
                'brochure_url',
                'sponsor_video_url',
            ]);
        });
    }
}
