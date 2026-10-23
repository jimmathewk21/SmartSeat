<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsor_inquiries', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('name');
            $table->string('country')->nullable()->after('company_name');
            $table->string('linkedin_url')->nullable()->after('website_url');
            $table->string('facebook_url')->nullable()->after('linkedin_url');
            $table->string('company_logo_url')->nullable()->after('facebook_url');
            $table->text('company_profile')->nullable()->after('company_logo_url');
            $table->json('sponsorship_categories')->nullable()->after('company_profile');
            $table->string('budget_range')->nullable()->after('sponsorship_categories');
            $table->boolean('benefit_logo_placement')->default(false)->after('budget_range');
            $table->string('benefit_prominent_logo')->nullable()->after('benefit_logo_placement');
            $table->boolean('benefit_social_media_promotion')->default(false)->after('benefit_prominent_logo');
            $table->string('benefit_social_media_posts')->nullable()->after('benefit_social_media_promotion');
            $table->boolean('benefit_one_a4_advert')->default(false)->after('benefit_social_media_posts');
            $table->boolean('benefit_two_a4_advert')->default(false)->after('benefit_one_a4_advert');
            $table->string('benefit_backdrop_logo')->nullable()->after('benefit_two_a4_advert');
            $table->boolean('benefit_exhibit_space')->default(false)->after('benefit_backdrop_logo');
            $table->string('benefit_required_delegate_registrations')->nullable()->after('benefit_exhibit_space');
            $table->string('registration_category')->nullable()->after('benefit_required_delegate_registrations');
            $table->unsignedInteger('total_registrations_required')->nullable()->after('registration_category');
            $table->boolean('benefit_participant_list')->default(false)->after('total_registrations_required');
            $table->boolean('benefit_rollup_standees')->default(false)->after('benefit_participant_list');
            $table->boolean('benefit_speaking_slot')->default(false)->after('benefit_rollup_standees');
            $table->boolean('benefit_promotional_materials')->default(false)->after('benefit_speaking_slot');
            $table->boolean('consent_accuracy')->default(false)->after('benefit_promotional_materials');
            $table->boolean('consent_review_time')->default(false)->after('consent_accuracy');
            $table->boolean('consent_approval_required')->default(false)->after('consent_review_time');
        });
    }

    public function down(): void
    {
        Schema::table('sponsor_inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'job_title',
                'country',
                'linkedin_url',
                'facebook_url',
                'company_logo_url',
                'company_profile',
                'sponsorship_categories',
                'budget_range',
                'benefit_logo_placement',
                'benefit_prominent_logo',
                'benefit_social_media_promotion',
                'benefit_social_media_posts',
                'benefit_one_a4_advert',
                'benefit_two_a4_advert',
                'benefit_backdrop_logo',
                'benefit_exhibit_space',
                'benefit_required_delegate_registrations',
                'registration_category',
                'total_registrations_required',
                'benefit_participant_list',
                'benefit_rollup_standees',
                'benefit_speaking_slot',
                'benefit_promotional_materials',
                'consent_accuracy',
                'consent_review_time',
                'consent_approval_required',
            ]);
        });
    }
};
