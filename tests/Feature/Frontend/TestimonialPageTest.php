<?php

namespace Tests\Feature\Frontend;

use App\Http\Controllers\FrontendTestimonialController;
use App\Models\Country;
use App\Models\Event;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class TestimonialPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_testimonials_page_filters_by_country_and_uses_event_name_sort_label(): void
    {
        $this->enableTestimonialsPageAccess();

        $india = Country::query()->create([
            'name' => 'India',
            'short_code' => 'IN',
        ]);
        $usa = Country::query()->create([
            'name' => 'United States',
            'short_code' => 'US',
        ]);

        $eventA = Event::factory()->create(['title' => 'Event A']);
        $eventZ = Event::factory()->create(['title' => 'Event Z']);

        Testimonial::query()->create([
            'event_id' => $eventA->id,
            'country_id' => $india->id,
            'name' => 'Asha Patel',
            'designation' => 'Director',
            'company' => 'Alpha Corp',
            'message' => 'Great sessions and networking.',
            'experience_word' => 'Excellent',
            'stars' => 5,
            'year' => 2026,
            'testimonial_date' => '2026-02-10',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Testimonial::query()->create([
            'event_id' => $eventZ->id,
            'country_id' => $usa->id,
            'name' => 'John Carter',
            'designation' => 'Partner',
            'company' => 'Beta LLC',
            'message' => 'Helpful and practical.',
            'experience_word' => 'Useful',
            'stars' => 4,
            'year' => 2026,
            'testimonial_date' => '2026-02-11',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $request = Request::create('/testimonials', 'GET', [
            'country_id' => $india->id,
            'sort' => 'event_name',
        ]);

        $view = app(FrontendTestimonialController::class)->index($request);
        $data = $view->getData();

        $this->assertSame('event_name', $data['sort']);
        $this->assertCount(1, $data['testimonials']);
        $this->assertSame('Asha Patel', $data['testimonials']->first()->name);

        $legacyRequest = Request::create('/testimonials', 'GET', ['sort' => 'key_figures']);
        $legacyView = app(FrontendTestimonialController::class)->index($legacyRequest);
        $legacyData = $legacyView->getData();

        $this->assertSame('event_name', $legacyData['sort']);
    }

    public function test_testimonials_page_defaults_to_latest_id_first(): void
    {
        $this->enableTestimonialsPageAccess();

        $country = Country::query()->create([
            'name' => 'India',
            'short_code' => 'IN',
        ]);

        $event = Event::factory()->create(['title' => 'Event A']);

        $older = Testimonial::query()->create([
            'event_id' => $event->id,
            'country_id' => $country->id,
            'name' => 'Older Testimonial',
            'designation' => 'Director',
            'company' => 'Alpha Corp',
            'message' => 'Older entry.',
            'experience_word' => 'Useful',
            'stars' => 4,
            'year' => 2026,
            'testimonial_date' => '2026-02-10',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $newer = Testimonial::query()->create([
            'event_id' => $event->id,
            'country_id' => $country->id,
            'name' => 'Newer Testimonial',
            'designation' => 'Partner',
            'company' => 'Beta LLC',
            'message' => 'Newer entry.',
            'experience_word' => 'Excellent',
            'stars' => 5,
            'year' => 2026,
            'testimonial_date' => '2026-02-10',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $request = Request::create('/testimonials', 'GET');
        $view = app(FrontendTestimonialController::class)->index($request);
        $data = $view->getData();

        $this->assertSame('latest', $data['sort']);

        $orderedIds = $data['testimonials']->pluck('id')->all();
        $newerIndex = array_search($newer->id, $orderedIds, true);
        $olderIndex = array_search($older->id, $orderedIds, true);

        $this->assertNotFalse($newerIndex);
        $this->assertNotFalse($olderIndex);
        $this->assertLessThan($olderIndex, $newerIndex);
    }

    public function test_testimonial_feedback_requires_email_and_valid_captcha(): void
    {
        $this->enableTestimonialsPageAccess();

        $country = Country::query()->create([
            'name' => 'India',
            'short_code' => 'IN',
        ]);

        $response = $this->from(route('frontend.testimonials.index'))
            ->withSession(['testimonial_captcha_answer' => '7'])
            ->post(route('frontend.testimonials.store'), [
                'name' => 'Rahul Sharma',
                'company_name' => 'IP Summit',
                'country_id' => $country->id,
                'experience_word' => 'Great',
                'stars' => 5,
                'comment' => 'Very useful event.',
                'captcha' => '5',
            ]);

        $response->assertRedirect(route('frontend.testimonials.index'));
        $response->assertSessionHasErrors(['email', 'captcha']);
    }

    public function test_testimonial_feedback_with_picture_sends_admin_email_with_email_and_country(): void
    {
        $this->enableTestimonialsPageAccess();

        $country = Country::query()->create([
            'name' => 'India',
            'short_code' => 'IN',
        ]);

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->withArgs(function (string $templateKey, array $payload): bool {
                return $templateKey === 'testimonial_feedback'
                    && $payload['name'] === 'Rahul Sharma'
                    && $payload['company_name'] === 'IP Summit'
                    && $payload['email'] === 'rahul@example.com'
                    && $payload['country'] === 'India'
                    && $payload['experience_word'] === 'Great'
                    && $payload['stars'] === 5
                    && $payload['comment'] === 'Very useful event.'
                    && str_contains((string) $payload['picture_url'], 'uploads/testimonial-feedback/');
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $response = $this->withSession(['testimonial_captcha_answer' => '7'])
            ->post(route('frontend.testimonials.store'), [
                'name' => 'Rahul Sharma',
                'company_name' => 'IP Summit',
                'email' => 'rahul@example.com',
                'country_id' => $country->id,
                'picture_file' => UploadedFile::fake()->image('feedback.jpg'),
                'experience_word' => 'Great',
                'stars' => 5,
                'comment' => 'Very useful event.',
                'captcha' => '7',
            ]);

        $response->assertRedirect(route('frontend.testimonials.index') . '#testimonial-feedback');
        $response->assertSessionHas('message');
    }

    private function enableTestimonialsPageAccess(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['testimonials' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );
    }
}
