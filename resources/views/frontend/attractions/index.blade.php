<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $settings['hero_title'] ?? 'Things To Do';
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
        body.attractions-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.attractions-page > footer { margin-top: 0 !important; }
        body.attractions-page {
            background: #f5f6f8;
            color: #111;
        }
        body.attractions-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.attractions-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.attractions-page .inside-page-hero-headline small {
            color: #000;
            font-size: .6em;
            font-weight: 400;
        }
        body.attractions-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 0;
            max-width: 1100px;
        }
        .attractions-world-section {
            position: relative;
            overflow: hidden;
            padding: 120px 0 80px;
        }
        .attractions-world-map {
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
        .attractions-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .attractions-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin: 32px 0 44px;
        }
        .attractions-filter-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 208px;
            padding: 16px 20px;
            border-radius: 14px;
            border: 2px solid #7a47f4;
            background: transparent;
            color: #7a47f4;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
        }
        .attractions-filter-btn:hover,
        .attractions-filter-btn:focus {
            color: #fff;
            text-decoration: none;
            background: linear-gradient(90deg, #7a47f4 0%, #6241f0 100%);
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(92, 62, 219, .18);
        }
        .attractions-filter-btn.active {
            background: linear-gradient(90deg, #7a47f4 0%, #6241f0 100%);
            color: #fff;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(92, 62, 219, .30);
        }
        .attraction-row {
            display: grid;
            grid-template-columns: minmax(260px, 360px) 1fr;
            gap: 32px;
            align-items: start;
            margin-bottom: 48px;
        }
        .attraction-media {
            border: 1px solid #d8dbee;
            border-radius: 26px;
            overflow: hidden;
            background: #eceff4;
            min-height: 320px;
        }
        .attraction-media img {
            width: 100%;
            height: 100%;
            min-height: 320px;
            object-fit: cover;
            display: block;
        }
        .attraction-copy {
            padding-top: 6px;
        }
        .attraction-name {
            margin: 0 0 12px;
            color: #111;
            font-size: 42px;
            line-height: .95;
            font-weight: 800;
            text-transform: uppercase;
        }
        .attraction-text {
            margin: 0;
            color: #232323;
            font-size: 19px;
            line-height: 1.55;
            white-space: pre-line;
        }
        .attraction-links {
            display: flex;
            gap: 34px;
            flex-wrap: wrap;
            margin-top: 22px;
        }
        .attraction-link {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #111;
            text-decoration: none;
            min-width: 74px;
        }
        .attraction-link:hover,
        .attraction-link:focus {
            color: #6f46f5;
            text-decoration: none;
        }
        .attraction-link-icon {
            font-size: 42px;
            line-height: 1;
        }
        .attraction-link-text {
            font-size: 15px;
            font-weight: 500;
        }
        .attractions-sponsor-strip {
            margin-top: 70px;
        }
        .attractions-sponsor-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 20px;
        }
        .attractions-sponsor-title {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
            color: #171717;
            white-space: nowrap;
        }
        .attractions-sponsor-line {
            height: 3px;
            flex: 1 1 auto;
            border-radius: 999px;
            background: linear-gradient(90deg, #7c4dff 0%, #b39dff 100%);
        }
        .attractions-sponsor-scroll {
            overflow: hidden;
            position: relative;
        }
        .attractions-sponsor-track {
            display: flex;
            align-items: center;
            gap: 18px;
            width: max-content;
            animation: attractionSponsorScroll 30s linear infinite;
        }
        .attractions-sponsor-scroll:hover .attractions-sponsor-track {
            animation-play-state: paused;
        }
        .attractions-sponsor-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 190px;
            min-width: 190px;
            height: 92px;
            padding: 16px 18px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #edf0f5;
            box-shadow: 0 10px 28px rgba(17, 24, 39, 0.06);
        }
        .attractions-sponsor-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        @keyframes attractionSponsorScroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @media (max-width: 991px) {
            .attraction-row {
                grid-template-columns: 1fr;
                gap: 22px;
            }
            .attraction-name {
                font-size: 34px;
            }
        }
        @media (max-width: 767px) {
            body.attractions-page .inside-page-hero-main {
                font-size: 38px;
            }
            body.attractions-page .inside-page-hero-description {
                font-size: 16px;
            }
            .attractions-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: .18;
            }
            .attractions-filters {
                gap: 12px;
            }
            .attractions-filter-btn {
                min-width: calc(50% - 6px);
                width: calc(50% - 6px);
                padding: 14px 16px;
                font-size: 12px;
            }
            .attraction-name {
                font-size: 28px;
            }
            .attraction-text {
                font-size: 17px;
            }
            .attractions-sponsor-title {
                font-size: 24px;
                white-space: normal;
            }
            .attractions-sponsor-logo {
                width: 150px;
                min-width: 150px;
                height: 78px;
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body class="attractions-page">
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
@endphp
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="attractions-world-section">
        <div class="attractions-world-map" aria-hidden="true"></div>
        <div class="container">
            @include('frontend.partials.inner-page-hero', [
                'heroLabel' => $settings['hero_label'] ?? '[THINGS TO DO + LOCAL GUIDE]',
                'heroTitle' => $settings['hero_title'] ?? 'Explore Bengaluru',
                'heroSuffix' => $settings['hero_title_suffix'] ?? '',
                'heroDescription' => $settings['hero_description'] ?? '',
                'heroButtons' => [],
            ])

            <div class="attractions-filters">
                <a href="{{ route('frontend.attractions.index') }}" class="attractions-filter-btn {{ $selectedCategory === '' ? 'active' : '' }}">All</a>
                @foreach($categories as $category)
                    <a href="{{ route('frontend.attractions.index', ['category' => $category]) }}" class="attractions-filter-btn {{ $selectedCategory === $category ? 'active' : '' }}">{{ $category }}</a>
                @endforeach
            </div>

            @forelse($attractions as $attraction)
                @php
                    $imageSrc = $assetHref($attraction->image_url ?: 'images/misc/1.webp');
                @endphp
                <article class="attraction-row">
                    <div class="attraction-media">
                        <img src="{{ $imageSrc }}" alt="{{ $attraction->name }}">
                    </div>
                    <div class="attraction-copy">
                        <h3 class="attraction-name">{{ $attraction->name }}</h3>
                        <p class="attraction-text">{{ $attraction->detail }}</p>

                        <div class="attraction-links">
                            @if(!empty($attraction->website_url))
                                <a href="{{ $attraction->website_url }}" class="attraction-link" target="_blank" rel="noopener">
                                    <span class="attraction-link-icon"><i class="fa fa-globe"></i></span>
                                    <span class="attraction-link-text">Website</span>
                                </a>
                            @endif
                            @if(!empty($attraction->instagram_url))
                                <a href="{{ $attraction->instagram_url }}" class="attraction-link" target="_blank" rel="noopener">
                                    <span class="attraction-link-icon"><i class="fab fa-instagram"></i></span>
                                    <span class="attraction-link-text">Instagram</span>
                                </a>
                            @endif
                            @if(!empty($attraction->direction_url))
                                <a href="{{ $attraction->direction_url }}" class="attraction-link" target="_blank" rel="noopener">
                                    <span class="attraction-link-icon"><i class="fa fa-location-arrow"></i></span>
                                    <span class="attraction-link-text">Direction</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="alert alert-light border">No attractions found for this category yet.</div>
            @endforelse

            @if(($sponsors ?? collect())->isNotEmpty())
                <div class="attractions-sponsor-strip">
                    <div class="attractions-sponsor-header">
                        <h4 class="attractions-sponsor-title">Sponsors & Partners</h4>
                        <span class="attractions-sponsor-line" aria-hidden="true"></span>
                    </div>
                    <div class="attractions-sponsor-scroll">
                        <div class="attractions-sponsor-track">
                            @foreach([$sponsors, $sponsors] as $sponsorLoop)
                                @foreach($sponsorLoop as $sponsor)
                                    @php
                                        $sponsorLogo = trim((string) ($sponsor->logo_url ?? ''));
                                        $sponsorLogoSrc = $sponsorLogo === ''
                                            ? asset('images/logo.webp')
                                            : ((str_starts_with($sponsorLogo, 'http://') || str_starts_with($sponsorLogo, 'https://') || str_starts_with($sponsorLogo, '//') || str_starts_with($sponsorLogo, '/'))
                                                ? $sponsorLogo
                                                : asset(ltrim($sponsorLogo, '/')));
                                    @endphp
                                    <a href="{{ route('frontend.sponsors.show', $sponsor) }}" class="attractions-sponsor-logo" aria-label="{{ $sponsor->name }}">
                                        <img src="{{ $sponsorLogoSrc }}" alt="{{ $sponsor->name }}">
                                    </a>
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
