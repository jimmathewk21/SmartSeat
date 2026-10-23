<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFaqCategoryToFaqsTable extends Migration
{
    public function up()
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->unsignedBigInteger('faq_category_id')
                ->nullable()
                ->after('event_id');
        });

        $generalCategoryId = DB::table('faq_categories')->insertGetId([
            'name' => 'General',
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('faqs')
            ->whereNull('faq_category_id')
            ->update(['faq_category_id' => $generalCategoryId]);

        Schema::table('faqs', function (Blueprint $table) {
            $table->foreign('faq_category_id', 'faq_category_fk_202602270002')
                ->references('id')
                ->on('faq_categories')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign('faq_category_fk_202602270002');
            $table->dropColumn('faq_category_id');
        });
    }
}

