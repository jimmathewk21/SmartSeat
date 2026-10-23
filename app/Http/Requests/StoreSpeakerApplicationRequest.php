<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpeakerApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'prefer_not_to_say', 'other'])],
            'nationality' => ['nullable', 'string', 'max:120'],
            'designation' => ['nullable', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'short_bio' => ['nullable', 'string', 'max:3000'],
            'photo_file' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'official_email' => ['required', 'email', 'max:255'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'alternate_mobile' => ['nullable', 'string', 'max:40'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'proposed_topic_title' => ['required', 'string', 'max:255'],
            'sub_theme' => ['required', 'string', Rule::in(['Yes', 'No'])],
            'session_type' => ['required', 'string', Rule::in(['Yes', 'No'])],
            'presentation_duration' => ['nullable', 'string', 'max:120'],
            'abstract_summary' => ['nullable', 'string', 'max:4000'],
            'areas_of_expertise' => ['nullable', 'string', 'max:2000'],
            'years_of_experience' => ['nullable', 'string', 'max:120'],
            'previous_engagements' => ['required', 'string', Rule::in(['Yes', 'No'])],
            'publications_awards' => ['nullable', 'string', 'max:4000'],
            'presentation_mode' => ['nullable', 'string', Rule::in(['Panel Discussion', 'Presentation'])],
            'av_requirements' => ['nullable', 'string', Rule::in(['Yes', 'No'])],
            'travel_support_required' => ['required', 'boolean'],
            'special_requirements' => ['nullable', 'string', 'max:2000'],
            'confirm_accuracy' => ['accepted'],
            'consent_promotional_use' => ['accepted'],
            'consent_selection_terms' => ['accepted'],
        ];
    }
}
