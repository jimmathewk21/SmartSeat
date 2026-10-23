<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsletterSubscriptionRequest;
use App\Models\MyContact;
use App\Services\AdminNotificationMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class FrontendNewsletterController extends Controller
{
    public function store(StoreNewsletterSubscriptionRequest $request): RedirectResponse
    {
        $email = strtolower(trim((string) $request->validated('email')));

        $contact = MyContact::query()->where('official_email', $email)->first();
        $name = $contact?->name ?: Str::headline(Str::before($email, '@'));
        if ($name === '') {
            $name = 'Newsletter Subscriber';
        }

        MyContact::query()->updateOrCreate(
            ['official_email' => $email],
            [
                'name' => $name,
                'is_subscribed' => true,
                'contact_status' => MyContact::STATUS_DEFAULT,
            ]
        );

        app(AdminNotificationMailer::class)->send('newsletter_subscription', [
            'email' => $email,
        ]);

        return redirect()
            ->to(route('frontend.home') . '#section-newsletter')
            ->with('newsletter_message', 'Thank you for subscribing to our newsletter.');
    }
}
