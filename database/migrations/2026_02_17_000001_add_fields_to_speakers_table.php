<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSpeakersTable extends Migration
{
    public function up()
    {
        Schema::table('speakers', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('designation');
            $table->string('country')->nullable()->after('company_name');
            $table->string('email')->nullable()->after('country');
            $table->string('phone')->nullable()->after('email');
            $table->string('topic')->nullable()->after('phone');
            $table->string('category')->nullable()->after('topic');
            $table->string('biography_type')->default('text')->after('bio');
            $table->string('resource_url')->nullable()->after('biography_type');
            $table->boolean('show_on_homepage')->default(false)->after('resource_url');
            $table->boolean('show_on_current_conference')->default(false)->after('show_on_homepage');
            $table->boolean('show_on_future_conference')->default(false)->after('show_on_current_conference');
        });
    }

    public function down()
    {
        Schema::table('speakers', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'country',
                'email',
                'phone',
                'topic',
                'category',
                'biography_type',
                'resource_url',
                'show_on_homepage',
                'show_on_current_conference',
                'show_on_future_conference',
            ]);
        });
    }
}
