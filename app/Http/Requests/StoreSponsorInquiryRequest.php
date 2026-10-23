<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSponsorInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'company_logo_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'company_profile' => ['nullable', 'string', 'max:2000'],
            'sponsorship_categories' => ['required', 'array', 'size:1'],
            'sponsorship_categories.*' => ['required', 'in:Platinum Sponsor,Gold Sponsor,Silver Sponsor,Exhibit Sponsor,Gift Sponsor'],
            'budget_range' => ['required', 'in:USD 499 ~ USD 999,USD 999 ~ USD 1999,USD 1999 ~ USD 2999,USD 2999 ~ USD 4999,USD 4999 ~ USD 9999'],
            'benefit_logo_placement' => ['required', 'in:Yes,No'],
            'benefit_backdrop_logo' => ['required', 'in:Yes,No'],
            'benefit_participant_list' => ['required', 'in:Yes,No'],
            'benefit_promotional_materials' => ['required', 'in:Yes,No'],
            'a4_advert_option' => ['required', 'in:One (1) Page A4 Advert,Two (2) Page A4 Advert,None'],
            'total_registrations_required' => ['required', 'in:1 Registration,2 Registrations,3 Registrations,4 Registrations,More than 5 Registrations'],
            'benefit_social_media_posts' => ['required', 'in:1 to 3 posts,3 to 5 posts,None'],
            'benefit_speaking_slot' => ['required', 'in:Yes,No'],
            'benefit_exhibit_space' => ['required', 'in:Yes,No'],
            'rollup_standees_option' => ['required', 'in:One (1),Two (2),Not required'],
            'benefit_prominent_logo' => ['nullable', 'in:Yes,No'],
            'benefit_required_delegate_registrations' => ['nullable', 'in:Yes,No'],
            'registration_category' => ['nullable', 'in:Economy,Prime,Premium'],
            'consent_accuracy' => ['accepted'],
            'consent_review_time' => ['nullable', 'boolean'],
            'consent_approval_required' => ['accepted'],
            'captcha' => [
                'required',
                function ($attribute, $value, $fail): void {
                    $expected = (string) session('sponsor_captcha_answer', '');
                    if ($expected === '' || trim((string) $value) !== $expected) {
                        $fail('Invalid captcha answer.');
                    }
                },
            ],
            'message' => ['nullable', 'string'],
        ];
    }
}
