<?php

namespace Tests\Feature\Services;

use App\Mail\HtmlBodyMail;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use App\Support\AdminEmailTemplateSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use ReflectionClass;
use Tests\TestCase;

class AdminNotificationMailerQueueTest extends TestCase
{
    use DatabaseTransactions;

    public function test_send_queues_html_mail_for_admin_recipients_when_template_enabled(): void
    {
        $this->createAdminUser('admin1@example.com');
        $this->createAdminUser('admin2@example.com');

        Mail::fake();

        app(AdminNotificationMailer::class)->send('contact_submission', [
            'name' => 'Queue Tester',
            'email' => 'queue@example.com',
            'subject' => 'Need support',
            'message' => 'Please call back.',
        ]);

        Mail::assertQueued(HtmlBodyMail::class, function (HtmlBodyMail $mail): bool {
            return $mail->hasTo('admin1@example.com');
        });
        Mail::assertQueued(HtmlBodyMail::class, function (HtmlBodyMail $mail): bool {
            return $mail->hasTo('admin2@example.com');
        });
    }

    public function test_send_does_not_queue_mail_when_template_is_disabled(): void
    {
        $this->createAdminUser('admin@example.com');

        AdminEmailTemplateSettings::set([
            'templates' => [
                'contact_submission' => [
                    'enabled' => false,
                    'subject' => 'Disabled',
                    'body' => '<p>Disabled</p>',
                ],
            ],
        ]);

        Mail::fake();

        app(AdminNotificationMailer::class)->send('contact_submission', [
            'name' => 'Queue Tester',
            'email' => 'queue@example.com',
            'subject' => 'Need support',
            'message' => 'Please call back.',
        ]);

        Mail::assertNothingQueued();
    }

    public function test_admin_login_notification_is_sent_only_to_first_admin_by_id(): void
    {
        $this->createAdminUser('first-admin@example.com');
        $this->createAdminUser('second-admin@example.com');

        $expectedRecipient = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('title', 'Admin');
            })
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id')
            ->value('email');

        Mail::fake();

        app(AdminNotificationMailer::class)->send('admin_login', [
            'admin_name' => 'Main Admin',
            'admin_email' => 'main-admin@example.com',
            'login_ip' => '127.0.0.1',
            'login_at' => now()->format('d-M-Y h:i A'),
            'user_agent' => 'PHPUnit',
        ]);

        Mail::assertQueued(HtmlBodyMail::class, 1);
        Mail::assertQueued(HtmlBodyMail::class, function (HtmlBodyMail $mail) use ($expectedRecipient): bool {
            return $mail->hasTo($expectedRecipient);
        });
    }

    public function test_send_renders_user_approval_template_for_the_target_user(): void
    {
        Mail::fake();

        app(AdminNotificationMailer::class)->send('user_approval', [
            'name' => 'Approved User',
            'email' => 'approved@example.com',
            'mobile' => '9999999999',
            'phone' => '9999999999',
            'company_name' => 'Approved Co',
            'login_url' => route('login'),
            'support_email' => 'support@example.com',
            'approved_at' => '29-Jun-2026 09:15 AM',
        ], [], ['approved@example.com']);

        Mail::assertQueued(HtmlBodyMail::class, function (HtmlBodyMail $mail): bool {
            if (! $mail->hasTo('approved@example.com')) {
                return false;
            }

            [$subject, $body] = $this->mailPayload($mail);

            return str_contains($subject, 'Approved User')
                && str_contains($body, 'Approved User')
                && str_contains($body, 'approved@example.com')
                && str_contains($body, '9999999999')
                && str_contains($body, 'Approved Co')
                && str_contains($body, route('login'))
                && str_contains($body, 'support@example.com');
        });
    }

    private function createAdminUser(string $email): User
    {
        $role = Role::query()->firstOrCreate(['title' => 'Admin']);

        $user = User::factory()->create([
            'email' => $email,
        ]);
        $user->roles()->sync([$role->id]);

        return $user;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function mailPayload(HtmlBodyMail $mail): array
    {
        $reflection = new ReflectionClass($mail);

        $subject = $reflection->getProperty('subjectLine');
        $subject->setAccessible(true);

        $body = $reflection->getProperty('htmlBody');
        $body->setAccessible(true);

        return [(string) $subject->getValue($mail), (string) $body->getValue($mail)];
    }
}
