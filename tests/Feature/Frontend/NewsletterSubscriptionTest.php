<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_newsletter_subscription_creates_or_updates_contact_and_notifies_admin(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['home' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->with('newsletter_subscription', [
                'email' => 'subscriber@example.com',
            ]);
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $this->post(route('frontend.newsletter.store'), [
            'email' => 'subscriber@example.com',
        ])->assertRedirect(route('frontend.home') . '#section-newsletter');

        $this->assertDatabaseHas('my_contacts', [
            'official_email' => 'subscriber@example.com',
            'name' => 'Subscriber',
            'is_subscribed' => 1,
            'contact_status' => 'default',
        ]);
    }
}
