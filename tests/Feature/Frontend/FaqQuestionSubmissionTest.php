<?php

namespace Tests\Feature\Frontend;

use App\Mail\HtmlBodyMail;
use App\Models\Setting;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class FaqQuestionSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_submit_faq_question_and_receives_confirmation_email(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['faqs' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        Mail::fake();

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->withArgs(function (string $templateKey, array $payload): bool {
                return $templateKey === 'contact_submission'
                    && $payload['name'] === 'FAQ User'
                    && $payload['email'] === 'faq-user@example.com'
                    && $payload['subject'] === 'FAQ Question'
                    && $payload['message'] === 'What is the agenda for day 1?';
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $response = $this->post(route('frontend.faqs.store'), [
            'name' => 'FAQ User',
            'email' => 'faq-user@example.com',
            'question' => 'What is the agenda for day 1?',
        ]);

        $response->assertRedirect(route('frontend.faqs.index'));
        $response->assertSessionHas('faq_question_submitted', true);

        $this->assertDatabaseHas('faq_questions', [
            'name' => 'FAQ User',
            'email' => 'faq-user@example.com',
            'question' => 'What is the agenda for day 1?',
            'status' => 'pending',
        ]);

        Mail::assertQueued(HtmlBodyMail::class, function (HtmlBodyMail $mail): bool {
            return $mail->hasTo('faq-user@example.com');
        });
    }
}

