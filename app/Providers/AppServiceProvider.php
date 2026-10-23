<?php

namespace App\Providers;

use App\Models\Faq;
use App\Models\FrontendSeoTitle;
use App\Support\FrontendFooterSettings;
use App\Support\FrontendHeaderSettings;
use App\Support\FrontendHomeSettings;
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

        View::composer(['frontend.*', 'auth.*'], function ($view) {
            $requestPath = request()->path();
            $normalizedPath = FrontendSeoTitle::normalizePath($requestPath === '/' ? '/' : $requestPath);
            static $seoTitles = [];

            if (! array_key_exists($normalizedPath, $seoTitles)) {
                $seoTitles[$normalizedPath] = FrontendSeoTitle::titleForPath($normalizedPath);
            }

            $view->with('frontendSeoTitle', $seoTitles[$normalizedPath]);
        });

        View::composer('frontend.*', function ($view) {
            $requestPath = request()->path();
            $siteMeta = SiteMetaSettings::resolveForPath($requestPath === '/' ? '/' : '/' . ltrim($requestPath, '/'));
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
            $homeSettings = FrontendHomeSettings::get();

            $view->with('frontendHeader', FrontendHeaderSettings::get());
            $view->with('frontendFooter', FrontendFooterSettings::get());
            $view->with('frontendHomeBanner', $homeSettings);
            $view->with('siteMeta', $siteMeta);
            $view->with('homeFaqs', $homeFaqs);
        });
    }
}
