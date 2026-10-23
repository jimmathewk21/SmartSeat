<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParticipantApproval extends Migration
{
    public function up()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('last_login_at');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
        });
    }

    public function down()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'is_approved']);
        });
    }
}
