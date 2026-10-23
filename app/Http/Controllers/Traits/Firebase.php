<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Log;

trait Firebase
{
    /**
     * Send Firebase App Notifications using `firebase_device_token` from the User model.
     *
     * @param array $data
     * @param string $deviceToken
     * @return bool
     */
    public function sendAppNotification(array $data, string $deviceToken): bool
    {
        if (empty($deviceToken)) {
            Log::error('Firebase Device Token is missing.');
            return false;
        }

        // Firebase Cloud Messaging API URL
        $url = 'https://fcm.googleapis.com/fcm/send';

        // Firebase Server Key (Get this from Firebase console > Project settings > Cloud Messaging)
        $serverKey = config('firebase.projects.app.server_key');

        if (empty($serverKey)) {
            Log::error('Firebase Server Key is missing in configuration.');
            return false;
        }

        // Notification payload
        $payload = [
            'to' => $deviceToken,
            'notification' => [
                'title' => $data['title'] ?? 'Notification Title',
                'body' => $data['body'] ?? 'Notification Body',
                'image' => $data['image'] ?? null,
            ],
            'data' => $data['data'] ?? [],
        ];

        // Send the notification using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log response for debugging
        Log::info('FCM Response: ' . $response);

        if ($httpCode === 200) {
            return true;
        } else {
            Log::error('Failed to send notification. HTTP Code: ' . $httpCode);
            return false;
        }
    }
}
