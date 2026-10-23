<!DOCTYPE html>
<html lang="en">

<head>
    @php
        $pageTitle = $siteMeta['title'] ?? 'AIvent - AI Event, Conference and Meetup Website Template';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
    @endphp
    <title>{{ $frontendSeoTitle ?? $pageTitle }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" >
    <meta content="AIvent" name="description" >
    <meta content="" name="keywords" >
    <meta content="" name="author" >
    <!-- CSS Files
    ================================================== -->
    <link rel="preload" href="css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="css/vendors.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="css/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <!-- color scheme -->
    <link rel="preload" id="colors" href="css/colors/scheme-01.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bootstrap-noscript">
        <link href="css/vendors.css" rel="stylesheet" type="text/css">
        <link href="css/style.css" rel="stylesheet" type="text/css">
        <link href="css/colors/scheme-01.css" rel="stylesheet" type="text/css">
    </noscript>
    <style>
        #section-speakers .speaker-card-image {
            height: 240px !important;
            object-fit: cover !important;
            object-position: center !important;
        }
        #section-speakers .speaker-card-name {
            font-size: 18px !important;
            line-height: 1.15 !important;
            margin: 8px 0 0 0 !important;
            color: #000 !important;
            text-align: center !important;
        }
        #section-speakers .lead {
            margin-bottom: 2rem !important;
        }
        #section-speakers .home-speakers-secondary-btn,
        #section-brands .home-sponsors-secondary-btn {
            background: #fff !important;
            color: #6f46f5 !important;
            border: 1px solid #cfc8f0 !important;
            box-shadow: none !important;
            opacity: 1 !important;
        }
        #section-speakers .home-speakers-secondary-btn:hover,
        #section-speakers .home-speakers-secondary-btn:focus,
        #section-brands .home-sponsors-secondary-btn:hover,
        #section-brands .home-sponsors-secondary-btn:focus {
            background: #f6f2ff !important;
            color: #5e35f2 !important;
            border-color: #b8a7f3 !important;
            text-decoration: none !important;
        }
        .agenda-video-shell {
            position: absolute;
            inset: 0;
            z-index: 1;
        }
        .agenda-video-shell .ratio {
            height: 100%;
        }
        .agenda-video-frame {
            width: 100%;
            height: 100%;
        }
        .agenda-video-controls {
            position: absolute;
            left: 16px;
            right: 16px;
            bottom: 16px;
            z-index: 3;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            pointer-events: none;
        }
        .agenda-video-controls .agenda-video-control-btn {
            pointer-events: auto;
            border: 0;
            border-radius: 999px;
            color: #fff;
            background: rgba(0, 0, 0, 0.55);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color .2s ease, transform .2s ease, opacity .2s ease;
        }
        .agenda-video-controls .agenda-video-control-btn:hover,
        .agenda-video-controls .agenda-video-control-btn:focus {
            background: rgba(0, 0, 0, 0.8);
            transform: translateY(-1px);
        }
        .agenda-video-controls .agenda-video-control-btn:focus {
            outline: 2px solid rgba(255, 255, 255, 0.75);
            outline-offset: 2px;
        }
        .agenda-video-control-btn.is-square {
            width: 44px;
            height: 44px;
        }
        .agenda-video-control-btn.is-pill {
            min-height: 44px;
            padding: 0 16px;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: .02em;
        }
        .agenda-video-control-btn .fa {
            font-size: 18px;
        }
        #section-hero .hero-main-content.has-third-line {
            padding-bottom: 30px;
        }
        #section-hero .hero-cta-row {
            margin-top: 22px;
        }
        @media (max-width: 991.98px) {
            #section-hero {
                min-height: auto !important;
                padding: 96px 0 34px !important;
            }
            #section-hero .abs.abs-centered.z-2.w-80 {
                position: relative !important;
                top: auto !important;
                left: auto !important;
                transform: none !important;
                width: 92% !important;
                margin: 0 auto !important;
            }
            #section-hero .home-countdown-strip.sm-hide {
                display: block !important;
            }
            #section-hero .abs.w-100.start-0.bottom-0.z-3 {
                position: relative !important;
                bottom: auto !important;
                margin-top: 28px;
            }
            #section-hero .home-countdown-strip {
                padding: 22px 18px !important;
            }
            #section-hero .home-countdown-strip .is-countdown .countdown-section:nth-child(4) {
                display: block !important;
            }
            #section-hero .home-countdown-strip .countdown-row {
                display: flex !important;
                flex-wrap: nowrap !important;
                justify-content: center !important;
                align-items: flex-start !important;
                padding: 0 !important;
            }
            #section-hero .home-countdown-strip .is-countdown .countdown-section {
                float: none !important;
                width: 24.5% !important;
            }
            #section-hero .home-countdown-strip .countdown-period {
                width: auto !important;
                font-size: 11px !important;
            }
            #section-hero .home-countdown-strip .row {
                row-gap: 18px !important;
                justify-content: center !important;
                text-align: center !important;
            }
            #section-hero .home-countdown-strip .col-lg-3,
            #section-hero .home-countdown-strip .col-lg-4 {
                text-align: center !important;
            }
            #section-hero .home-countdown-strip h2,
            #section-hero .home-countdown-strip h4 {
                text-align: center;
            }
            #section-hero .home-countdown-strip .d-flex {
                justify-content: center;
                align-items: center;
                text-align: center;
            }
            #section-hero .home-countdown-strip .col-lg-4:last-child .d-flex {
                justify-content: center !important;
                text-align: center !important;
                width: 100% !important;
            }
            #section-hero .home-countdown-strip .col-lg-4:last-child .ms-3 {
                margin-left: 10px !important;
                text-align: center !important;
            }
        }
        @media (max-width: 767.98px) {
            #section-hero {
                padding-top: 88px !important;
            }
            #section-hero .abs.w-100.start-0.bottom-0.z-3 {
                margin-top: 24px;
            }
            #section-speakers .speaker-card-image {
                height: 200px !important;
            }
            #section-speakers .speaker-card-name {
                font-size: 16px !important;
            }
            #section-hero .home-countdown-strip {
                padding: 18px 14px !important;
            }
            #section-hero .home-countdown-strip .countdown-amount {
                font-size: 34px !important;
            }
            #section-hero .home-countdown-strip .is-countdown .countdown-section {
                width: 24.5% !important;
            }
            #section-hero .home-countdown-strip .countdown-period {
                font-size: 10px !important;
            }
            #section-hero .home-countdown-strip .d-flex {
                justify-content: flex-start !important;
                text-align: left !important;
            }
            #section-hero .home-countdown-strip .ms-3 {
                text-align: left !important;
            }
            #section-about .me-lg-5.pe-lg-5.py-5.my-5 {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
                padding-top: 1.5rem !important;
                padding-bottom: 1.5rem !important;
            }
        }
        @media (max-width: 991.98px) {
            #section-hero .abs.abs-centered.z-2.w-80 {
                width: 92% !important;
            }
            #section-hero h1 {
                font-size: clamp(46px, 10vw, 88px) !important;
            }
            #section-hero .btn-main {
                margin-top: 10px;
            }
            #section-map iframe {
                min-height: 360px;
            }
            #section-about .rotate-animation {
                display: none !important;
            }
            #section-speakers .lead {
                margin-bottom: 1.25rem !important;
            }
            #section-venue .d-flex.justify-content-center {
                justify-content: flex-start !important;
                text-align: left !important;
            }
            #section-venue .d-flex.justify-content-center .ms-3 {
                text-align: left !important;
            }
            #section-counter .row > div {
                width: 50% !important;
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }
        }
        @media (max-width: 767.98px) {
            #section-hero .d-block.d-md-flex.justify-content-center {
                gap: 12px;
            }
            #section-hero .d-flex.justify-content-center.align-items-center.mx-4 {
                margin-left: 0 !important;
                margin-right: 0 !important;
                justify-content: center !important;
                text-align: center;
            }
            #section-hero .btn-main {
                display: block;
                width: 100%;
                margin: 10px 0 0 !important;
            }
            #section-map .rounded-1.overflow-hidden.h-100 {
                min-height: 280px !important;
            }
            #section-newsletter .btn,
            section .btn-lg {
                width: 100%;
            }
        }
        @media (min-width: 576px) and (max-width: 991.98px) and (orientation: portrait) {
            #section-speakers .speaker-grid > div {
                width: 50% !important;
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }
            #section-hero .home-countdown-strip .d-flex {
                justify-content: center !important;
                text-align: center !important;
            }
            #section-hero .home-countdown-strip .ms-3 {
                text-align: center !important;
                margin-left: 10px !important;
            }
            #testimonial-carousel .testimonial-slide .row.align-items-center {
                --bs-gutter-y: 1.5rem;
            }
            #testimonial-carousel .testimonial-slide .col-md-4 .relative.w-100 {
                max-width: 420px;
                margin: 0 auto;
                padding-right: 0 !important;
            }
            #testimonial-carousel .testimonial-slide .col-md-4 img {
                height: 320px;
                object-fit: cover;
                object-position: center;
            }
            #testimonial-carousel .testimonial-slide .col-md-4 .abs.bg-color {
                width: 62px !important;
                height: 62px !important;
                right: 10px !important;
                top: 10px !important;
                border-radius: 10px !important;
            }
            #testimonial-carousel .testimonial-slide .col-md-4 .abs.bg-color i {
                font-size: 28px !important;
                padding-top: 16px !important;
            }
            #testimonial-carousel .testimonial-slide .col-md-8 {
                text-align: center;
            }
            #testimonial-carousel .testimonial-slide .col-md-8 h3 {
                font-size: 30px !important;
                line-height: 1.35;
                margin-bottom: 14px !important;
            }
            #testimonial-carousel .d-flex.justify-content-end {
                justify-content: center !important;
            }
        }
    </style>

</head>

<body class="dark-scheme">

    <div id="wrapper">

        <div class="float-text show-on-scroll">
            <span><a href="#">Scroll to top</a></span>
        </div>
        <div class="scrollbar-v show-on-scroll"></div>

        @include('frontend.partials.header')

        @if(empty($frontendHomeBanner['hide_banner_section']))
        <section id="section-hero" class="section-dark no-top no-bottom text-light jarallax relative mh-800" data-video-src="{{ $frontendHomeBanner['video_src'] ?? 'mp4:video/2.mp4' }}">
            <div class="gradient-edge-top op-6 h-50 color"></div>
            <div class="gradient-edge-bottom"></div>
            <div class="sw-overlay op-8"></div>
            <div class="abs abs-centered z-2 w-80">
                <div class="container wow scaleIn" data-wow-duration="3s">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <div class="subtitle">{{ $frontendHomeBanner['subtitle'] ?? 'The Future of Intelligence' }}</div>
                            @php
                                $bannerTitleLine1 = trim((string) ($frontendHomeBanner['title_line_1'] ?? $frontendHomeBanner['title'] ?? 'AI Summit 2026'));
                                $bannerTitleLine2 = trim((string) ($frontendHomeBanner['title_line_2'] ?? ''));
                                $bannerTitleLine3 = trim((string) ($frontendHomeBanner['title_line_3'] ?? ''));
            @endphp
                            <div class="hero-main-content {{ $bannerTitleLine3 !== '' ? 'has-third-line' : '' }}">
                                <h1 class="fs-120 text-uppercase fs-sm-12vw mb-4 lh-1">{{ $bannerTitleLine1 }}@if($bannerTitleLine2 !== '')<br>{{ $bannerTitleLine2 }}@endif @if($bannerTitleLine3 !== '')<br>{{ $bannerTitleLine3 }}@endif</h1>

                                <div class="d-block d-md-flex justify-content-center">
                                    <div class="d-flex justify-content-center align-items-center mx-4">
                                        <i class="{{ $frontendHomeBanner['event_date_icon'] ?? 'fa fa-calendar' }} id-color me-3"></i>
                                        <h4 class="mb-0">{{ $frontendHomeBanner['event_date'] ?? 'October 1-5, 2026' }}</h4>
                                    </div>

                                    <div class="d-flex justify-content-center align-items-center mx-4">
                                        <i class="{{ $frontendHomeBanner['event_location_icon'] ?? 'fa fa-location-pin' }} id-color me-3"></i>
                                        <h4 class="mb-0">{{ $frontendHomeBanner['event_location'] ?? 'San Francisco, CA' }}</h4>
                                    </div>
                                </div>

                                <div class="hero-cta-row">
                                    @php
                                        $primaryButtonText = $frontendHomeBanner['primary_button_text'] ?? 'Get Tickets';
                                        $primaryButtonLink = $frontendHomeBanner['primary_button_link'] ?? '#section-tickets';
                                        $secondaryButtonText = trim((string) ($frontendHomeBanner['secondary_button_text'] ?? 'View Schedule'));
                                        $secondaryButtonLink = $frontendHomeBanner['secondary_button_link'] ?? '#section-schedule';
                                        $normalizedSecondary = strtolower(str_replace([' ', '-'], '', $secondaryButtonText));
                                        $isLoginSecondary = in_array($normalizedSecondary, ['signin', 'login', 'signin/register'], true);
                                    @endphp
                                    <a class="btn-main mx-2 fx-slide" href="{{ $primaryButtonLink }}"><span>{{ $primaryButtonText }}</span></a>
                                    @if(! auth()->check() || ! $isLoginSecondary)
                                        <a class="btn-main btn-line mx-2 fx-slide" href="{{ $secondaryButtonLink }}"><span>{{ $secondaryButtonText }}</span></a>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            @if(empty($frontendHomeBanner['hide_countdown_section']))
            <div class="abs w-100 start-0 bottom-0 z-3">
                <div class="container">
                    <div class="home-countdown-strip sm-hide border-white-op-3 p-40 py-4 rounded-1 bg-blur relative overflow-hidden wow fadeInUp">
                        <div class="gradient-edge-bottom color start-0 h-50 op-5"></div>
                        <div class="row g-4 justify-content-between align-items-center relative z-2">
                            <div class="col-lg-3">
                                <h2 class="mb-0">{{ $frontendHomeBanner['countdown_title'] ?? 'Hurry Up!' }}</h2>
                                <h4 class="mb-0">{{ $frontendHomeBanner['countdown_subtitle'] ?? 'Book Your Seat Now' }}</h4>
                            </div>
                            <div class="col-lg-4">
                                <div id="defaultCountdown" class="pt-2" data-until="{{ $frontendHomeBanner['countdown_until'] ?? '2026-10-01T08:00' }}"></div>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex">
                                    <i class="fs-60 icofont-google-map id-color"></i>
                                    <div class="ms-3">
                                        <h4 class="mb-0">{{ $frontendHomeBanner['countdown_location_line_1'] ?? '121 AI Blvd,' }}<br>{{ $frontendHomeBanner['countdown_location_line_2'] ?? 'San Francisco BCA 94107' }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </section>
        @endif

        @if(empty($frontendHomeBanner['hide_about_section']))
        <section id="section-about" class="bg-dark section-dark text-light">
            @php
                $aboutImage = $frontendHomeBanner['about_image'] ?? 'images/misc/c1.webp';
                $aboutImageSrc = (str_starts_with($aboutImage, 'http://') || str_starts_with($aboutImage, 'https://') || str_starts_with($aboutImage, '//') || str_starts_with($aboutImage, '/'))
                    ? $aboutImage
                    : asset($aboutImage);
            @endphp
            <div class="container">
                <div class="row  gx-5 align-items-center justify-content-between">
                    <div class="col-lg-6">
                          <div class="me-lg-5 pe-lg-5 py-5 my-5">
                              <div class="subtitle wow fadeInUp" data-wow-delay=".2s">{{ $frontendHomeBanner['about_subtitle'] ?? 'About the Event' }}</div>
                              <h2 class="wow fadeInUp" data-wow-delay=".4s">{{ $frontendHomeBanner['about_title'] ?? 'A Global Gathering of AI Innovators' }}</h2>
                              <p class="wow fadeInUp" data-wow-delay=".6s">{{ $frontendHomeBanner['about_description'] ?? '' }}</p>

                              <ul class="ul-check">
                                  <li class="wow fadeInUp" data-wow-delay=".8s">{{ $frontendHomeBanner['about_point_1'] ?? '' }}</li>
                                  <li class="wow fadeInUp" data-wow-delay=".9s">{{ $frontendHomeBanner['about_point_2'] ?? '' }}</li>
                                  <li class="wow fadeInUp" data-wow-delay="1s">{{ $frontendHomeBanner['about_point_3'] ?? '' }}</li>
                              </ul>

                          </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="wow scaleIn">
                            <img src="{{ $aboutImageSrc }}" class="w-100 rotate-animation" alt="">
                        </div>
                    </div>

                </div>
            </div>
        </section>
        @endif

        {{--
        <section class="section-dark p-0" aria-label="section">
            <div class="bg-color text-light d-flex py-4 lh-1 rot-2">
              <div class="de-marquee-list-1 wow fadeInLeft" data-wow-duration="3s">
                <span class="fs-60 mx-3">Next Intelligence</span>
                <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Future Now</span>
                 <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Empowering Innovation</span>
                 <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Smarter Tomorrow</span>
                 <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Think Forward</span>
                 <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Cognitive Shift</span>
                 <span class="fs-60 mx-3 op-2">/</span>
              </div>
            </div>

            <div class="bg-color-2 text-light d-flex py-4 lh-1 rot-min-1 mt-min-20">
              <div class="de-marquee-list-2 wow fadeInRight" data-wow-duration="3s">
                <span class="fs-60 mx-3">Next Intelligence</span>
                <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Future Now</span>
                 <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Empowering Innovation</span>
                 <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Smarter Tomorrow</span>
                 <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Think Forward</span>
                 <span class="fs-60 mx-3 op-2">/</span>
                <span class="fs-60 mx-3">Cognitive Shift</span>
                 <span class="fs-60 mx-3 op-2">/</span>
              </div>
            </div>
        </section>
        --}}
        @if(empty($frontendHomeBanner['hide_why_us_section']))
        <section id="section-why-attend" class="bg-light text-dark pt-80 pb-80">
            @php
                $whyUsCards = [];
                $whyUsCardDefaults = [
                    1 => ['image' => 'images/misc/s3.webp', 'title' => 'WHO WE ARE?', 'text' => ''],
                    2 => ['image' => 'images/misc/s4.webp', 'title' => 'WHAT WE DO?', 'text' => ''],
                    3 => ['image' => 'images/misc/s5.webp', 'title' => 'WHY US?', 'text' => ''],
                    4 => ['image' => 'images/misc/s6.webp', 'title' => 'OUR IMPACT?', 'text' => ''],
                ];

                for ($i = 1; $i <= 4; $i++) {
                    $whyUsImage = trim((string) ($frontendHomeBanner['why_us_card_' . $i . '_image'] ?? $whyUsCardDefaults[$i]['image']));
                    $whyUsImageSrc = (str_starts_with($whyUsImage, 'http://') || str_starts_with($whyUsImage, 'https://') || str_starts_with($whyUsImage, '//') || str_starts_with($whyUsImage, '/'))
                        ? $whyUsImage
                        : asset($whyUsImage);

                    $whyUsCards[] = [
                        'image' => $whyUsImageSrc,
                        'title' => $frontendHomeBanner['why_us_card_' . $i . '_title'] ?? $whyUsCardDefaults[$i]['title'],
                        'text' => $frontendHomeBanner['why_us_card_' . $i . '_text'] ?? $whyUsCardDefaults[$i]['text'],
                    ];
                }
            @endphp
            <div class="container">
                <div class="row mb-5">
                    <div class="col-lg-8 offset-lg-2 text-center">
                        <div class="subtitle" style="color: #000;">{{ $frontendHomeBanner['why_us_section_name'] ?? 'Why Us' }}</div>
                        <h2 class="mb-3" style="color: #000 !important; -webkit-text-fill-color: #000 !important; background: none !important; text-shadow: none !important; opacity: 1 !important;">{{ $frontendHomeBanner['why_us_title'] ?? 'WHAT ABOUT EVENT' }}</h2>
                        <p style="color: #000;">{{ $frontendHomeBanner['why_us_description'] ?? '' }}</p>
                    </div>
                </div>

                <div class="row g-4">
                    @foreach($whyUsCards as $whyUsCard)
                        <div class="col-lg-3 col-md-6 col-6">
                            <img src="{{ $whyUsCard['image'] }}" class="w-100 rounded-1 mb-3" alt="">
                            <h4 class="text-uppercase" style="color: #000;">{{ $whyUsCard['title'] }}</h4>
                            <p class="mb-0" style="color: #000;">{{ $whyUsCard['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
        @php
            $testimonialItems = $homeTestimonials ?? collect();
        @endphp
        @if(empty($frontendHomeBanner['hide_testimonials_section']) && $testimonialItems->count())
        <section class="bg-dark section-dark text-light pt-80 relative jarallax" aria-label="section">
            <img src="images/background/2.webp" class="jarallax-img" alt="">
            <div class="gradient-edge-top"></div>
            <div class="gradient-edge-bottom"></div>
            <div class="sw-overlay op-8"></div>
            <div class="container relative z-4" id="testimonial-carousel">
                <div class="row g-4 justify-content-center mb-4">
                    <div class="col-lg-8 text-center">
                        <div class="subtitle wow fadeInUp" data-wow-delay=".0s">{{ $frontendHomeBanner['testimonials_subtitle'] ?? 'Testimonials' }}</div>
                        <h2 class="wow fadeInUp" data-wow-delay=".2s">{{ $frontendHomeBanner['testimonials_title'] ?? 'What People Say' }}</h2>
                    </div>
                </div>
                @foreach($testimonialItems as $index => $testimonial)
                    @php
                        $photo = trim((string) ($testimonial->photo_url ?? ''));
                        $isAbsolutePhoto = str_starts_with($photo, 'http://')
                            || str_starts_with($photo, 'https://')
                            || str_starts_with($photo, '//')
                            || str_starts_with($photo, '/');
                        $isInvalidPhoto = in_array($photo, ['http://', 'https://', '//'], true);
                        $photoSrc = (! $photo || $isInvalidPhoto)
                            ? asset('images/testimonial/1.webp')
                            : ($isAbsolutePhoto ? $photo : asset(ltrim($photo, '/')));
                        $designationLine = trim(($testimonial->designation ?? '') . (($testimonial->company ?? '') ? ', ' . $testimonial->company : ''));
                    @endphp
                    <div class="testimonial-slide {{ $index === 0 ? '' : 'd-none' }}" data-slide-index="{{ $index }}">
                        <div class="row align-items-center g-5">
                            <div class="col-md-4">
                                <div class="relative w-100 d-inline-block pe-5">
                                    <div class="abs bg-color w-80px h-80px rounded-1 text-center end-0 z-2">
                                        <i class="icofont-quote-left text-white fs-40 d-block pt-3"></i>
                                    </div>
                                    <img src="{{ $photoSrc }}" class="w-100 rounded-1" alt="{{ $testimonial->name }}">
                                </div>
                            </div>

                            <div class="col-md-8">
                                <h3 class="fs-32 mb-4">"{{ $testimonial->message }}"</h3>
                                <span>{{ $testimonial->name }}</span>
                                @if($designationLine !== '')
                                    <div class="text-light mt-1">{{ $designationLine }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($testimonialItems->count() > 1)
                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <button type="button" class="btn-main btn-line px-3 py-2" id="testimonial-prev" aria-label="Previous testimonial">
                            <i class="fa fa-angle-left"></i>
                        </button>
                        <button type="button" class="btn-main btn-line px-3 py-2" id="testimonial-next" aria-label="Next testimonial">
                            <i class="fa fa-angle-right"></i>
                        </button>
                    </div>
                @endif

                @if(!empty($frontendHomeBanner['testimonials_button_text']) && !empty($frontendHomeBanner['testimonials_button_link']))
                    <div class="text-center mt-4">
                        <a class="btn-main fx-slide" href="{{ $frontendHomeBanner['testimonials_button_link'] }}"><span>{{ $frontendHomeBanner['testimonials_button_text'] }}</span></a>
                    </div>
                @endif
            </div>
        </section>
        @endif

        @php
            $speakerItems = $homeSpeakers ?? collect();
        @endphp
        @if(empty($frontendHomeBanner['hide_speakers_section']) && $speakerItems->count())
            <section id="section-speakers" class="bg-light text-dark pt-80 pb-80">
                <div class="container">
                    <div class="row g-4 justify-content-center">
                        <div class="col-lg-6 relative z-3">
                              <div class="text-center">
                                   <div class="subtitle wow fadeInUp" data-wow-delay=".0s" style="color: #000;">{{ $frontendHomeBanner['speakers_subtitle'] ?? 'Event Speakers' }}</div>
                                   <h2 class="wow fadeInUp" data-wow-delay=".2s" style="color: #000 !important; -webkit-text-fill-color: #000 !important; background: none !important; text-shadow: none !important; opacity: 1 !important;">{{ $frontendHomeBanner['speakers_title'] ?? 'Meet Our Speakers' }}</h2>
                                   @if(!empty($frontendHomeBanner['speakers_description']))
                                       <p class="lead wow fadeInUp mt-3 mb-0" data-wow-delay=".4s" style="color:#444;">{{ $frontendHomeBanner['speakers_description'] }}</p>
                                   @endif
                              </div>
                        </div>
                    </div>

                    <div class="row g-4 speaker-grid">
                        @foreach($speakerItems as $speaker)
                            @php
                                $speakerPhoto = trim((string) ($speaker->photo_url ?? ''));
                                $isAbsoluteSpeakerPhoto = str_starts_with($speakerPhoto, 'http://')
                                    || str_starts_with($speakerPhoto, 'https://')
                                    || str_starts_with($speakerPhoto, '//')
                                    || str_starts_with($speakerPhoto, '/');
                                $speakerPhotoSrc = $speakerPhoto
                                    ? ($isAbsoluteSpeakerPhoto ? $speakerPhoto : asset(ltrim($speakerPhoto, '/')))
                                    : asset('images/team/1.webp');
                            @endphp
                            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12">
                                <div class="rounded-1 overflow-hidden wow fadeIn scale-in-mask">
                                    <img src="{{ $speakerPhotoSrc }}" class="w-100 hover-scale-1-1 speaker-card-image" alt="{{ $speaker->name }}">
                                </div>
                                <h3 class="speaker-card-name">{{ $speaker->name }}</h3>
                            </div>
                        @endforeach
                    </div>
                    @php
                        $speakersViewAllText = trim((string) ($frontendHomeBanner['speakers_view_all_text'] ?? 'View All Speakers'));
                        $speakersViewAllLink = trim((string) ($frontendHomeBanner['speakers_view_all_link'] ?? '/speakers'));
                        $speakersBecomeText = trim((string) ($frontendHomeBanner['speakers_become_text'] ?? 'Become a Speaker'));
                        $speakersBecomeLink = trim((string) ($frontendHomeBanner['speakers_become_link'] ?? '/become-speaker'));
                    @endphp
                    @if($speakersViewAllText !== '' || $speakersBecomeText !== '')
                        <div class="text-center mt-4">
                            @if($speakersViewAllText !== '' && $speakersViewAllLink !== '')
                                <a class="btn-main me-2 mb-2" href="{{ $speakersViewAllLink }}">{{ $speakersViewAllText }}</a>
                            @endif
                            @if($speakersBecomeText !== '' && $speakersBecomeLink !== '')
                                <a class="btn-main btn-line mb-2 home-speakers-secondary-btn" href="{{ $speakersBecomeLink }}">{{ $speakersBecomeText }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @php
            $homeCounters = [
                [
                    'icon' => $frontendHomeBanner['counter_1_icon'] ?? 'fa fa-users',
                    'number' => $frontendHomeBanner['counter_1_number'] ?? '1200',
                    'text' => $frontendHomeBanner['counter_1_text'] ?? 'Participants',
                ],
                [
                    'icon' => $frontendHomeBanner['counter_2_icon'] ?? 'fa fa-cubes',
                    'number' => $frontendHomeBanner['counter_2_number'] ?? '1500',
                    'text' => $frontendHomeBanner['counter_2_text'] ?? 'Topics',
                ],
                [
                    'icon' => $frontendHomeBanner['counter_3_icon'] ?? 'fa fa-power-off',
                    'number' => $frontendHomeBanner['counter_3_number'] ?? '1700',
                    'text' => $frontendHomeBanner['counter_3_text'] ?? 'Sessions',
                ],
                [
                    'icon' => $frontendHomeBanner['counter_4_icon'] ?? 'fa fa-microphone',
                    'number' => $frontendHomeBanner['counter_4_number'] ?? '1800',
                    'text' => $frontendHomeBanner['counter_4_text'] ?? 'Speakers',
                ],
            ];
        @endphp
        @if(empty($frontendHomeBanner['hide_counter_section']))
        <section id="section-counter" class="bg-light text-dark pt-60 pb-60">
            <div class="container">
                <div class="row g-4 text-center">
                    @foreach($homeCounters as $counter)
                        <div class="col-lg-3 col-md-6">
                            <div class="py-3">
                                <i class="{{ $counter['icon'] }} fs-32 mb-3" style="color:#ff0000 !important;"></i>
                                <h2 class="mb-0" style="color:#000 !important; -webkit-text-fill-color:#000 !important; background:none !important; text-shadow:none !important; opacity:1 !important;">{{ $counter['number'] }}</h2>
                                <div class="mb-3" style="color:#666 !important; line-height: 1;">__</div>
                                <h6 class="mb-0 text-uppercase" style="color:#000 !important; -webkit-text-fill-color:#000 !important; background:none !important; text-shadow:none !important; opacity:1 !important;">{{ $counter['text'] }}</h6>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @php
            $agendaBg = trim((string) ($frontendHomeBanner['agenda_background_image'] ?? 'images/background/4.webp'));
            $agendaBgSrc = (str_starts_with($agendaBg, 'http://') || str_starts_with($agendaBg, 'https://') || str_starts_with($agendaBg, '//') || str_starts_with($agendaBg, '/'))
                ? $agendaBg
                : asset(ltrim($agendaBg, '/'));
        @endphp
        @if(empty($frontendHomeBanner['hide_agenda_section']))
        <section id="section-agenda" class="pt-0 pb-0">
            <div class="container-fluid px-0">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-6">
                        <div class="h-100 d-flex align-items-center justify-content-center text-light px-4 px-lg-5 py-5" style="background:#1f2437; padding-top:5rem !important; padding-bottom:5rem !important;">
                            <div class="text-end ms-lg-auto" style="width:100%; max-width: 620px;">
                                <h4 class="text-uppercase mb-1" style="letter-spacing:0.5px;">{{ $frontendHomeBanner['agenda_subtitle'] ?? 'Content Marketing Institute:' }}</h4>
                                <h2 class="text-uppercase mb-4">{{ $frontendHomeBanner['agenda_title'] ?? 'Executive Forum' }}</h2>
                                <div class="text-uppercase mb-4 id-color" style="font-weight:600;">{{ $frontendHomeBanner['agenda_meta'] ?? 'March 10-11, 2016 - Las Vegas, NV' }}</div>
                                <p class="mb-4" style="white-space:normal; overflow-wrap:anywhere; word-break:break-word;">{{ $frontendHomeBanner['agenda_description_1'] ?? '' }}</p>
                                <p class="mb-5" style="white-space:normal; overflow-wrap:anywhere; word-break:break-word;">{{ $frontendHomeBanner['agenda_description_2'] ?? '' }}</p>
                                <a href="{{ $frontendHomeBanner['agenda_button_link'] ?? '#' }}" class="btn-main btn-line px-5 text-uppercase">{{ $frontendHomeBanner['agenda_button_text'] ?? 'Download Brochure' }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="h-100 w-100 d-flex align-items-center justify-content-center position-relative" style="min-height: 420px; background-image: url('{{ $agendaBgSrc }}'); background-size: cover; background-position: center;">
                            <div class="agenda-video-shell" data-agenda-video-shell style="display:none;">
                                <div class="ratio ratio-16x9 h-100">
                                    <div id="agendaVideoFrame" class="agenda-video-frame" title="Agenda Video"></div>
                                </div>
                                <div class="agenda-video-controls">
                                    <button type="button" class="agenda-video-control-btn is-square" data-agenda-toggle-play aria-label="Pause video" title="Pause video">
                                        <i class="fa fa-pause"></i>
                                    </button>
                                    <button type="button" class="agenda-video-control-btn is-pill" data-agenda-close-video aria-label="Close video" title="Close video">
                                        <i class="fa fa-times"></i>
                                        <span>Close</span>
                                    </button>
                                </div>
                            </div>
                            <a href="#" class="d-inline-flex align-items-center justify-content-center rounded-circle text-white position-relative" data-agenda-video-url="{{ $frontendHomeBanner['agenda_video_url'] ?? '' }}" data-agenda-open-video style="width:90px; height:90px; border:2px solid rgba(255,255,255,0.6); background:rgba(0,0,0,0.25); font-size:32px; z-index:2;">
                                <i class="fa fa-play ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @php
            $brandLogos = [];
            $homeSponsorItems = $homeSponsors ?? collect();
            if ($homeSponsorItems->count() > 0) {
                foreach ($homeSponsorItems as $homeSponsorItem) {
                    $value = trim((string) ($homeSponsorItem->logo_url ?? ''));
                    if ($value === '') {
                        continue;
                    }
                    $brandLogos[] = [
                        'src' => (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//') || str_starts_with($value, '/'))
                            ? $value
                            : asset(ltrim($value, '/')),
                        'name' => trim((string) ($homeSponsorItem->name ?? 'Brand Logo')),
                        'size' => in_array(($homeSponsorItem->logo_size ?? 'md'), ['sm', 'md', 'lg'], true) ? $homeSponsorItem->logo_size : 'md',
                    ];
                }
            }

            if (count($brandLogos) === 0) {
                for ($i = 1; $i <= 8; $i++) {
                    $value = trim((string) ($frontendHomeBanner['brand_logo_' . $i] ?? ('images/logo-light/' . $i . '.webp')));
                    $brandLogos[] = [
                        'src' => (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//') || str_starts_with($value, '/'))
                            ? $value
                            : asset(ltrim($value, '/')),
                        'name' => 'Brand Logo',
                        'size' => 'md',
                    ];
                }
            }
        @endphp
        @if(empty($frontendHomeBanner['hide_brands_section']))
        <section id="section-brands" class="bg-light text-dark pt-80 pb-80">
            <div class="container">
                <div class="row align-items-center g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="subtitle wow fadeInUp" data-wow-delay=".0s" style="color:#000;">{{ $frontendHomeBanner['brands_subtitle'] ?? 'Our Official Sponsors' }}</div>
                        <h2 class="wow fadeInUp mb-0" data-wow-delay=".2s" style="color:#000 !important; -webkit-text-fill-color:#000 !important; background:none !important; text-shadow:none !important; opacity:1 !important;">{{ $frontendHomeBanner['brands_title'] ?? 'Trusted By Over 150 Organizations In More Than 50 Countries' }}</h2>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        @php
                            $brandsPrimaryText = trim((string) ($frontendHomeBanner['brands_button_text'] ?? 'Become a Sponsor'));
                            $brandsPrimaryLink = trim((string) ($frontendHomeBanner['brands_button_link'] ?? '#'));
                            $brandsViewAllText = trim((string) ($frontendHomeBanner['brands_view_all_text'] ?? 'View All Sponsors'));
                            $brandsViewAllLink = trim((string) ($frontendHomeBanner['brands_view_all_link'] ?? '/sponsors'));
                        @endphp
                        @if($brandsViewAllText !== '' && $brandsViewAllLink !== '')
                            <a href="{{ $brandsViewAllLink }}" class="btn-main me-2 mb-2">{{ $brandsViewAllText }}</a>
                        @endif
                        @if($brandsPrimaryText !== '' && $brandsPrimaryLink !== '')
                            <a href="{{ $brandsPrimaryLink }}" class="btn-main btn-line mb-2 home-sponsors-secondary-btn">{{ $brandsPrimaryText }}</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="container-fluid px-0">
                <div class="owl-carousel owl-theme home-brands-carousel">
                    @foreach($brandLogos as $logo)
                        @php
                            $homeLogoHeight = $logo['size'] === 'sm' ? 95 : ($logo['size'] === 'lg' ? 170 : 125);
                        @endphp
                        <div class="item px-2">
                            <div class="rounded-1 bg-white h-100 d-flex align-items-center justify-content-center p-4" style="min-height: 120px;">
                                <img src="{{ $logo['src'] }}" class="img-fluid" alt="{{ $logo['name'] }}" style="max-height: {{ $homeLogoHeight }}px; width: auto; object-fit: contain;">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @php
            $galleryImages = [];
            if (!empty($frontendHomeBanner['gallery_images']) && is_array($frontendHomeBanner['gallery_images'])) {
                foreach ($frontendHomeBanner['gallery_images'] as $imagePath) {
                    $value = trim((string) $imagePath);
                    if ($value === '') {
                        continue;
                    }
                    $galleryImages[] = (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//') || str_starts_with($value, '/'))
                        ? $value
                        : asset(ltrim($value, '/'));
                }
            }

            if (empty($galleryImages)) {
                for ($i = 1; $i <= 6; $i++) {
                    $value = trim((string) ($frontendHomeBanner['gallery_image_' . $i] ?? ('images/gallery/' . $i . '.webp')));
                    $galleryImages[] = (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//') || str_starts_with($value, '/'))
                        ? $value
                        : asset(ltrim($value, '/'));
                }
            }

            $galleryViewMoreText = trim((string) ($frontendHomeBanner['gallery_view_more_text'] ?? 'View More'));
            $galleryViewMoreLink = trim((string) ($frontendHomeBanner['gallery_view_more_link'] ?? '#'));
            $galleryViewMoreIsExternal = str_starts_with($galleryViewMoreLink, 'http://') || str_starts_with($galleryViewMoreLink, 'https://');
        @endphp
        @if(empty($frontendHomeBanner['hide_gallery_section']) && count($galleryImages))
        <section id="section-gallery" class="bg-dark section-dark pt-80 pb-80">
            <div class="container">
                <div class="row g-4 justify-content-center mb-3">
                    <div class="col-lg-8 text-center">
                        <div class="subtitle wow fadeInUp" data-wow-delay=".0s">{{ $frontendHomeBanner['gallery_section_name'] ?? 'Image Gallery' }}</div>
                        <h2 class="wow fadeInUp" data-wow-delay=".2s">{{ $frontendHomeBanner['gallery_main_title'] ?? 'Moments From Our Events' }}</h2>
                        <p class="lead wow fadeInUp" data-wow-delay=".4s">{{ $frontendHomeBanner['gallery_description'] ?? 'Explore highlights from past conferences, sessions, and networking experiences.' }}</p>
                    </div>
                </div>
            </div>
            <div class="container-fluid px-0">
                <div class="owl-carousel owl-theme home-gallery-carousel">
                    @foreach($galleryImages as $index => $galleryImage)
                        <div class="item">
                            <a href="#" data-gallery-open data-gallery-src="{{ $galleryImage }}">
                                <img src="{{ $galleryImage }}" alt="Gallery Image {{ $index + 1 }}" class="w-100" style="height:280px; object-fit:cover;">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @if($galleryViewMoreText !== '' && $galleryViewMoreLink !== '')
                <div class="container mt-5 text-center">
                    <a href="{{ $galleryViewMoreLink }}" class="btn-main text-uppercase" @if($galleryViewMoreIsExternal) target="_blank" rel="noopener noreferrer" @endif>{{ $galleryViewMoreText }}</a>
                </div>
            @endif
        </section>

        <div class="modal fade" id="galleryImageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-transparent border-0">
                    <div class="modal-body p-0 position-relative text-center">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                        <img id="galleryImageModalPreview" src="" alt="Gallery Preview" class="img-fluid rounded-1">
                    </div>
                </div>
            </div>
        </div>
        @endif

        @php
            $faqItems = $homeFaqs ?? collect();
            $faqViewMoreText = trim((string) ($frontendHomeBanner['faq_view_more_text'] ?? 'View More'));
            $faqViewMoreLink = trim((string) ($frontendHomeBanner['faq_view_more_link'] ?? '#'));
            $faqViewMoreIsExternal = str_starts_with($faqViewMoreLink, 'http://') || str_starts_with($faqViewMoreLink, 'https://');
        @endphp
        @if(empty($frontendHomeBanner['hide_faq_section']) && $faqItems->count())
            <section id="section-faq" class="bg-dark section-dark text-light">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="subtitle wow fadeInUp" data-wow-delay=".0s">{{ $frontendHomeBanner['faq_subtitle'] ?? 'Everything You Need to Know' }}</div>
                            <h2 class="wow fadeInUp" data-wow-delay=".2s">{{ $frontendHomeBanner['faq_title'] ?? 'Frequently Asked Questions' }}</h2>
                            @if($faqViewMoreText !== '' && $faqViewMoreLink !== '')
                                <div class="mt-4">
                                    <a href="{{ $faqViewMoreLink }}" class="btn-main text-uppercase" @if($faqViewMoreIsExternal) target="_blank" rel="noopener noreferrer" @endif>{{ $faqViewMoreText }}</a>
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-7">
                            <div class="accordion s2 wow fadeInUp">
                                <div class="accordion-section">
                                    @foreach($faqItems as $faq)
                                        @php
                                            $faqTabId = 'accordion-home-faq-' . $faq->id;
                                        @endphp
                                        <div class="accordion-section-title" data-tab="#{{ $faqTabId }}">
                                            {{ $faq->question }}
                                        </div>
                                        <div class="accordion-section-content" id="{{ $faqTabId }}">
                                            {!! nl2br(e($faq->answer)) !!}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{--
        <section id="section-schedule" class="bg-dark section-dark text-light">
            <div class="container">
              <div class="row g-4 gx-5 justify-content-center">
                <div class="col-lg-6 text-center">
                    <div class="subtitle s2 mb-3 wow fadeInUp" data-wow-delay=".0s">Event Schedule</div>
                    <h2 class="wow fadeInUp" data-wow-delay=".2s">5 Days of AI Excellence</h2>
                </div>
              </div>
              <div class="row g-4 gx-5 justify-content-center wow fadeInUp">
                <div class="col-lg-12">
                    <div class="de-tab plain">
                        <ul class="d-tab-nav mb-4 pb-4 d-flex justify-content-between">
                            <li class="active-tab">
                                <h3>Day 1</h3>
                                Oct 1, 2026
                            </li>
                            <li>
                                <h3>Day 2</h3>
                                Oct 2, 2026
                            </li>
                            <li>
                                <h3>Day 3</h3>
                                Oct 3, 2026
                            </li>
                            <li>
                                <h3>Day 4</h3>
                                Oct 5, 2026
                            </li>
                            <li>
                                <h3>Day 5</h3>
                                Oct 5, 2026
                            </li>
                        </ul>
                        <ul class="d-tab-content pt-3 wow fadeInUp">   
                            <!-- day 1 -->                        
                            <li>
                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            08:00 â€“ 10:00 AM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/1.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Joshua Henry</h5>
                                                    AI Research Lead, DeepTech Labs
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Opening Keynote â€“ The State of AI 2026</h3>
                                            <p class="fs-15 mb-0">Kick off the event with an insightful overview of where artificial intelligence is headed. Ava will explore breakthroughs, global shifts, and whatâ€™s next in deep learning, generative models, and AI ethics.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            12:00 â€“ 14:00 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/2.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Leila Zhang</h5>
                                                    VP of Machine Learning, Google
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Building Human-Centered AI Products</h3>
                                            <p class="fs-15 mb-0">This session covers how to design AI solutions that prioritize usability, fairness, and real-world impact. Bring your laptopâ€”hands-on UX exercises included.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            16:00 â€“ 18:00 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/3.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Carlos Rivera</h5>
                                                    Founder & CEO, NeuralCore
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: AI Policy & Regulation â€“ A Global Overview</h3>
                                            <p class="fs-15 mb-0">Learn how nations and organizations are approaching AI governance, including frameworks for data privacy, bias mitigation, and accountability in model deployment.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            20:00 â€“ 22:00 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/4.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Maria Gonzalez</h5>
                                                    Founder & CEO, SynthCore AI
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Building a Startup with AI at the Core</h3>
                                            <p class="fs-15 mb-0">Marco shares his journey launching an AI-first startup. Discover tips on tech stacks, team-building, funding, and scaling responsibly.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->
                            </li>

                            <!-- day 2 -->
                            <li>
                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            09:00 â€“ 10:30 AM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/5.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Leila Zhang</h5>
                                                    Head of AI Strategy, VisionFlow
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Ethical AI â€” From Theory to Practice</h3>
                                            <p class="fs-15 mb-0">Explore how leading companies are implementing fairness, accountability, and transparency in real-world AI systems across healthcare and finance.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            11:00 â€“ 12:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/6.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Lisa Zhang</h5>
                                                    AI Ethics Researcher, FairAI Group
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Bias in Data â€” Hidden Dangers in AI Pipelines</h3>
                                            <p class="fs-15 mb-0">Lisa dives deep into the causes of bias in training data and showcases methods to detect and mitigate harm before deployment.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            14:00 â€“ 15:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/7.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Markus Blom</h5>
                                                    CTO, SynthMind AI
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Generative Models Beyond Text</h3>
                                            <p class="fs-15 mb-0">A practical tour of the next generation of multimodal models generating images, video, and even 3D environments with AI.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            16:00 â€“ 17:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/8.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Priya Natarajan</h5>
                                                    Lead Engineer, CogniWare
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Workshop: Building AI-Powered Interfaces</h3>
                                            <p class="fs-15 mb-0">Learn how to embed conversational AI into web and mobile apps using modern open-source frameworks and API-first design.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                            </li>

                            <!-- day 3 -->
                            <li>
                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            09:00 â€“ 10:30 AM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/9.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Sofia Romero</h5>
                                                    ML Engineer, NeuronEdge
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Transformers in 2026 â€” What's Next?</h3>
                                            <p class="fs-15 mb-0">A technical session diving into the future of transformer architectures, memory optimization, and scaling challenges.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            11:00 â€“ 12:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/10.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">TomÃ¡s Eriksson</h5>
                                                    Founder, RealSim AI
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Synthetic Data Generation for Training</h3>
                                            <p class="fs-15 mb-0">TomÃ¡s shares tools and techniques for creating high-quality synthetic datasets that speed up training and reduce risk.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            14:00 â€“ 15:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/11.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Aisha Mensah</h5>
                                                    Senior AI Strategist, Datavine
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Panel: AI Regulation & Global Policy Outlook</h3>
                                            <p class="fs-15 mb-0">Top voices discuss the global AI policy landscape, upcoming legislation, and how it will shape the future of AI deployment.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            16:00 â€“ 17:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/12.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Leo Tanaka</h5>
                                                    Robotics Engineer, MetaForm
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Embodied AI in Robotics</h3>
                                            <p class="fs-15 mb-0">Discover how AI is powering next-gen robotics for manufacturing, logistics, and autonomous mobility through real-time interaction models.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                            </li>

                            <!-- day 4 -->
                            <li>
                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            09:00 â€“ 10:30 AM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/13.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Nina KÃ¶hler</h5>
                                                    Chief Product Officer, SynthOS
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: AI in Product Design â€” From Concept to Launch</h3>
                                            <p class="fs-15 mb-0">Nina shares how AI is revolutionizing product development, from ideation to real-time user feedback integration.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            11:00 â€“ 12:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/14.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Emmanuel Ruiz</h5>
                                                    CEO, NextCore Analytics
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Scaling AI Infrastructure for Enterprise</h3>
                                            <p class="fs-15 mb-0">Explore key considerations when deploying and managing scalable, secure, and cost-effective AI systems in the enterprise space.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            14:00 â€“ 15:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/15.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Isabelle Chen</h5>
                                                    Head of Language Models, LumoAI
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Multilingual AI â€” Global Challenges & Innovations</h3>
                                            <p class="fs-15 mb-0">How modern LLMs are overcoming linguistic bias, translation errors, and dialect diversity in global applications.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            16:00 â€“ 17:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/16.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Connor Walsh</h5>
                                                    Cloud AI Architect, SkyStack
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Workshop: Building AI Pipelines in the Cloud</h3>
                                            <p class="fs-15 mb-0">Hands-on session building a full AI workflow using serverless tech, vector databases, and model deployment strategies.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->
                                
                            </li>

                            <!-- day 5 -->
                            <li>
                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            09:00 â€“ 10:30 AM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/17.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Elena Greco</h5>
                                                    Ethics Advisor, Global AI Forum
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Ethical Design in AI â€” A Human-Centered Approach</h3>
                                            <p class="fs-15 mb-0">A deep dive into responsible AI, highlighting bias mitigation, fairness, transparency, and global implications of autonomous systems.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            11:00 â€“ 12:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/18.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Marcus Dlamini</h5>
                                                    Founder, EduAI Labs
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Personalized Learning with AI</h3>
                                            <p class="fs-15 mb-0">Explore how AI-driven platforms are transforming education with adaptive learning paths and dynamic content delivery.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="border-white-bottom-op-2 pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            14:00 â€“ 15:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/19.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Lara Nguyen</h5>
                                                    GenAI Director, NovaSynth
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Session: Creative AI â€” From Text to Video</h3>
                                            <p class="fs-15 mb-0">Lara demonstrates how generative AI is transforming content creation, with real-time demos in video, audio, and image generation.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                                <!-- schedule item begin -->
                                <div class="pb-5 mb-5">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-2">
                                            16:00 â€“ 17:30 PM
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <img src="images/team/20.webp" class="w-100px rounded-1 me-4" alt="">
                                                <div>
                                                    <h5 class="mb-0">Dr. Hassan Al-Mansour</h5>
                                                    Lead Data Scientist, FutureVision
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h3>Closing Keynote: AI & Humanity â€” Co-Evolution or Collapse?</h3>
                                            <p class="fs-15 mb-0">A visionary closing on AIâ€™s long-term trajectory, human-AI collaboration, and the existential questions we must answer now.</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- schedule item end -->

                            </li>

                        </ul>
                    </div>
                </div>
              </div>
            </div>
        </section>
        --}}

        @if(empty($frontendHomeBanner['hide_ticket_section']) && isset($homeFeaturedEvent) && $homeFeaturedEvent && $homeFeaturedEvent->ticketPlans->count())
        <section id="section-tickets" class="bg-dark section-dark text-light pt-80 relative jarallax" aria-label="section">
            <img src="images/background/7.webp" class="jarallax-img" alt="">
            <div class="gradient-edge-top"></div>
            <div class="gradient-edge-bottom"></div>
            <div class="sw-overlay op-7"></div>

            <div class="container relative z-2">
                <div class="row g-4 gx-5 justify-content-center">
                  <div class="col-lg-6 text-center">
                      <div class="subtitle s2 mb-3 wow fadeInUp" data-wow-delay=".0s">{{ $frontendHomeBanner['ticket_subtitle'] ?? 'Ticket Options' }}</div>
                      <h2 class="wow fadeInUp" data-wow-delay=".2s">{{ $frontendHomeBanner['ticket_title'] ?? 'Choose Your Pass' }}</h2>
                      <p class="lead wow fadeInUp" data-wow-delay=".4s">{{ $frontendHomeBanner['ticket_description'] ?? 'Select the perfect ticket for your needs and gain access to exclusive sessions, workshops, and more.' }}</p>
                  </div>
                </div>

                @php
                    $ticketCards = [];
                    $ticketCardStyles = ['', '', 's2'];
                    foreach ($homeFeaturedEvent->ticketPlans->values() as $idx => $plan) {
                        $i = $idx + 1;
                        $ticketImage = trim((string) ($plan->background_image_url ?: ($frontendHomeBanner['ticket_' . $i . '_image'] ?? 'images/logo.webp')));
                        $ticketImageSrc = (str_starts_with($ticketImage, 'http://') || str_starts_with($ticketImage, 'https://') || str_starts_with($ticketImage, '//') || str_starts_with($ticketImage, '/'))
                            ? $ticketImage
                            : asset(ltrim($ticketImage, '/'));

                        $buttonLink = route('frontend.events.tickets', ['event' => $homeFeaturedEvent->slug, 'plan' => $plan->id]);
                        $benefits = preg_split('/\r\n|\r|\n/', (string) ($plan->benefits ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                        if (empty($benefits)) {
                            $benefits = preg_split('/\r\n|\r|\n/', (string) ($frontendHomeBanner['ticket_' . $i . '_points'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                        }

                        $ticketCards[] = [
                            'style' => $ticketCardStyles[$i - 1] ?? '',
                            'image' => $ticketImageSrc,
                            'title' => $plan->name ?: ($frontendHomeBanner['ticket_' . $i . '_title'] ?? ''),
                            'price' => '₹' . number_format(((int) $plan->sale_inr_paise) / 100, 2),
                            'usd_price' => '$' . number_format(((int) $plan->sale_usd_cents) / 100, 2),
                            'date' => $plan->valid_till_text ?: ($frontendHomeBanner['ticket_' . $i . '_date'] ?? ''),
                            'points' => $benefits,
                            'button_text' => $frontendHomeBanner['ticket_' . $i . '_button_text'] ?? 'Buy Ticket',
                            'button_link' => $buttonLink,
                        ];
                    }
                @endphp
                <div class="row g-4 justify-content-center">
                    <div class="col-lg-12">
                        <div class="owl-carousel owl-theme owl-3-dots wow mask-right">
                            @foreach($ticketCards as $ticket)
                                <div class="item">
                                    <div class="d-ticket {{ $ticket['style'] }}" style="background-image: url('{{ $ticket['image'] }}'); background-size: cover; background-position: center;">
                                        <h2>{{ $ticket['title'] }}</h2>
                                        <h4 class="mb-1">{{ $ticket['price'] }}</h4>
                                        <div class="fs-18 fw-600 mb-4 text-white">{{ $ticket['usd_price'] }}</div>
                                        <div class="fs-14">{{ $ticket['date'] }}</div>
                                    </div>
                                    <div class="relative">
                                        <div class="py-4 z-2">
                                            <ul class="ul-check mb-4">
                                                @foreach($ticket['points'] as $point)
                                                    <li>{{ $point }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    <a class="btn-main fx-slide w-100" href="{{ $ticket['button_link'] }}"><span>{{ $ticket['button_text'] }}</span></a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @php
            $venueImages = [];
            if (!empty($frontendHomeBanner['venue_images']) && is_array($frontendHomeBanner['venue_images'])) {
                foreach ($frontendHomeBanner['venue_images'] as $imagePath) {
                    $value = trim((string) $imagePath);
                    if ($value === '') {
                        continue;
                    }
                    $venueImages[] = (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//') || str_starts_with($value, '/'))
                        ? $value
                        : asset(ltrim($value, '/'));
                }
            }

            if (empty($venueImages)) {
                foreach (['venue_image_1', 'venue_image_2'] as $legacyVenueImage) {
                    $value = trim((string) ($frontendHomeBanner[$legacyVenueImage] ?? ''));
                    if ($value === '') {
                        continue;
                    }
                    $venueImages[] = (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '//') || str_starts_with($value, '/'))
                        ? $value
                        : asset(ltrim($value, '/'));
                }
            }
            $venueViewMoreText = trim((string) ($frontendHomeBanner['venue_view_more_text'] ?? 'View More'));
            $venueViewMoreLink = trim((string) ($frontendHomeBanner['venue_view_more_link'] ?? '#'));
            $venueViewMoreIsExternal = str_starts_with($venueViewMoreLink, 'http://') || str_starts_with($venueViewMoreLink, 'https://');
        @endphp
        @if(empty($frontendHomeBanner['hide_venue_section']))
        <section id="section-venue" class="bg-dark section-dark text-light pt-80 relative jarallax" aria-label="section">
          <div class="container relative z-2">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-6 text-center">
                    <div class="subtitle wow fadeInUp" data-wow-delay=".0s">{{ $frontendHomeBanner['venue_subtitle'] ?? 'Event Location' }}</div>
                    <h2 class="wow fadeInUp" data-wow-delay=".2s">{{ $frontendHomeBanner['venue_title'] ?? 'Location & Venue' }}</h2>
                    <p class="lead wow fadeInUp" data-wow-delay=".6s">{{ $frontendHomeBanner['venue_description'] ?? '' }}</p>
                </div>
            </div>

            @if(count($venueImages))
                <div class="row g-4 mt-1">
                    <div class="col-12">
                        <div class="owl-carousel owl-theme home-venue-carousel">
                            @foreach($venueImages as $index => $venueImage)
                                <div class="item">
                                    <img src="{{ $venueImage }}" class="w-100 rounded-1 wow scale-in-mask" alt="Venue Image {{ $index + 1 }}" style="height:360px; object-fit:cover;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if($venueViewMoreText !== '' && $venueViewMoreLink !== '')
                    <div class="row mt-5">
                        <div class="col-12 text-center">
                            <a href="{{ $venueViewMoreLink }}" class="btn-main text-uppercase" @if($venueViewMoreIsExternal) target="_blank" rel="noopener noreferrer" @endif>{{ $venueViewMoreText }}</a>
                        </div>
                    </div>
                @endif
            @endif

            <div class="row g-4 mt-2">

                <div class="col-lg-4 col-md-6 mb-sm-30">
                    <div class="d-flex justify-content-center wow fadeInUp" data-wow-delay=".2s">
                        <i class="fs-60 id-color icofont-google-map"></i>
                        <div class="ms-3">
                            <h4 class="mb-0">{{ $frontendHomeBanner['venue_address_heading'] ?? 'Address' }}</h4>
                            <p>{{ $frontendHomeBanner['venue_address_text'] ?? '121 AI Blvd, San Francisco, CA 94107' }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-sm-30">
                    <div class="d-flex justify-content-center wow fadeInUp" data-wow-delay=".4s">
                        <i class="fs-60 id-color icofont-phone"></i>
                        <div class="ms-3">
                            <h4 class="mb-0">{{ $frontendHomeBanner['venue_phone_heading'] ?? 'Phone' }}</h4>
                            <p>{{ $frontendHomeBanner['venue_phone_text'] ?? 'Call: +1 123 456 789' }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-sm-30">
                    <div class="d-flex justify-content-center wow fadeInUp" data-wow-delay=".6s">
                        <i class="fs-60 id-color icofont-envelope"></i>
                        <div class="ms-3">
                            <h4 class="mb-0">{{ $frontendHomeBanner['venue_email_heading'] ?? 'Email' }}</h4>
                            <p>{{ $frontendHomeBanner['venue_email_text'] ?? 'contact@aivent.com' }}</p>
                        </div>
                    </div>
                </div>
            </div>

          </div>
        </section>
        @endif

        @if(empty($frontendHomeBanner['hide_map_section']))
        <section id="section-map" class="bg-light text-dark pt-80 pb-80">
            <div class="container">
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-5">
                        <div class="pe-lg-4">
                            <div class="subtitle" style="color: #000;">{{ $frontendHomeBanner['map_heading'] ?? 'Reach Us' }}</div>
                            <h2 style="color: #000 !important; -webkit-text-fill-color: #000 !important; background: none !important; text-shadow: none !important; opacity: 1 !important;">{{ $frontendHomeBanner['map_title'] ?? 'Get Direction to the Event Hall' }}</h2>
                            <p style="color: #000;">{{ $frontendHomeBanner['map_content'] ?? '' }}</p>

                            <div class="mt-4">
                                <h4 class="mb-2" style="color:#000;">{{ $frontendHomeBanner['map_address_title'] ?? 'Brighton Waterfront Hotel, Brighton, London' }}</h4>
                                <p class="mb-1" style="color:#000;">{{ $frontendHomeBanner['map_address_line_1'] ?? '1Hd- 50, 010 Avenue, NY 90001' }}</p>
                                <p class="mb-0" style="color:#000;">{{ $frontendHomeBanner['map_address_line_2'] ?? 'United States' }}</p>
                            </div>

                            <div class="row mt-4">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <h5 class="mb-2" style="color:#000;">{{ $frontendHomeBanner['map_tickets_title'] ?? 'Tickets info' }}</h5>
                                    <p class="mb-1" style="color:#000;">{{ $frontendHomeBanner['map_tickets_name'] ?? 'Name: Ronaldo Konig' }}</p>
                                    <p class="mb-1" style="color:#000;">{{ $frontendHomeBanner['map_tickets_phone'] ?? 'Phone: 009-21-55-595' }}</p>
                                    <p class="mb-0" style="color:#000;">{{ $frontendHomeBanner['map_tickets_email'] ?? 'Email: info@example.com' }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <h5 class="mb-2" style="color:#000;">{{ $frontendHomeBanner['map_programme_title'] ?? 'Programme Details' }}</h5>
                                    <p class="mb-1" style="color:#000;">{{ $frontendHomeBanner['map_programme_name'] ?? 'Name: Ronaldo Konig' }}</p>
                                    <p class="mb-1" style="color:#000;">{{ $frontendHomeBanner['map_programme_phone'] ?? 'Phone: 009-21-55-595' }}</p>
                                    <p class="mb-0" style="color:#000;">{{ $frontendHomeBanner['map_programme_email'] ?? 'Email: info@example.com' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="rounded-1 overflow-hidden h-100" style="min-height: 420px;">
                            {!! $frontendHomeBanner['map_iframe'] ?? '' !!}
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if(empty($frontendHomeBanner['hide_things_section']))
        <section id="section-things-to-do" class="bg-light text-dark pt-80 pb-80">
            <div class="container">
                <div class="row g-4 justify-content-center">
                    <div class="col-lg-8 text-center">
                        <div class="subtitle" style="color:#000;">{{ $frontendHomeBanner['things_subtitle'] ?? 'Things To Do' }}</div>
                        <h2 style="color:#000 !important; -webkit-text-fill-color:#000 !important; background:none !important; text-shadow:none !important; opacity:1 !important;">{{ $frontendHomeBanner['things_title'] ?? 'Discover Local Attractions' }}</h2>
                        @if(!empty($frontendHomeBanner['things_description']))
                            <p class="lead mt-3 mb-0" style="color:#444;">{{ $frontendHomeBanner['things_description'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="row g-4 mt-2">
                    @forelse(($homeAttractions ?? collect()) as $attraction)
                        @php
                            $cardImage = trim((string) ($attraction->image_url ?? 'images/misc/1.webp'));
                            $cardImageSrc = (str_starts_with($cardImage, 'http://') || str_starts_with($cardImage, 'https://') || str_starts_with($cardImage, '//') || str_starts_with($cardImage, '/'))
                                ? $cardImage
                                : asset(ltrim($cardImage, '/'));
                        @endphp
                        <div class="col-lg-3 col-md-6">
                            <div class="rounded-1 overflow-hidden">
                                <img src="{{ $cardImageSrc }}" class="w-100 rounded-1 mb-3" alt="{{ $attraction->name }}">
                            </div>
                            <h5 class="text-uppercase mb-0 text-center" style="color:#000;">{{ $attraction->name }}</h5>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-light border mb-0 text-center">No homepage attractions are available right now.</div>
                        </div>
                    @endforelse
                </div>

                <div class="row mt-5">
                    <div class="col-12 text-center">
                        <a class="btn-main fx-slide" href="{{ $frontendHomeBanner['things_button_link'] ?? '#' }}"><span>{{ $frontendHomeBanner['things_button_text'] ?? 'VIEW ALL ATTRACTIONS' }}</span></a>
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{--
        <section  id="section-faq" class="bg-dark section-dark text-light">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="subtitle wow fadeInUp" data-wow-delay=".0s">Everything You Need to Know</div>
                        <h2 class="wow fadeInUp" data-wow-delay=".2s">Frequently Asked Questions</h2>
                    </div>

                    <div class="col-lg-7">
                        <div class="accordion s2 wow fadeInUp">
                            <div class="accordion-section">
                                <div class="accordion-section-title" data-tab="#accordion-a1">
                                    What is the AI Summit 2026?
                                </div>
                                <div class="accordion-section-content" id="accordion-a1">
                                    The AI Summit 2026 is a premier event gathering leading AI experts, thought leaders, and innovators. It features keynotes, workshops, panels, and networking opportunities focusing on the latest advancements in artificial intelligence.
                                </div>

                                <div class="accordion-section-title" data-tab="#accordion-a2">
                                    When and where will the event be held?
                                </div>
                                <div class="accordion-section-content" id="accordion-a2">
                                    The AI Summit 2026 will take place from **[Event Dates]** at **[Event Location]**. More details about the venue and directions will be provided closer to the event.
                                </div>

                                <div class="accordion-section-title" data-tab="#accordion-a3">
                                    How can I register for the event?
                                </div>
                                <div class="accordion-section-content" id="accordion-a3">
                                    You can register for the AI Summit 2026 through our official website. Simply choose your ticket type and fill out the registration form.
                                </div>

                                <div class="accordion-section-title" data-tab="#accordion-a4">
                                    What ticket options are available?
                                </div>
                                <div class="accordion-section-content" id="accordion-a4">
                                    We offer a range of ticket options, including Standard, VIP, Full Access Pass, Student, and Virtual tickets. You can find more details about each ticket type on our [Tickets Page](#).
                                </div>

                                <div class="accordion-section-title" data-tab="#accordion-a5">
                                    Can I transfer my ticket to someone else?
                                </div>
                                <div class="accordion-section-content" id="accordion-a5">
                                    Tickets are non-transferable. If you are unable to attend, please contact our support team for assistance.
                                </div>

                                <div class="accordion-section-title" data-tab="#accordion-a6">
                                    Will there be virtual participation?
                                </div>
                                <div class="accordion-section-content" id="accordion-a6">
                                    Yes! For those who canâ€™t attend in person, we offer a **Virtual Ticket**. This provides access to live-streamed sessions, workshops, and networking opportunities online.
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
            </div>
        </section>
        --}}
        @if(empty($frontendHomeBanner['hide_newsletter_section']))
        <section id="section-newsletter" class="bg-dark section-dark text-light pt-80 relative jarallax" aria-label="section">
            <img src="images/background/3.webp" class="jarallax-img" alt="">
            <div class="gradient-edge-top"></div>
            <div class="gradient-edge-bottom"></div>
            <div class="sw-overlay op-8"></div>
          <div class="container relative z-2">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="wow fadeInUp" data-wow-delay=".2s">{{ $frontendHomeBanner['newsletter_title'] ?? 'Join the Future of Innovation' }}</h2>
                    @if(session('newsletter_message'))
                        <div class="alert alert-success border-0 shadow-sm mt-4 mb-0">{{ session('newsletter_message') }}</div>
                    @endif
                </div>
            </div>

            <form class="row justify-content-center" method="POST" action="{{ route('frontend.newsletter.store') }}">
              @csrf
              <div class="col-md-6 col-lg-4 mb-3 wow fadeInUp" data-wow-delay=".6s">
                <input type="email" name="email" class="form-control form-control-lg text-center" value="{{ old('email') }}" placeholder="{{ $frontendHomeBanner['newsletter_placeholder'] ?? 'Enter Your Email Address' }}" required>
              </div>
              <div class="col-auto mb-3 wow fadeInUp" data-wow-delay=".6s">
                <button type="submit" class="btn bg-color text-light btn-lg px-4">{{ $frontendHomeBanner['newsletter_button_text'] ?? 'SIGN UP' }}</button>
              </div>
            </form>
          </div>
        </section>
        @endif

    </div>

    @include('frontend.partials.footer')
    <!-- Javascript Files
    ================================================== -->
    <script src="js/vendors.js"></script>
    <script src="js/designesia.js"></script>
    <script src="js/countdown-custom.js"></script>
        <script src="js/custom-marquee.js"></script>
    <script>
        (function () {
            var root = document.getElementById('testimonial-carousel');
            if (!root) {
                return;
            }

            var slides = Array.prototype.slice.call(root.querySelectorAll('.testimonial-slide'));
            if (slides.length <= 1) {
                return;
            }

            var prevBtn = document.getElementById('testimonial-prev');
            var nextBtn = document.getElementById('testimonial-next');
            var activeIndex = 0;
            var autoplayMs = 5000;
            var autoplayTimer = null;

            function render(index) {
                slides.forEach(function (slide, i) {
                    slide.classList.toggle('d-none', i !== index);
                });
            }

            function goNext() {
                activeIndex = (activeIndex + 1) % slides.length;
                render(activeIndex);
            }

            function goPrev() {
                activeIndex = (activeIndex - 1 + slides.length) % slides.length;
                render(activeIndex);
            }

            function startAutoplay() {
                stopAutoplay();
                autoplayTimer = setInterval(goNext, autoplayMs);
            }

            function stopAutoplay() {
                if (autoplayTimer) {
                    clearInterval(autoplayTimer);
                    autoplayTimer = null;
                }
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    goNext();
                    startAutoplay();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    goPrev();
                    startAutoplay();
                });
            }

            root.addEventListener('mouseenter', stopAutoplay);
            root.addEventListener('mouseleave', startAutoplay);

            render(activeIndex);
            startAutoplay();
        })();

        (function () {
            var trigger = document.querySelector('[data-agenda-open-video]');
            var shell = document.querySelector('[data-agenda-video-shell]');
            var frame = document.getElementById('agendaVideoFrame');
            var playToggle = document.querySelector('[data-agenda-toggle-play]');
            var closeButton = document.querySelector('[data-agenda-close-video]');
            var player = null;
            var isPlaying = false;
            var isOpen = false;
            var apiPromise = null;

            if (!trigger || !shell || !frame) {
                return;
            }

            function toVideoId(url) {
                if (!url) {
                    return null;
                }

                try {
                    var parsed = new URL(url);
                    var host = parsed.hostname.replace('www.', '');
                    var id = '';

                    if (host === 'youtu.be') {
                        id = parsed.pathname.replace('/', '');
                    } else if (host === 'youtube.com' || host === 'm.youtube.com') {
                        if (parsed.pathname === '/watch') {
                            id = parsed.searchParams.get('v') || '';
                        } else if (parsed.pathname.indexOf('/shorts/') === 0) {
                            id = parsed.pathname.split('/')[2] || '';
                        } else if (parsed.pathname.indexOf('/embed/') === 0) {
                            id = parsed.pathname.split('/')[2] || '';
                        }
                    }

                    if (!id) {
                        return null;
                    }

                    return id;
                } catch (e) {
                    return null;
                }
            }

            function setControlsState() {
                if (!playToggle) {
                    return;
                }

                playToggle.setAttribute('aria-label', isPlaying ? 'Pause video' : 'Play video');
                playToggle.setAttribute('title', isPlaying ? 'Pause video' : 'Play video');
                playToggle.innerHTML = isPlaying ? '<i class="fa fa-pause"></i>' : '<i class="fa fa-play"></i>';
            }

            function updatePlayerState(state) {
                isPlaying = state === window.YT.PlayerState.PLAYING;
                setControlsState();
            }

            function loadYouTubeApi() {
                if (window.YT && window.YT.Player) {
                    return Promise.resolve(window.YT);
                }

                if (apiPromise) {
                    return apiPromise;
                }

                apiPromise = new Promise(function (resolve, reject) {
                    var settled = false;
                    var previous = window.onYouTubeIframeAPIReady;
                    var timeoutId = setTimeout(function () {
                        if (settled) {
                            return;
                        }

                        settled = true;
                        reject(new Error('YouTube IFrame API timed out'));
                    }, 10000);

                    window.onYouTubeIframeAPIReady = function () {
                        if (settled) {
                            return;
                        }

                        settled = true;
                        clearTimeout(timeoutId);

                        if (typeof previous === 'function') {
                            previous();
                        }

                        resolve(window.YT);
                    };

                    var script = document.createElement('script');
                    script.src = 'https://www.youtube.com/iframe_api';
                    script.async = true;
                    script.onerror = function () {
                        if (settled) {
                            return;
                        }

                        settled = true;
                        clearTimeout(timeoutId);
                        reject(new Error('Failed to load YouTube IFrame API'));
                    };
                    document.head.appendChild(script);
                });

                return apiPromise;
            }

            function destroyPlayer() {
                if (player && typeof player.destroy === 'function') {
                    player.destroy();
                }

                player = null;
                isPlaying = false;
                frame.innerHTML = '';
                setControlsState();
            }

            function openPlayer() {
                var videoUrl = trigger.getAttribute('data-agenda-video-url') || '';
                var videoId = toVideoId(videoUrl);

                if (!videoId) {
                    return;
                }

                shell.style.display = 'block';
                isOpen = true;
                trigger.style.display = 'none';
                trigger.style.pointerEvents = 'none';
                setControlsState();

                loadYouTubeApi().then(function () {
                    if (!isOpen) {
                        return;
                    }

                    destroyPlayer();

                    player = new window.YT.Player('agendaVideoFrame', {
                        videoId: videoId,
                        playerVars: {
                            autoplay: 1,
                            controls: 0,
                            rel: 0,
                            modestbranding: 1,
                            playsinline: 1,
                            fs: 1,
                            iv_load_policy: 3,
                            enablejsapi: 1,
                            origin: window.location.origin
                        },
                        events: {
                            onReady: function (event) {
                                event.target.mute();
                                event.target.playVideo();
                                updatePlayerState(window.YT.PlayerState.PLAYING);
                            },
                            onStateChange: function (event) {
                                updatePlayerState(event.data);
                                if (event.data === window.YT.PlayerState.PLAYING) {
                                    window.setTimeout(function () {
                                        if (isOpen && player && typeof player.unMute === 'function') {
                                            player.unMute();
                                        }
                                    }, 150);
                                } else if (event.data === window.YT.PlayerState.ENDED) {
                                    closePlayer();
                                } else if (event.data === window.YT.PlayerState.PAUSED) {
                                    trigger.style.display = 'none';
                                }
                            }
                        }
                    });
                }).catch(function () {
                    closePlayer();
                });
            }

            function togglePlayback() {
                if (!player || typeof player.getPlayerState !== 'function') {
                    return;
                }

                var state = player.getPlayerState();
                if (state === window.YT.PlayerState.PLAYING) {
                    player.pauseVideo();
                    updatePlayerState(window.YT.PlayerState.PAUSED);
                } else {
                    if (typeof player.unMute === 'function') {
                        player.unMute();
                    }
                    player.playVideo();
                    updatePlayerState(window.YT.PlayerState.PLAYING);
                }
            }

            function closePlayer() {
                isOpen = false;
                destroyPlayer();
                shell.style.display = 'none';
                trigger.style.display = '';
                trigger.style.pointerEvents = '';
            }

            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                openPlayer();
            });

            if (playToggle) {
                playToggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    togglePlayback();
                });
            }

            if (closeButton) {
                closeButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    closePlayer();
                });
            }

            setControlsState();
        })();

        (function () {
            var modalEl = document.getElementById('galleryImageModal');
            var preview = document.getElementById('galleryImageModalPreview');

            if (!modalEl || !preview || !window.bootstrap || !window.bootstrap.Modal) {
                return;
            }

            var modal = new window.bootstrap.Modal(modalEl);
            document.addEventListener('click', function (event) {
                var trigger = event.target.closest('[data-gallery-open]');
                if (!trigger) {
                    return;
                }
                event.preventDefault();
                var src = trigger.getAttribute('data-gallery-src') || '';
                if (!src) {
                    return;
                }
                preview.setAttribute('src', src);
                modal.show();
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                preview.setAttribute('src', '');
            });
        })();

        (function () {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.owlCarousel) {
                return;
            }
            var $carousel = window.jQuery('.home-gallery-carousel');
            if (!$carousel.length) {
                return;
            }

            $carousel.owlCarousel({
                items: 4,
                loop: true,
                margin: 0,
                nav: false,
                dots: false,
                autoplay: true,
                autoplayTimeout: 2500,
                autoplayHoverPause: true,
                smartSpeed: 700,
                responsive: {
                    0: { items: 1 },
                    576: { items: 2 },
                    992: { items: 3 },
                    1200: { items: 4 }
                }
            });
        })();

        (function () {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.owlCarousel) {
                return;
            }
            var $carousel = window.jQuery('.home-venue-carousel');
            if (!$carousel.length) {
                return;
            }

            $carousel.owlCarousel({
                items: 2,
                loop: true,
                margin: 20,
                nav: false,
                dots: false,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                smartSpeed: 700,
                responsive: {
                    0: { items: 1 },
                    768: { items: 2 }
                }
            });
        })();

        (function () {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.owlCarousel) {
                return;
            }
            var $carousel = window.jQuery('.home-brands-carousel');
            if (!$carousel.length) {
                return;
            }

            $carousel.owlCarousel({
                items: 4,
                loop: true,
                margin: 12,
                nav: false,
                dots: false,
                autoplay: true,
                autoplayTimeout: 2600,
                autoplayHoverPause: true,
                smartSpeed: 700,
                responsive: {
                    0: { items: 1 },
                    576: { items: 2 },
                    992: { items: 3 },
                    1200: { items: 4 }
                }
            });
        })();
    </script>

</body>


<!-- Mirrored from madebydesignesia.com/themes/aivent/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 18 Feb 2026 08:36:56 GMT -->
</html>
