<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('my_contacts', function (Blueprint $table): void {
            $table->string('experience')->nullable()->after('specialization');
            $table->boolean('is_published')->default(true)->after('contact_status');
            $table->boolean('show_email')->default(false)->after('is_published');
            $table->boolean('show_whatsapp')->default(false)->after('show_email');
            $table->boolean('show_linkedin')->default(false)->after('show_whatsapp');
            $table->boolean('show_website')->default(false)->after('show_linkedin');
            $table->boolean('show_category')->default(false)->after('show_website');
            $table->boolean('show_expertise')->default(false)->after('show_category');
        });
    }

    public function down(): void
    {
        Schema::table('my_contacts', function (Blueprint $table): void {
            $table->dropColumn([
                'experience',
                'is_published',
                'show_email',
                'show_whatsapp',
                'show_linkedin',
                'show_website',
                'show_category',
                'show_expertise',
            ]);
        });
    }
};
