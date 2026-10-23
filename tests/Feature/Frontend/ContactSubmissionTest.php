<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class ContactSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_contact_submission_requires_valid_captcha(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['contact' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $response = $this->from(route('frontend.contact.index'))
            ->withSession(['contact_captcha_answer' => '9'])
            ->post(route('frontend.contact.store'), [
                'name' => 'Contact User',
                'email' => 'contact@example.com',
                'subject' => 'General Inquiry',
                'message' => 'Hello there',
                'captcha' => '5',
            ]);

        $response->assertRedirect(route('frontend.contact.index'));
        $response->assertSessionHasErrors(['captcha']);
    }

    public function test_contact_submission_sends_admin_email_when_captcha_is_valid(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['contact' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->withArgs(function (string $templateKey, array $payload): bool {
                return $templateKey === 'contact_submission'
                    && $payload['name'] === 'Contact User'
                    && $payload['email'] === 'contact@example.com'
                    && $payload['subject'] === 'General Inquiry'
                    && $payload['message'] === 'Hello there';
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $response = $this->from(route('frontend.contact.index'))
            ->withSession(['contact_captcha_answer' => '9'])
            ->post(route('frontend.contact.store'), [
                'name' => 'Contact User',
                'email' => 'contact@example.com',
                'subject' => 'General Inquiry',
                'message' => 'Hello there',
                'captcha' => '9',
            ]);

        $response->assertRedirect(route('frontend.contact.index'));
        $response->assertSessionHas('message');
    }
}
