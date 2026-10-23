<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'Venue Hotels';
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
        body.venue-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.venue-page > footer { margin-top: 0 !important; }
        body.venue-page { background: #f5f6f8; color: #111; }
        body.venue-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.venue-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.venue-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.venue-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        .venue-world-section {
            position: relative;
            overflow: hidden;
        }
        .venue-world-map {
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
        .venue-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .venue-card {
            background: #fff;
            border: 1px solid #e4e7ef;
            border-radius: 14px;
            overflow: hidden;
            height: 100%;
        }
        .venue-card-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        .venue-card-body {
            padding: 18px 18px 20px;
        }
        .venue-stars {
            color: #f2b705;
            font-size: 15px;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        .venue-card-title {
            margin: 0 0 8px;
            color: #000;
            font-size: 27px;
            line-height: 1.2;
        }
        .venue-card-address {
            font-size: 15px;
            color: #334155;
            min-height: 48px;
        }
        @media (max-width: 768px) {
            .venue-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: 0.18;
            }
        }
    </style>
</head>
<body class="venue-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80 venue-world-section">
        <div class="venue-world-map" aria-hidden="true"></div>
        <div class="container">
            @include('frontend.partials.inner-page-hero', [
                'heroLabel' => $settings['hero_label'] ?? 'EVENT VENUE + HOTELS',
                'heroTitle' => $settings['hero_title'] ?? 'CHOOSE YOUR HOTEL',
                'heroSuffix' => $settings['hero_title_suffix'] ?? 'book your stay',
                'heroDescription' => $settings['hero_description'] ?? 'Select from partner hotels and continue to room reservation on the next step.',
                'heroButtons' => [],
            ])

            <div class="row g-4">
                @forelse($hotels as $hotel)
                    @php
                        $image = trim((string) ($hotel->hotel_image ?? ''));
                        $isAbsolute = str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '//') || str_starts_with($image, '/');
                        $imageSrc = $image !== '' ? ($isAbsolute ? $image : asset($image)) : asset('images/misc/1.webp');
                    @endphp
                    <div class="col-lg-4 col-md-6">
                        <div class="venue-card wow fadeInUp" data-wow-delay=".2s">
                            <img src="{{ $imageSrc }}" alt="{{ $hotel->name }}" class="venue-card-image">
                            <div class="venue-card-body">
                                @if((int) $hotel->stars > 0)
                                    <div class="venue-stars" aria-label="{{ (int) $hotel->stars }} star rating">
                                        @for($i = 0; $i < (int) $hotel->stars; $i++)
                                            <i class="fa fa-star" aria-hidden="true"></i>
                                        @endfor
                                    </div>
                                @endif
                                <h3 class="venue-card-title">{{ $hotel->name }}</h3>
                                <p class="venue-card-address mb-3">{{ $hotel->hotel_address ?: (($hotel->city ?: '') . (($hotel->country ?: '') ? ', ' . $hotel->country : '')) }}</p>
                                <a class="btn-main fx-slide" href="{{ route('frontend.venue.show', $hotel) }}"><span>View Hotel</span></a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border">{{ $settings['empty_state_text'] ?? 'No hotels available right now.' }}</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">{{ $hotels->links() }}</div>
        </div>
    </section>
</div>

@include('frontend.partials.footer')
<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
</body>
</html>

