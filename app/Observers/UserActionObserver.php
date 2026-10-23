<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\DataChangeEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class UserActionObserver
{
    public function created(User $model)
    {
        $data = [
            'action' => 'created',
            'model_name' => 'User',
        ];

        // Retrieve admin users with non-null emails
        $users = User::whereHas('roles', function ($q) {
            $q->where('title', 'Admin');
        })
            ->whereNotNull('email') // Ensure the user has an email
            ->get();

        // Check if there are any users to notify
        if ($users->isEmpty()) {
            Log::warning('No admin users with valid emails found to notify about user creation.');
            return; // Exit if no valid users to notify
        }

        try {
            Notification::send($users, new DataChangeEmailNotification($data));
            Log::info('DataChangeEmailNotification sent to admin users.');
        } catch (\Exception $e) {
            Log::error('Failed to send DataChangeEmailNotification: ' . $e->getMessage());
        }
    }
}
