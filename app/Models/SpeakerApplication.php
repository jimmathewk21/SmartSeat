<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeakerApplication extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'speaker_applications';

    protected $fillable = [
        'full_name',
        'gender',
        'nationality',
        'designation',
        'organization',
        'short_bio',
        'photo_url',
        'linkedin_url',
        'website_url',
        'official_email',
        'personal_email',
        'mobile',
        'alternate_mobile',
        'city',
        'state',
        'country',
        'proposed_topic_title',
        'sub_theme',
        'session_type',
        'presentation_duration',
        'abstract_summary',
        'areas_of_expertise',
        'years_of_experience',
        'previous_engagements',
        'publications_awards',
        'presentation_mode',
        'av_requirements',
        'travel_support_required',
        'special_requirements',
        'confirm_accuracy',
        'consent_promotional_use',
        'consent_selection_terms',
        'ip_address',
        'status',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $casts = [
        'travel_support_required' => 'boolean',
        'confirm_accuracy' => 'boolean',
        'consent_promotional_use' => 'boolean',
        'consent_selection_terms' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

