<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name') . ' Email Verification')
            ->line(Lang::get('Please click the button below to verify your email address for :app.', [
                'app' => config('app.name'),
            ]))
            ->action(Lang::get('Verify Email Address'), $url)
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }
}
