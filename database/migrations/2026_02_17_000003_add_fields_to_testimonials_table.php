<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToTestimonialsTable extends Migration
{
    public function up()
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('photo_url')->nullable()->after('name');
            $table->string('company')->nullable()->after('photo_url');
            $table->string('video_id')->nullable()->after('message');
            $table->date('testimonial_date')->nullable()->after('video_id');
            $table->string('mail_image_url')->nullable()->after('testimonial_date');
            $table->boolean('show_on_homepage')->default(false)->after('mail_image_url');
        });
    }

    public function down()
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['photo_url', 'company', 'video_id', 'testimonial_date', 'mail_image_url', 'show_on_homepage']);
        });
    }
}
