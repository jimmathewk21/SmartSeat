<?php

namespace App\Providers;

use App\Models\Faq;
use App\Models\Event;
use App\Models\FrontendSeoTitle;
use App\Models\Sponsor;
use App\Models\Speaker;
use App\Models\Attraction;
use App\Models\Testimonial;
use App\Support\FrontendFooterSettings;
use App\Support\FrontendHeaderSettings;
use App\Support\FrontendHomeSettings;
use App\Support\SpeakerPageSettings;
use App\Support\SponsorPageSettings;
use App\Support\SiteMetaSettings;
use App\Services\SMSService;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Symfony\Component\Mime\Address;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SMSService::class, function ($app) {
            return new SMSService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        $alwaysCc = collect(explode(',', (string) config('mail.always_cc', '')))
            ->map(fn (string $address) => trim($address))
            ->filter(fn (string $address) => $address !== '' && filter_var($address, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();

        if ($alwaysCc !== []) {
            EventFacade::listen(MessageSending::class, function (MessageSending $event) use ($alwaysCc): void {
                $existingCc = collect($event->message->getCc() ?? [])
                    ->map(fn (Address $address) => strtolower($address->getAddress()))
                    ->all();

                foreach ($alwaysCc as $address) {
                    if (! in_array(strtolower($address), $existingCc, true)) {
                        $event->message->addCc($address);
                    }
                }
            });
        }

        View::composer(['frontend', 'frontend.*', 'auth.*'], function ($view) {
            $requestPath = request()->path();
            $normalizedPath = FrontendSeoTitle::normalizePath($requestPath === '/' ? '/' : $requestPath);
            static $seoTitles = [];

            if (! array_key_exists($normalizedPath, $seoTitles)) {
                $seoTitles[$normalizedPath] = FrontendSeoTitle::titleForPath($normalizedPath);
            }

            $view->with('frontendSeoTitle', $seoTitles[$normalizedPath]);
        });

        View::composer(['frontend', 'frontend.*'], function ($view) {
            $requestPath = request()->path();
            $siteMeta = SiteMetaSettings::resolveForPath($requestPath === '/' ? '/' : '/' . ltrim($requestPath, '/'));
            $homeTestimonials = Testimonial::query()
                ->select(['id', 'name', 'designation', 'company', 'message', 'photo_url', 'sort_order'])
                ->where('is_active', true)
                ->where('show_on_homepage', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            $homeSpeakers = Speaker::query()
                ->select(['id', 'name', 'designation', 'company_name', 'photo_url', 'sort_order', 'is_key_figure'])
                ->where('is_active', true)
                ->where('show_on_homepage', true)
                ->orderedForDisplay()
                ->get();
            $homeFaqs = Faq::query()
                ->select(['id', 'question', 'answer', 'sort_order'])
                ->where('is_active', true)
                ->where('show_on_homepage', true)
                ->whereHas('category', function ($query) {
                    $query->where('is_active', true);
                })
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            $homeSponsors = Sponsor::query()
                ->select(['id', 'name', 'logo_url', 'logo_size', 'sort_order', 'show_on_homepage', 'sponsor_category_id'])
                ->where('is_active', true)
                ->where('show_on_homepage', true)
                ->where(function ($query) {
                    $query->whereHas('sponsorCategories', function ($categoryQuery) {
                        $categoryQuery->where('is_active', true);
                    })->orWhereHas('sponsorCategory', function ($categoryQuery) {
                        $categoryQuery->where('is_active', true);
                    });
                })
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            $homeAttractions = Attraction::query()
                ->select(['id', 'name', 'image_url', 'sort_order', 'show_on_homepage'])
                ->where('is_active', true)
                ->where('show_on_homepage', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(4)
                ->get();

            $homeSettings = FrontendHomeSettings::get();

            $view->with('frontendHeader', FrontendHeaderSettings::get());
            $view->with('frontendFooter', FrontendFooterSettings::get());
            $view->with('frontendHomeBanner', $homeSettings);
            $view->with('speakerPageSettings', SpeakerPageSettings::get());
            $view->with('sponsorPageSettings', SponsorPageSettings::get());
            $view->with('siteMeta', $siteMeta);
            $view->with('homeTestimonials', $homeTestimonials);
            $view->with('homeSpeakers', $homeSpeakers);
            $view->with('homeFaqs', $homeFaqs);
            $view->with('homeSponsors', $homeSponsors);
            $view->with('homeAttractions', $homeAttractions);

            $featuredEventQuery = Event::query()
                ->select(['id', 'title', 'slug', 'venue', 'start_at', 'description'])
                ->bookableUpcoming()
                ->with(['ticketPlans' => function ($query) {
                    $query->select(['id', 'event_id', 'plan_index', 'sort_order', 'name', 'background_image_url', 'mrp_inr_paise', 'sale_inr_paise', 'mrp_usd_cents', 'sale_usd_cents', 'benefits', 'is_active', 'valid_till_text'])
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('id');
                }]);

            $featuredEvent = (clone $featuredEventQuery)
                ->orderBy('sort_order')
                ->orderBy('start_at')
                ->first();

            if ($featuredEvent) {
                $limit = 3;
                $featuredEvent->setRelation('ticketPlans', $featuredEvent->ticketPlans->take($limit));
            }

            $view->with('homeFeaturedEvent', $featuredEvent);
        });
    }
}
