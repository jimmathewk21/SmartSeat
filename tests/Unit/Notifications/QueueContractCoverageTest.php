<?php

namespace Tests\Unit\Notifications;

use App\Mail\HtmlBodyMail;
use App\Mail\PlainTextMail;
use App\Notifications\DataChangeEmailNotification;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

class QueueContractCoverageTest extends TestCase
{
    public function test_all_notification_and_mail_classes_used_for_delivery_implement_should_queue(): void
    {
        $notification = new DataChangeEmailNotification([
            'action' => 'created',
            'model_name' => 'User',
        ]);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertTrue(property_exists($notification, 'data'));
        $this->assertInstanceOf(ShouldQueue::class, new QueuedResetPassword('token-123'));
        $this->assertInstanceOf(ShouldQueue::class, new QueuedVerifyEmail());
        $this->assertInstanceOf(ShouldQueue::class, new HtmlBodyMail('Subject', '<p>Body</p>'));
        $this->assertInstanceOf(ShouldQueue::class, new PlainTextMail('Subject', 'Body'));
    }
}
