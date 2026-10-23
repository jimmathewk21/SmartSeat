<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoginTrackingToParticipantsTable extends Migration
{
    public function up()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->ipAddress('last_login_ip')->nullable()->after('mobile');
            $table->timestamp('last_login_at')->nullable()->after('last_login_ip');
        });
    }

    public function down()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['last_login_ip', 'last_login_at']);
        });
    }
}
