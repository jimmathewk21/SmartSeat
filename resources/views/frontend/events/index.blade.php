<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $settings['hero_title'] ?? 'Events';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
    @endphp
    <title>{{ $frontendSeoTitle ?? $pageTitle }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <link id="colors" href="{{ asset('css/colors/scheme-01.css') }}" rel="stylesheet" type="text/css">
    <style>
        html, body { margin: 0 !important; padding: 0 !important; }
        #wrapper { display: flow-root; margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.events-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.events-page > footer { margin-top: 0 !important; }
        body.events-page {
            background: #f5f6f8;
            color: #111;
        }
        body.events-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.events-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.events-page .inside-page-hero-headline small {
            color: #000;
            font-size: .6em;
            font-weight: 400;
        }
        body.events-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 0;
            max-width: 1100px;
        }
        .events-world-section {
            position: relative;
            overflow: hidden;
            padding: 120px 0 90px;
        }
        .events-world-map {
            position: absolute;
            top: 140px;
            left: 50%;
            width: min(1411px, 100vw);
            aspect-ratio: 1411 / 702;
            height: auto;
            transform: translateX(-50%);
            background-image: url('{{ asset('images/worldmap.png') }}');
            background-repeat: no-repeat;
            background-position: center 28px;
            background-size: contain;
            opacity: 0.40;
            pointer-events: none;
            z-index: 0;
        }
        .events-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .events-section {
            margin-top: 48px;
        }
        .events-section-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 24px;
        }
        .events-section-title {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
            color: #171717;
            white-space: nowrap;
        }
        .events-section-line {
            height: 3px;
            flex: 1 1 auto;
            border-radius: 999px;
            background: linear-gradient(90deg, #7c4dff 0%, #b39dff 100%);
        }
        .events-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 28px 22px;
        }
        .events-grid-upcoming {
            grid-template-columns: minmax(0, 360px);
        }
        .event-card-visual {
            background: #fff;
            border: 1px solid #dadcf0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(46, 53, 91, .05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .event-card-image {
            display: block;
            width: 100%;
            aspect-ratio: 1 / 0.82;
            object-fit: contain;
            object-position: center;
            background: #f4f6fb;
        }
        .event-card-body {
            padding: 16px 16px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex: 1 1 auto;
        }
        .event-card-title {
            margin: 0 0 6px;
            font-size: clamp(26px, 2.2vw, 48px);
            line-height: 1.02;
            font-weight: 800;
            color: #111;
            letter-spacing: -.01em;
        }
        .event-card-text {
            margin: 0 0 14px;
            font-size: 14px;
            line-height: 1.3;
            color: #1f1f1f;
            min-height: 36px;
            max-width: 320px;
            font-weight: 500;
        }
        .event-card-facts {
            width: 100%;
            max-width: 360px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            column-gap: 18px;
            row-gap: 8px;
            text-align: left;
            margin-bottom: 12px;
        }
        .event-card-fact {
            min-width: 0;
            font-size: 13px;
            line-height: 1.4;
            color: #111;
            word-break: break-word;
            min-height: 38px;
        }
        .event-card-fact-label {
            font-weight: 800;
            margin-right: 4px;
        }
        .event-card-fact-value {
            font-weight: 500;
        }
        .event-card-actions {
            display: flex;
            gap: 12px;
            flex-wrap: nowrap;
            justify-content: center;
            margin-top: auto;
            width: 100%;
        }
        .event-card-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            flex: 1 1 0;
            padding: 12px 10px;
            border-radius: 14px;
            background: linear-gradient(90deg, #6d4fe8 0%, #4f41d9 100%);
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
            text-decoration: none;
            white-space: nowrap;
            line-height: 1;
        }
        .event-card-btn.is-disabled {
            opacity: .55;
            pointer-events: none;
        }
        .event-card-btn:hover,
        .event-card-btn:focus {
            color: #fff;
            text-decoration: none;
            background: linear-gradient(90deg, #7356ef 0%, #5648e2 100%);
            filter: none;
        }
        .events-testimonial-strip {
            margin-top: 70px;
        }
        .events-testimonial-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 20px;
        }
        .events-testimonial-title {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
            color: #171717;
            white-space: nowrap;
        }
        .events-testimonial-line {
            height: 3px;
            flex: 1 1 auto;
            border-radius: 999px;
            background: linear-gradient(90deg, #7c4dff 0%, #b39dff 100%);
        }
        .events-testimonial-scroll {
            overflow: hidden;
            position: relative;
            padding-top: 40px;
        }
        .events-testimonial-track {
            display: flex;
            gap: 16px;
            width: max-content;
            animation: eventTestimonialScroll 34s linear infinite;
        }
        .events-testimonial-scroll:hover .events-testimonial-track {
            animation-play-state: paused;
        }
        .events-testimonial-card {
            width: 240px;
            min-width: 240px;
            padding: 18px 16px 14px;
            border-radius: 24px;
            background: #f7f7fb;
            border: 1px solid #cfc8f0;
            box-shadow: none;
            color: #1b1b1b;
            overflow: visible;
        }
        .events-testimonial-top {
            display: grid;
            grid-template-columns: 60px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
            margin-bottom: 12px;
            padding-left: 10px;
        }
        .events-testimonial-top img {
            width: 55px;
            height: 55px;
            border-radius: 10px;
            object-fit: cover;
            object-position: center top;
            flex: 0 0 55px;
            box-shadow: none;
            margin-top: -40px;
        }
        .events-testimonial-name {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
        }
        .events-testimonial-company {
            font-size: 10px;
            color: #6c7285;
            line-height: 1.2;
            margin-top: 2px;
        }
        .events-testimonial-message {
            font-size: 11px;
            line-height: 1.45;
            color: #5a6175;
            min-height: 78px;
            display: -webkit-box;
            -webkit-line-clamp: 5;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .events-testimonial-rating {
            color: #f3b300;
            letter-spacing: 1px;
            font-size: 11px;
            margin-top: 10px;
        }
        @keyframes eventTestimonialScroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @media (max-width: 1199px) {
            .events-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 767px) {
            body.events-page .inside-page-hero-main {
                font-size: 38px;
            }
            body.events-page .inside-page-hero-description {
                font-size: 16px;
            }
            .events-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: .18;
            }
            .events-section-title,
            .events-testimonial-title {
                font-size: 24px;
                white-space: normal;
            }
            .events-grid,
            .events-grid-upcoming {
                grid-template-columns: 1fr;
            }
            .event-card-title {
                font-size: clamp(22px, 8vw, 34px);
            }
            .event-card-text {
                font-size: 13px;
                min-height: 34px;
            }
            .event-card-fact {
                font-size: 12px;
                min-height: 34px;
            }
            .event-card-btn {
                font-size: 12px;
                padding: 11px 10px;
            }
            .events-testimonial-card {
                width: 210px;
                min-width: 210px;
            }
        }
    </style>
</head>
<body class="events-page">
@php
    $assetHref = function (?string $path): string {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    };

    $renderCard = function ($event) use ($assetHref) {
        $imageSrc = $assetHref($event->image_url ?: 'images/misc/l1.webp');
        $description = \Illuminate\Support\Str::limit(trim((string) $event->description), 90);
        $galleryHref = trim((string) ($event->gallery_url ?? ''));
        $brochureHref = trim((string) ($event->brochure_url ?? ''));
        if ($galleryHref !== '' && ! str_starts_with($galleryHref, 'http://') && ! str_starts_with($galleryHref, 'https://') && ! str_starts_with($galleryHref, '//') && ! str_starts_with($galleryHref, '/')) {
            $galleryHref = asset(ltrim($galleryHref, '/'));
        }
        if ($brochureHref !== '' && ! str_starts_with($brochureHref, 'http://') && ! str_starts_with($brochureHref, 'https://') && ! str_starts_with($brochureHref, '//') && ! str_starts_with($brochureHref, '/')) {
            $brochureHref = asset(ltrim($brochureHref, '/'));
        }

        return view('frontend.events.partials.card', [
            'event' => $event,
            'imageSrc' => $imageSrc,
            'description' => $description,
            'galleryHref' => $galleryHref,
            'brochureHref' => $brochureHref,
        ])->render();
    };
@endphp
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="events-world-section">
        <div class="events-world-map" aria-hidden="true"></div>
        <div class="container">
            @include('frontend.partials.inner-page-hero', [
                'heroLabel' => $settings['hero_label'] ?? '[EVENTS + HIGHLIGHTS]',
                'heroTitle' => $settings['hero_title'] ?? 'What People Say',
                'heroSuffix' => $settings['hero_title_suffix'] ?? '',
                'heroDescription' => $settings['hero_description'] ?? '',
                'heroButtons' => [],
            ])

            <div class="events-section">
                <div class="events-section-header">
                    <h3 class="events-section-title">{{ $settings['upcoming_title'] ?? 'Upcoming Event' }}</h3>
                    <span class="events-section-line" aria-hidden="true"></span>
                </div>
                @if($upcomingEvent)
                    <div class="events-grid events-grid-upcoming">
                        {!! $renderCard($upcomingEvent) !!}
                    </div>
                @else
                    <div class="alert alert-light border">No upcoming published event is available right now.</div>
                @endif
            </div>

            <div class="events-section">
                <div class="events-section-header">
                    <h3 class="events-section-title">{{ $settings['past_title'] ?? 'Past Events' }}</h3>
                    <span class="events-section-line" aria-hidden="true"></span>
                </div>
                @if($pastEvents->isNotEmpty())
                    <div class="events-grid">
                        @foreach($pastEvents as $event)
                            {!! $renderCard($event) !!}
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-light border">No past published events are available yet.</div>
                @endif
            </div>

            @if(($testimonials ?? collect())->isNotEmpty())
                <div class="events-testimonial-strip">
                    <div class="events-testimonial-header">
                        <h4 class="events-testimonial-title">{{ $settings['testimonial_title'] ?? 'What People Say...' }}</h4>
                        <span class="events-testimonial-line" aria-hidden="true"></span>
                    </div>
                    <div class="events-testimonial-scroll">
                        <div class="events-testimonial-track">
                            @foreach([$testimonials, $testimonials] as $testimonialLoop)
                                @foreach($testimonialLoop as $testimonial)
                                    @php
                                        $testimonialPhotoSrc = $assetHref($testimonial->photo_url ?: 'images/team/1.webp');
                                        $testimonialMessage = trim((string) ($testimonial->message ?? ''));
                                        $testimonialStars = max(3, min(5, (int) ($testimonial->stars ?? 5)));
                                    @endphp
                                    <div class="events-testimonial-card">
                                        <div class="events-testimonial-top">
                                            <img src="{{ $testimonialPhotoSrc }}" alt="{{ $testimonial->name }}">
                                            <div>
                                                <div class="events-testimonial-name">{{ $testimonial->name }}</div>
                                                <div class="events-testimonial-company">{{ $testimonial->company ?: 'Company Name' }}</div>
                                            </div>
                                        </div>
                                        <div class="events-testimonial-message">{{ $testimonialMessage !== '' ? $testimonialMessage : 'A short attendee review will appear here once testimonial content is added.' }}</div>
                                        <div class="events-testimonial-rating" aria-label="{{ $testimonialStars }} out of 5 stars">{{ str_repeat('★', $testimonialStars) }}</div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

@include('frontend.partials.footer')
<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
</body>
</html>
