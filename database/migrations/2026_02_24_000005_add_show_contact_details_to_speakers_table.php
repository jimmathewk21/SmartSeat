<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowContactDetailsToSpeakersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('speakers')) {
            return;
        }

        if (!Schema::hasColumn('speakers', 'show_contact_details')) {
            Schema::table('speakers', function (Blueprint $table) {
                $table->boolean('show_contact_details')
                    ->default(false)
                    ->after('phone');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('speakers')) {
            return;
        }

        if (Schema::hasColumn('speakers', 'show_contact_details')) {
            Schema::table('speakers', function (Blueprint $table) {
                $table->dropColumn('show_contact_details');
            });
        }
    }
}
