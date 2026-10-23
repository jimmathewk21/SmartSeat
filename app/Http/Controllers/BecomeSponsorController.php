<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSponsorInquiryRequest;
use App\Models\Country;
use App\Models\SponsorCategory;
use App\Models\SponsorInquiry;
use App\Services\AdminNotificationMailer;
use Illuminate\Support\Facades\Session;

class BecomeSponsorController extends Controller
{
    public function create()
    {
        $left = random_int(1, 9);
        $right = random_int(1, 9);
        $question = "{$left} + {$right} = ?";

        Session::put('sponsor_captcha_answer', (string) ($left + $right));
        Session::put('sponsor_captcha_question', $question);

        $categories = SponsorCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        $countries = Country::query()
            ->orderBy('name')
            ->pluck('name');

        return view('frontend.become-sponsor', compact('categories', 'countries', 'question'));
    }

    public function store(StoreSponsorInquiryRequest $request)
    {
        $validated = $request->validated();
        $a4AdvertOption = (string) ($validated['a4_advert_option'] ?? 'None');
        $rollupOption = (string) ($validated['rollup_standees_option'] ?? 'Not required');
        $registrationOption = (string) ($validated['total_registrations_required'] ?? '1 Registration');
        $logoAttachment = [];

        $logoPlacementYes = ($validated['benefit_logo_placement'] ?? 'No') === 'Yes';
        $participantListYes = ($validated['benefit_participant_list'] ?? 'No') === 'Yes';
        $promotionalMaterialYes = ($validated['benefit_promotional_materials'] ?? 'No') === 'Yes';
        $speakingSlotYes = ($validated['benefit_speaking_slot'] ?? 'No') === 'Yes';
        $exhibitSpaceYes = ($validated['benefit_exhibit_space'] ?? 'No') === 'Yes';
        $isOneA4 = $a4AdvertOption === 'One (1) Page A4 Advert';
        $isTwoA4 = $a4AdvertOption === 'Two (2) Page A4 Advert';
        $needsRollup = $rollupOption !== 'Not required';
        $registrationCountMap = [
            '1 Registration' => 1,
            '2 Registrations' => 2,
            '3 Registrations' => 3,
            '4 Registrations' => 4,
            'More than 5 Registrations' => 6,
        ];

        $validated['benefit_logo_placement'] = $logoPlacementYes;
        $validated['benefit_prominent_logo'] = $logoPlacementYes ? 'Yes' : 'No';
        $validated['benefit_participant_list'] = $participantListYes;
        $validated['benefit_promotional_materials'] = $promotionalMaterialYes;
        $validated['benefit_speaking_slot'] = $speakingSlotYes;
        $validated['benefit_exhibit_space'] = $exhibitSpaceYes;
        $validated['benefit_one_a4_advert'] = $isOneA4;
        $validated['benefit_two_a4_advert'] = $isTwoA4;
        $validated['benefit_rollup_standees'] = $needsRollup;
        $validated['rollup_standees_option'] = $rollupOption;
        $validated['benefit_social_media_promotion'] = ($validated['benefit_social_media_posts'] ?? 'None') !== 'None';
        $validated['benefit_required_delegate_registrations'] = 'Yes';
        $validated['total_registrations_required'] = $registrationCountMap[$registrationOption] ?? 1;
        $validated['registration_category'] = null;

        if ($request->hasFile('company_logo_file')) {
            $image = $request->file('company_logo_file');
            $directory = public_path('uploads/sponsor-inquiries');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $fileName = 'sponsor-inquiry-' . now()->format('YmdHis') . '-' . substr(md5((string) microtime(true)), 0, 8) . '.' . $image->getClientOriginalExtension();
            $image->move($directory, $fileName);
            $validated['company_logo_url'] = 'uploads/sponsor-inquiries/' . $fileName;
            $logoAttachment[] = [
                'path' => public_path($validated['company_logo_url']),
                'as' => $fileName,
                'mime' => $image->getMimeType() ?: 'application/octet-stream',
            ];
        }

        unset($validated['company_logo_file'], $validated['captcha'], $validated['a4_advert_option']);

        SponsorInquiry::create($validated);

        app(AdminNotificationMailer::class)->send('sponsor_inquiry', [
            'name' => $validated['name'] ?? '',
            'job_title' => $validated['job_title'] ?? '',
            'email' => $validated['email'] ?? '',
            'phone' => $validated['phone'] ?? '',
            'company_name' => $validated['company_name'] ?? '',
            'country' => $validated['country'] ?? '',
            'category' => $validated['category'] ?? '',
            'website_url' => $validated['website_url'] ?? '',
            'linkedin_url' => $validated['linkedin_url'] ?? '',
            'facebook_url' => $validated['facebook_url'] ?? '',
            'company_profile' => $validated['company_profile'] ?? '',
            'budget_range' => $validated['budget_range'] ?? '',
            'sponsorship_categories' => $validated['sponsorship_categories'] ?? [],
            'benefit_logo_placement' => $logoPlacementYes ? 'Yes' : 'No',
            'benefit_prominent_logo' => $validated['benefit_prominent_logo'] ?? '',
            'benefit_participant_list' => $participantListYes ? 'Yes' : 'No',
            'benefit_promotional_materials' => $promotionalMaterialYes ? 'Yes' : 'No',
            'a4_advert_option' => $a4AdvertOption,
            'benefit_social_media_posts' => $validated['benefit_social_media_posts'] ?? '',
            'benefit_speaking_slot' => $speakingSlotYes ? 'Yes' : 'No',
            'benefit_exhibit_space' => $exhibitSpaceYes ? 'Yes' : 'No',
            'benefit_backdrop_logo' => $validated['benefit_backdrop_logo'] ?? '',
            'benefit_required_delegate_registrations' => $validated['benefit_required_delegate_registrations'] ?? '',
            'registration_category' => $validated['registration_category'] ?? '',
            'total_registrations_required' => $registrationOption,
            'rollup_standees_option' => $rollupOption,
            'consent_accuracy' => ! empty($validated['consent_accuracy']) ? 'Yes' : 'No',
            'consent_approval_required' => ! empty($validated['consent_approval_required']) ? 'Yes' : 'No',
            'message' => $validated['message'] ?? '',
            'company_logo_url' => $validated['company_logo_url'] ?? '',
        ], $logoAttachment);

        Session::forget('sponsor_captcha_answer');
        Session::forget('sponsor_captcha_question');

        return redirect()
            ->route('frontend.become-sponsor.create')
            ->with('sponsor_success_message', 'Thank you for submitting form. We are currently reviewing your details and will reach out to you soon with further information and the proposal on your email address. For any assistance or support you can reach us at info@igisummit.com');
    }
}
