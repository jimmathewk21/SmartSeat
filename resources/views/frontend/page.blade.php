<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $page->title ?: ($siteMeta['title'] ?? 'Page');
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
    @endphp
    <title>{{ $frontendSeoTitle ?? $pageTitle }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="SmartSeat railway booking information." name="description">
    <meta content="" name="keywords">
    <meta content="" name="author">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <link id="colors" href="{{ asset('css/colors/scheme-01.css') }}" rel="stylesheet" type="text/css">
    <style>
        html, body { margin: 0 !important; padding: 0 !important; }
        #wrapper { display: flow-root; margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.dynamic-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.dynamic-page > footer { margin-top: 0 !important; }
        body.dynamic-page {
            background: #f8f9fa;
            color: #111;
        }
        .dynamic-page-world-section {
            position: relative;
            overflow: hidden;
        }
        .dynamic-page-world-map {
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
        .dynamic-page-world-section > .container {
            position: relative;
            z-index: 1;
        }
        body.dynamic-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.dynamic-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.dynamic-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.dynamic-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 20px;
            max-width: 1100px;
        }
        @media (max-width: 768px) {
            .dynamic-page-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: 0.18;
            }
            body.dynamic-page .inside-page-hero-main {
                font-size: 30px;
                line-height: 1.08;
            }
            body.dynamic-page .inside-page-hero-description {
                font-size: 15px;
                line-height: 1.7;
            }
        }
    </style>
</head>
<body class="dynamic-page">
<div id="wrapper">
    <div class="float-text show-on-scroll">
        <span><a href="#">Scroll to top</a></span>
    </div>
    <div class="scrollbar-v show-on-scroll"></div>
    <div id="de-loader"></div>

    @include('frontend.partials.header')

    <section class="bg-light text-dark pt-120 pb-80 dynamic-page-world-section">
        <div class="dynamic-page-world-map" aria-hidden="true"></div>
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-10">
                    @include('frontend.partials.inner-page-hero', [
                        'heroLabel' => $page->subtitle ?? '',
                        'heroTitle' => $page->title ?? '',
                        'heroSuffix' => $page->title_suffix ?? '',
                        'heroDescription' => '',
                        'heroButtons' => [],
                    ])
                    <div class="mt-4">
                        {!! $page->content !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@include('frontend.partials.footer')

<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script src="{{ asset('js/countdown-custom.js') }}"></script>
<script src="{{ asset('js/custom-marquee.js') }}"></script>
</body>
</html>
