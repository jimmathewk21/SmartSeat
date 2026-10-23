<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Tests\TestCase;

class PasswordMailBrandingTest extends TestCase
{
    public function test_password_reset_notification_uses_igi_summit_subject(): void
    {
        $notification = new QueuedResetPassword('reset-token-123');
        $mailMessage = $notification->toMail(User::factory()->make([
            'email' => 'brand-check@example.com',
        ]));

        $this->assertSame('IGI Summit Password Reset Notification', $mailMessage->subject);
    }

    public function test_email_verification_notification_uses_igi_summit_subject(): void
    {
        $user = User::factory()->make([
            'email' => 'brand-check@example.com',
        ]);

        QueuedVerifyEmail::createUrlUsing(fn () => 'https://example.com/email/verify');

        try {
            $notification = new QueuedVerifyEmail();
            $mailMessage = $notification->toMail($user);

            $this->assertSame('IGI Summit Email Verification', $mailMessage->subject);
        } finally {
            QueuedVerifyEmail::$createUrlCallback = null;
        }
    }

    public function test_mail_header_is_igi_summit_branded(): void
    {
        $html = view('vendor.mail.html.header', [
            'url' => config('app.url'),
        ])->render();

        $this->assertStringContainsString('images/logo.webp', $html);
        $this->assertStringContainsString('IGI Summit', $html);
        $this->assertStringNotContainsString('laravel.com/img/notification-logo-v2.1.png', $html);
    }
}
