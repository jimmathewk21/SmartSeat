<?php

namespace Tests\Feature\Frontend;

use App\Services\AdminNotificationMailer;
use App\Support\FrontendPageAccessSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        FrontendPageAccessSettings::set(FrontendPageAccessSettings::defaults());
    }

    public function test_newsletter_subscription_creates_or_updates_contact_and_notifies_admin(): void
    {
        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->with('newsletter_subscription', [
                'email' => 'subscriber@example.com',
            ]);
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $this->post(route('frontend.newsletter.store'), [
            'email' => 'subscriber@example.com',
        ])->assertRedirect(route('frontend.trains.home'))
            ->assertSessionHas('newsletter_message', 'Thank you for subscribing to our newsletter.');

        $this->get(route('frontend.trains.home'))->assertOk()
            ->assertSeeText('Thank you for subscribing to our newsletter.');

        $this->assertDatabaseHas('my_contacts', [
            'official_email' => 'subscriber@example.com',
            'name' => 'Subscriber',
            'is_subscribed' => 1,
            'contact_status' => 'default',
        ]);
    }

    public function test_homepage_escapes_newsletter_confirmation_output(): void
    {
        $this->withSession(['newsletter_message' => '<script>alert("newsletter")</script>'])
            ->get(route('frontend.trains.home'))
            ->assertOk()
            ->assertSee('<script>alert("newsletter")</script>')
            ->assertDontSee('<script>alert("newsletter")</script>', false);
    }
}
