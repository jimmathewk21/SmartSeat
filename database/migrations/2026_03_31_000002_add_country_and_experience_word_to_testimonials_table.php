<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (! Schema::hasColumn('testimonials', 'experience_word')) {
                $table->string('experience_word', 80)->nullable()->after('message');
            }

            if (! Schema::hasColumn('testimonials', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->after('event_id');
                $table->foreign('country_id', 'testimonial_country_fk')->references('id')->on('countries');
            }
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('testimonials', 'country_id')) {
                $table->dropForeign('testimonial_country_fk');
                $table->dropColumn('country_id');
            }

            if (Schema::hasColumn('testimonials', 'experience_word')) {
                $table->dropColumn('experience_word');
            }
        });
    }
};
