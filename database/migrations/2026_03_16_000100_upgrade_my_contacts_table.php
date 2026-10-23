<?php

use App\Models\MyContact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('my_contacts', function (Blueprint $table) {
            $table->string('mobile_number')->nullable()->after('alternate_email');
            $table->string('alternate_mobile_number')->nullable()->after('whatsapp_number');
            $table->string('specialization')->nullable()->after('category');
            $table->string('speaker_tag')->nullable()->after('role');
            $table->text('event_names')->nullable()->after('event_name');
            $table->string('web_url')->nullable()->after('linkedin_profile');
            $table->string('contact_status')->default(MyContact::STATUS_DEFAULT)->after('is_blacklisted');
        });

        DB::table('my_contacts')
            ->whereNull('event_names')
            ->update([
                'event_names' => DB::raw('event_name'),
            ]);

        DB::table('my_contacts')
            ->where('is_subscribed', false)
            ->update(['contact_status' => MyContact::STATUS_UNSUBSCRIBED]);

        DB::table('my_contacts')
            ->where('is_whitelisted', true)
            ->where('is_subscribed', true)
            ->update(['contact_status' => MyContact::STATUS_KNOWN]);
    }

    public function down(): void
    {
        Schema::table('my_contacts', function (Blueprint $table) {
            $table->dropColumn([
                'mobile_number',
                'alternate_mobile_number',
                'specialization',
                'speaker_tag',
                'event_names',
                'web_url',
                'contact_status',
            ]);
        });
    }
};
