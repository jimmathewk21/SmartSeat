<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpeakerApplicationRequest;
use App\Models\Country;
use App\Models\SpeakerApplication;
use App\Services\AdminNotificationMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class FrontendSpeakerApplicationController extends Controller
{
    public function create()
    {
        $countries = Country::query()
            ->select(['name'])
            ->orderBy('name')
            ->pluck('name');

        return view('frontend.speakers.apply', compact('countries'));
    }

    public function store(StoreSpeakerApplicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $photoUrl = null;
        if ($request->hasFile('photo_file')) {
            $image = $request->file('photo_file');
            $directory = public_path('uploads/speaker-applications');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($image->getClientOriginalExtension());
            $fileName = 'speaker-application-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $image->move($directory, $fileName);
            $photoUrl = 'uploads/speaker-applications/' . $fileName;
        }

        $application = SpeakerApplication::create([
            ...$validated,
            'photo_url' => $photoUrl,
            'country' => (string) ($validated['country'] ?? $validated['nationality'] ?? ''),
            'travel_support_required' => (bool) ($validated['travel_support_required'] ?? false),
            'confirm_accuracy' => true,
            'consent_promotional_use' => true,
            'consent_selection_terms' => true,
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        $this->sendSubmissionEmails($application);

        return redirect()
            ->route('frontend.speakers.index')
            ->with('message', 'Thank you for submitting form. We are currently reviewing your details and will reach out to you soon with further information and the proposal on your email address. For any assistance or support you can reach us at info@igisummit.com');
    }

    private function sendSubmissionEmails(SpeakerApplication $application): void
    {
        try {
            app(AdminNotificationMailer::class)->send('speaker_application', [
                'full_name' => $application->full_name,
                'gender' => $application->gender ?: '-',
                'nationality' => $application->nationality ?: '-',
                'designation' => $application->designation ?: '-',
                'organization' => $application->organization ?: '-',
                'short_bio' => $application->short_bio ?: '-',
                'photo_url' => $application->photo_url ?: '-',
                'linkedin_url' => $application->linkedin_url ?: '-',
                'website_url' => $application->website_url ?: '-',
                'official_email' => $application->official_email,
                'personal_email' => $application->personal_email ?: '-',
                'mobile' => $application->mobile ?: '-',
                'alternate_mobile' => $application->alternate_mobile ?: '-',
                'city' => $application->city ?: '-',
                'state' => $application->state ?: '-',
                'country' => $application->country ?: '-',
                'proposed_topic_title' => $application->proposed_topic_title ?: '-',
                'hotel_stay_support' => $application->sub_theme ?: '-',
                'registration_support' => $application->session_type ?: '-',
                'speaking_slot_duration' => $application->presentation_duration ?: '-',
                'abstract_summary' => $application->abstract_summary ?: '-',
                'areas_of_expertise' => $application->areas_of_expertise ?: '-',
                'years_of_experience' => $application->years_of_experience ?: '-',
                'sponsor_interest' => $application->previous_engagements ?: '-',
                'publications_awards' => $application->publications_awards ?: '-',
                'presentation_mode' => $application->presentation_mode ?: '-',
                'av_requirements' => $application->av_requirements ?: '-',
                'air_ticket_support' => $application->travel_support_required,
                'special_requirements' => $application->special_requirements ?: '-',
                'session_type' => $application->session_type ?: '-',
            ]);
        } catch (\Throwable $e) {
            // Keep flow non-blocking when SMTP is unavailable.
        }
    }
}
