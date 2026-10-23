<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserQueuedNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_send_password_reset_notification_uses_queued_notification_class(): void
    {
        $user = User::factory()->create([
            'email' => 'reset-user@example.com',
        ]);

        Notification::fake();

        $user->sendPasswordResetNotification('reset-token-123');

        Notification::assertSentTo($user, QueuedResetPassword::class);
    }

    public function test_send_email_verification_notification_uses_queued_notification_class(): void
    {
        $user = User::factory()->create([
            'email' => 'verify-user@example.com',
        ]);

        Notification::fake();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, QueuedVerifyEmail::class);
    }
}
