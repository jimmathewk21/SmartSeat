<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Models\MyContact;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class MyContactSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_my_contact_submission_sends_admin_email_with_uploaded_profile_details(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['my_contacts' => ['guest' => true]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->withArgs(function (string $templateKey, array $payload): bool {
                return $templateKey === 'my_contact_submission'
                    && $payload['full_name'] === 'Raju Kumar'
                    && $payload['designation'] === 'Partner'
                    && $payload['nationality'] === 'India'
                    && $payload['category'] === 'Law Firm'
                    && $payload['company_name'] === 'IGI Summit'
                    && $payload['area_of_expertise'] === 'Patents'
                    && $payload['official_email'] === 'raju@example.com'
                    && $payload['publish_basic_details'] === true
                    && $payload['publish_contact_details'] === true
                    && $payload['confirm_accuracy'] === true
                    && $payload['grant_permission'] === true
                    && str_contains($payload['photo_url'], 'uploads/my-contact-submissions/');
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $response = $this->withSession(['my_contact_captcha_answer' => '9'])
            ->post(route('frontend.my-contacts.store'), [
                'full_name' => 'Raju Kumar',
                'designation' => 'Partner',
                'nationality' => 'India',
                'category' => 'Law Firm',
                'gender' => 'male',
                'photo_file' => UploadedFile::fake()->image('profile.jpg'),
                'company_name' => 'IGI Summit',
                'area_of_expertise' => 'Patents',
                'official_email' => 'raju@example.com',
                'alternate_email' => 'raju.alt@example.com',
                'mobile_whatsapp_number' => '+919876543210',
                'linkedin_url' => 'https://linkedin.com/in/raju',
                'website_url' => 'https://example.com',
                'publish_basic_details' => '1',
                'publish_contact_details' => '1',
                'confirm_accuracy' => '1',
                'grant_permission' => '1',
                'captcha' => '9',
            ]);

        $response->assertRedirect(route('frontend.my-contacts.create'));
        $response->assertSessionHas('message');
    }

    public function test_my_contact_submission_requires_core_fields_and_valid_captcha(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['my_contacts' => ['guest' => true]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $response = $this->from(route('frontend.my-contacts.create'))
            ->withSession(['my_contact_captcha_answer' => '9'])
            ->post(route('frontend.my-contacts.store'), [
                'full_name' => 'Raju Kumar',
                'captcha' => '5',
            ]);

        $response->assertRedirect(route('frontend.my-contacts.create'));
        $response->assertSessionHasErrors([
            'designation',
            'nationality',
            'category',
            'gender',
            'photo_file',
            'company_name',
            'area_of_expertise',
            'official_email',
            'confirm_accuracy',
            'grant_permission',
            'captcha',
        ]);
    }

    public function test_my_contact_email_check_endpoint_reports_existing_and_available_addresses(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['my_contacts' => ['guest' => true]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        MyContact::query()->create([
            'name' => 'Existing Contact',
            'official_email' => 'existing@example.com',
        ]);

        $takenResponse = $this->getJson(route('frontend.my-contacts.check-email', [
            'official_email' => 'existing@example.com',
        ]));

        $takenResponse->assertOk()
            ->assertJson([
                'exists' => true,
                'message' => 'This email already exists.',
            ]);

        $availableResponse = $this->getJson(route('frontend.my-contacts.check-email', [
            'official_email' => 'available@example.com',
        ]));

        $availableResponse->assertOk()
            ->assertJson([
                'exists' => false,
                'message' => 'This email is available.',
            ]);
    }

    public function test_my_contact_submission_rejects_duplicate_official_email(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['my_contacts' => ['guest' => true]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        MyContact::query()->create([
            'name' => 'Existing Contact',
            'official_email' => 'raju@example.com',
        ]);

        $response = $this->from(route('frontend.my-contacts.create'))
            ->withSession(['my_contact_captcha_answer' => '9'])
            ->post(route('frontend.my-contacts.store'), [
                'full_name' => 'Raju Kumar',
                'designation' => 'Partner',
                'nationality' => 'India',
                'category' => 'Law Firm',
                'gender' => 'male',
                'photo_file' => UploadedFile::fake()->image('profile.jpg'),
                'company_name' => 'IGI Summit',
                'area_of_expertise' => 'Patents',
                'official_email' => 'raju@example.com',
                'alternate_email' => 'raju.alt@example.com',
                'mobile_whatsapp_number' => '+919876543210',
                'linkedin_url' => 'https://linkedin.com/in/raju',
                'website_url' => 'https://example.com',
                'publish_basic_details' => '1',
                'publish_contact_details' => '1',
                'confirm_accuracy' => '1',
                'grant_permission' => '1',
                'captcha' => '9',
            ]);

        $response->assertRedirect(route('frontend.my-contacts.create'));
        $response->assertSessionHasErrors(['official_email']);
    }
}
