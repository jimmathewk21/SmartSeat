<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            if (! Schema::hasColumn('sponsors', 'logo_size')) {
                $table->string('logo_size', 10)->default('md')->after('logo_url');
            }
        });

        if (! Schema::hasTable('sponsor_sponsor_category')) {
            Schema::create('sponsor_sponsor_category', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sponsor_id');
                $table->unsignedBigInteger('sponsor_category_id');
                $table->timestamps();

                $table->unique(['sponsor_id', 'sponsor_category_id'], 'sponsor_category_unique');
                $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
                $table->foreign('sponsor_category_id')->references('id')->on('sponsor_categories')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('sponsor_sponsor_category')) {
            $pairs = DB::table('sponsors')
                ->select('id as sponsor_id', 'sponsor_category_id')
                ->whereNotNull('sponsor_category_id')
                ->get();

            foreach ($pairs as $pair) {
                DB::table('sponsor_sponsor_category')->updateOrInsert(
                    [
                        'sponsor_id' => $pair->sponsor_id,
                        'sponsor_category_id' => $pair->sponsor_category_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_sponsor_category');

        Schema::table('sponsors', function (Blueprint $table) {
            if (Schema::hasColumn('sponsors', 'logo_size')) {
                $table->dropColumn('logo_size');
            }
        });
    }
};
