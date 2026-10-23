<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class SponsorInquirySubmissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sponsor_inquiry_requires_key_basic_and_sponsorship_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('frontend.become-sponsor.create'))
            ->actingAs($user)
            ->withSession(['sponsor_captcha_answer' => '9'])
            ->post(route('frontend.become-sponsor.store'), [
                'name' => 'Sponsor User',
                'email' => 'sponsor@example.com',
                'captcha' => '9',
                'consent_accuracy' => '1',
                'consent_review_time' => '1',
                'consent_approval_required' => '1',
            ]);

        $response->assertRedirect(route('frontend.become-sponsor.create'));
        $response->assertSessionHasErrors([
            'company_name',
            'sponsorship_categories',
            'budget_range',
            'benefit_logo_placement',
            'benefit_backdrop_logo',
            'benefit_participant_list',
            'benefit_promotional_materials',
            'a4_advert_option',
            'total_registrations_required',
            'benefit_social_media_posts',
            'benefit_speaking_slot',
            'benefit_exhibit_space',
            'rollup_standees_option',
        ]);
    }

    public function test_sponsor_inquiry_stores_required_sponsorship_detail_fields(): void
    {
        $user = User::factory()->create();

        $capturedAttachmentPath = null;
        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->withArgs(function (string $templateKey, array $payload, array $attachments = []) use (&$capturedAttachmentPath): bool {
                $capturedAttachmentPath = $attachments[0]['path'] ?? null;

                return $templateKey === 'sponsor_inquiry'
                    && $payload['name'] === 'Sponsor User'
                    && $payload['company_name'] === 'IGI Summit'
                    && $payload['email'] === 'sponsor@example.com'
                    && $payload['budget_range'] === 'USD 4999 ~ USD 9999'
                    && $payload['benefit_logo_placement'] === 'Yes'
                    && $payload['benefit_prominent_logo'] === 'Yes'
                    && $payload['benefit_participant_list'] === 'Yes'
                    && $payload['benefit_promotional_materials'] === 'Yes'
                    && $payload['a4_advert_option'] === 'One (1) Page A4 Advert'
                    && $payload['benefit_social_media_posts'] === '3 to 5 posts'
                    && $payload['benefit_speaking_slot'] === 'No'
                    && $payload['benefit_exhibit_space'] === 'No'
                    && $payload['benefit_backdrop_logo'] === 'No'
                    && $payload['benefit_required_delegate_registrations'] === 'Yes'
                    && $payload['registration_category'] === ''
                    && $payload['total_registrations_required'] === '3 Registrations'
                    && $payload['rollup_standees_option'] === 'One (1)'
                    && $payload['consent_accuracy'] === 'Yes'
                    && $payload['consent_approval_required'] === 'Yes'
                    && is_string($capturedAttachmentPath)
                    && str_contains(str_replace('\\', '/', $capturedAttachmentPath), '/public/uploads/sponsor-inquiries/');
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $response = $this->actingAs($user)
            ->withSession(['sponsor_captcha_answer' => '9'])
            ->post(route('frontend.become-sponsor.store'), [
                'name' => 'Sponsor User',
                'company_name' => 'IGI Summit',
                'email' => 'sponsor@example.com',
                'sponsorship_categories' => ['Platinum Sponsor'],
                'budget_range' => 'USD 4999 ~ USD 9999',
                'benefit_logo_placement' => 'Yes',
                'benefit_backdrop_logo' => 'No',
                'benefit_participant_list' => 'Yes',
                'benefit_promotional_materials' => 'Yes',
                'a4_advert_option' => 'One (1) Page A4 Advert',
                'total_registrations_required' => '3 Registrations',
                'benefit_social_media_posts' => '3 to 5 posts',
                'benefit_speaking_slot' => 'No',
                'benefit_exhibit_space' => 'No',
                'rollup_standees_option' => 'One (1)',
                'consent_accuracy' => '1',
                'consent_review_time' => '1',
                'consent_approval_required' => '1',
                'captcha' => '9',
                'company_logo_file' => UploadedFile::fake()->image('sponsor-logo.png'),
            ]);

        $response->assertRedirect(route('frontend.become-sponsor.create'));

        $this->assertDatabaseHas('sponsor_inquiries', [
            'name' => 'Sponsor User',
            'company_name' => 'IGI Summit',
            'email' => 'sponsor@example.com',
            'budget_range' => 'USD 4999 ~ USD 9999',
            'benefit_prominent_logo' => 'Yes',
            'benefit_social_media_posts' => '3 to 5 posts',
            'benefit_backdrop_logo' => 'No',
            'benefit_required_delegate_registrations' => 'Yes',
            'registration_category' => null,
            'total_registrations_required' => 3,
        ]);

        $this->assertNotNull($capturedAttachmentPath);
        $this->assertFileExists($capturedAttachmentPath);

        @unlink($capturedAttachmentPath);
    }
}
