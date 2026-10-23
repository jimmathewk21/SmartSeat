<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('speaker_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('full_name');
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('designation')->nullable();
            $table->string('organization')->nullable();
            $table->text('short_bio')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('website_url')->nullable();
            $table->string('official_email');
            $table->string('personal_email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('alternate_mobile')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('proposed_topic_title')->nullable();
            $table->string('sub_theme')->nullable();
            $table->string('session_type')->nullable();
            $table->string('presentation_duration')->nullable();
            $table->text('abstract_summary')->nullable();
            $table->text('areas_of_expertise')->nullable();
            $table->string('years_of_experience')->nullable();
            $table->text('previous_engagements')->nullable();
            $table->text('publications_awards')->nullable();
            $table->string('presentation_mode')->nullable();
            $table->text('av_requirements')->nullable();
            $table->boolean('travel_support_required')->default(false);
            $table->text('special_requirements')->nullable();
            $table->boolean('confirm_accuracy')->default(false);
            $table->boolean('consent_promotional_use')->default(false);
            $table->boolean('consent_selection_terms')->default(false);
            $table->string('ip_address', 64)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reviewed_by', 'speaker_applications_reviewed_by_fk')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('speaker_applications');
    }
};

