<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpeakerRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('speaker_create');
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge([
                'email' => mb_strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

    public function rules()
    {
        return [
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'name' => ['string', 'required', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo_url' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'speaker_role' => ['required', Rule::in(['speaker', 'moderator', 'both'])],
            'photo_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:speakers,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'show_contact_details' => ['required', 'boolean'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'topic_category' => ['nullable', 'string', 'max:255'],
            'biography_type' => ['required', Rule::in(['text', 'link', 'pdf'])],
            'resource_url' => ['nullable', 'string', 'max:255'],
            'bio_file' => ['nullable', 'file', 'extensions:pdf,doc,docx', 'max:20480'],
            'advert_url' => ['nullable', 'string', 'max:255'],
            'presentation_file_url' => ['nullable', 'string', 'max:255'],
            'advert_file' => ['nullable', 'file', 'extensions:jpeg,jpg,png,pdf', 'max:20480'],
            'presentation_file' => ['nullable', 'file', 'extensions:pdf,doc,docx,ppt,pptx', 'max:20480'],
            'show_on_homepage' => ['required', 'boolean'],
            'show_on_scrollbar' => ['required', 'boolean'],
            'is_key_figure' => ['required', 'boolean'],
        ];
    }
}
