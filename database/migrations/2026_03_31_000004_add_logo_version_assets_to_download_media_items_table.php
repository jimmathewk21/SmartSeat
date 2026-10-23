<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('download_media_items', function (Blueprint $table) {
            foreach ([1, 2, 3] as $version) {
                $table->string("version_{$version}_label")->nullable()->after('secondary_file_url');
                $table->string("version_{$version}_image_url")->nullable()->after("version_{$version}_label");
                $table->string("version_{$version}_pdf_url")->nullable()->after("version_{$version}_image_url");
                $table->string("version_{$version}_png_url")->nullable()->after("version_{$version}_pdf_url");
                $table->string("version_{$version}_jpg_url")->nullable()->after("version_{$version}_png_url");
            }
        });
    }

    public function down(): void
    {
        Schema::table('download_media_items', function (Blueprint $table) {
            foreach ([1, 2, 3] as $version) {
                $table->dropColumn([
                    "version_{$version}_label",
                    "version_{$version}_image_url",
                    "version_{$version}_pdf_url",
                    "version_{$version}_png_url",
                    "version_{$version}_jpg_url",
                ]);
            }
        });
    }
};
