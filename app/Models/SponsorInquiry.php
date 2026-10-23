<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorInquiry extends Model
{
    use HasFactory;

    public $table = 'sponsor_inquiries';

    protected $fillable = [
        'name',
        'job_title',
        'email',
        'phone',
        'company_name',
        'country',
        'category',
        'website_url',
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
        'rollup_standees_option',
        'benefit_speaking_slot',
        'benefit_promotional_materials',
        'consent_accuracy',
        'consent_review_time',
        'consent_approval_required',
        'message',
    ];

    protected $casts = [
        'sponsorship_categories' => 'array',
        'benefit_logo_placement' => 'boolean',
        'benefit_social_media_promotion' => 'boolean',
        'benefit_one_a4_advert' => 'boolean',
        'benefit_two_a4_advert' => 'boolean',
        'benefit_exhibit_space' => 'boolean',
        'benefit_participant_list' => 'boolean',
        'benefit_rollup_standees' => 'boolean',
        'benefit_speaking_slot' => 'boolean',
        'benefit_promotional_materials' => 'boolean',
        'consent_accuracy' => 'boolean',
        'consent_review_time' => 'boolean',
        'consent_approval_required' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
