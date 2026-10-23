<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSponsorsTable extends Migration
{
    public function up()
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->text('detail')->nullable()->after('logo_url');
            $table->string('asset_type')->default('file')->after('detail');
            $table->string('resource_url')->nullable()->after('asset_type');
            $table->boolean('show_on_homepage')->default(false)->after('resource_url');
        });
    }

    public function down()
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn(['detail', 'asset_type', 'resource_url', 'show_on_homepage']);
        });
    }
}
