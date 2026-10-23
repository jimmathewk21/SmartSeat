<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class SpeakerApplicationSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_speaker_application_stores_required_and_support_fields(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['become_speaker' => ['guest' => true]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        Mail::fake();

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->withArgs(function (string $templateKey, array $payload): bool {
                return $templateKey === 'speaker_application'
                    && $payload['full_name'] === 'Raju Kumar'
                    && $payload['organization'] === 'IGI Summit'
                    && $payload['country'] === 'India'
                    && $payload['proposed_topic_title'] === 'AI Governance'
                    && $payload['air_ticket_support'] === true
                    && $payload['hotel_stay_support'] === 'No'
                    && $payload['registration_support'] === 'Yes'
                    && $payload['sponsor_interest'] === 'No'
                    && $payload['av_requirements'] === 'Yes';
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $response = $this->post(route('frontend.speakers.apply'), [
            'full_name' => 'Raju Kumar',
            'gender' => 'male',
            'nationality' => 'India',
            'organization' => 'IGI Summit',
            'photo_file' => UploadedFile::fake()->image('speaker.jpg'),
            'official_email' => 'raju@example.com',
            'proposed_topic_title' => 'AI Governance',
            'av_requirements' => 'Yes',
            'travel_support_required' => '1',
            'sub_theme' => 'No',
            'session_type' => 'Yes',
            'previous_engagements' => 'No',
            'presentation_duration' => '30',
            'confirm_accuracy' => '1',
            'consent_promotional_use' => '1',
            'consent_selection_terms' => '1',
        ]);

        $response->assertRedirect(route('frontend.speakers.index'));

        $this->assertDatabaseHas('speaker_applications', [
            'full_name' => 'Raju Kumar',
            'gender' => 'male',
            'nationality' => 'India',
            'organization' => 'IGI Summit',
            'official_email' => 'raju@example.com',
            'country' => 'India',
            'proposed_topic_title' => 'AI Governance',
            'av_requirements' => 'Yes',
            'travel_support_required' => 1,
            'sub_theme' => 'No',
            'session_type' => 'Yes',
            'previous_engagements' => 'No',
            'presentation_duration' => '30',
            'status' => 'pending',
        ]);
    }

    public function test_speaker_application_requires_company_photo_topic_and_support_answers(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['become_speaker' => ['guest' => true]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $response = $this->from(route('frontend.speakers.apply.create'))
            ->post(route('frontend.speakers.apply'), [
                'full_name' => 'Raju Kumar',
                'official_email' => 'raju@example.com',
            ]);

        $response->assertRedirect(route('frontend.speakers.apply.create'));
        $response->assertSessionHasErrors([
            'organization',
            'photo_file',
            'proposed_topic_title',
            'travel_support_required',
            'sub_theme',
            'session_type',
            'previous_engagements',
            'confirm_accuracy',
            'consent_promotional_use',
            'consent_selection_terms',
        ]);
    }
}
