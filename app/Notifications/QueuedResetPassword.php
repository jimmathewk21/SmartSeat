<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;

    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name') . ' Password Reset Notification')
            ->line(Lang::get('You are receiving this email because we received a password reset request for your account at :app.', [
                'app' => config('app.name'),
            ]))
            ->action(Lang::get('Reset Password'), $url)
            ->line(Lang::get('This password reset link will expire in :count minutes.', [
                'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));
    }
}
