<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMyContactSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:120'],
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'prefer_not_to_say', 'other'])],
            'photo_file' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'company_name' => ['required', 'string', 'max:255'],
            'area_of_expertise' => ['required', 'string', 'max:255'],
            'official_email' => ['required', 'email', 'max:255', Rule::unique('my_contacts', 'official_email')],
            'alternate_email' => ['nullable', 'email', 'max:255'],
            'mobile_whatsapp_number' => ['nullable', 'string', 'max:50'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'publish_basic_details' => ['nullable', 'boolean'],
            'publish_contact_details' => ['nullable', 'boolean'],
            'confirm_accuracy' => ['accepted'],
            'grant_permission' => ['accepted'],
            'captcha' => [
                'required',
                function ($attribute, $value, $fail): void {
                    $expected = (string) session('my_contact_captcha_answer', '');
                    if ($expected === '' || trim((string) $value) !== $expected) {
                        $fail('Invalid captcha answer.');
                    }
                },
            ],
        ];
    }
}
