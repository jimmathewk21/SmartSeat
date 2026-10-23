@php
    $footerLogo = trim((string) ($frontendFooter['logo_url'] ?? 'images/logo.webp'));
    $footerLogoIsAbsolute = str_starts_with($footerLogo, 'http://') || str_starts_with($footerLogo, 'https://') || str_starts_with($footerLogo, '//') || str_starts_with($footerLogo, '/');
    $footerLogoSrc = $footerLogo !== '' && ($footerLogoIsAbsolute || is_file(public_path(ltrim($footerLogo, '/'))))
        ? ($footerLogoIsAbsolute ? $footerLogo : asset(ltrim($footerLogo, '/')))
        : asset('images/logo.webp');

    $socialLinks = collect($frontendFooter['social_links'] ?? [])->filter(function ($item) {
        return trim((string) ($item['url'] ?? '')) !== '';
    })->values();

    $upcomingEvents = collect($frontendFooter['upcoming_events'] ?? [])->filter(function ($item) {
        return trim((string) ($item['datetime'] ?? '')) !== ''
            || trim((string) ($item['name'] ?? '')) !== ''
            || trim((string) ($item['cta_text'] ?? '')) !== ''
            || trim((string) ($item['cta_link'] ?? '')) !== '';
    })->values();

    $quickLinks = collect($frontendFooter['quick_links'] ?? [])->filter(function ($item) {
        return trim((string) ($item['text'] ?? '')) !== '' || trim((string) ($item['url'] ?? '')) !== '';
    })->values();

    $companyLinks = collect($frontendFooter['company_links'] ?? [])->filter(function ($item) {
        return trim((string) ($item['text'] ?? '')) !== '' || trim((string) ($item['url'] ?? '')) !== '';
    })->values();

    $hasContactInfo = trim((string) ($frontendFooter['contact_title'] ?? '')) !== ''
        || trim((string) ($frontendFooter['contact_email'] ?? '')) !== ''
        || trim((string) ($frontendFooter['contact_address'] ?? '')) !== ''
        || trim((string) ($frontendFooter['contact_phone'] ?? '')) !== '';

    $bottomLinks = collect($frontendFooter['bottom_links'] ?? [])->filter(function ($item) {
        return trim((string) ($item['text'] ?? '')) !== '' || trim((string) ($item['url'] ?? '')) !== '';
    })->values();
@endphp

<footer class="text-light section-dark" style="background:#000; border-top:1px solid rgba(255,255,255,0.08);">
    <style>
        footer.section-dark {
            margin-top: 0 !important;
            padding-top: 10px !important;
        }
        .site-footer-bottom-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
        }
        .site-footer-logo {
            width: 220px;
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }
        @media (max-width: 767px) {
            .site-footer-topbar {
                gap: 16px;
            }
            .site-footer-logo {
                width: 190px;
            }
            .site-footer-social {
                display: flex;
                flex-wrap: wrap;
                gap: 10px 14px;
            }
            .site-footer-social a {
                margin-right: 0 !important;
            }
            .site-footer-bottom-links {
                margin-top: 10px;
                gap: 8px 14px;
            }
        }
    </style>
    <div class="container pb-5" style="padding-top:10px;">
        <div class="site-footer-topbar d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between border-bottom pb-4 mb-4" style="border-color: rgba(255,255,255,0.12) !important;">
            <div class="d-flex align-items-center mb-3 mb-lg-0">
                <img src="{{ $footerLogoSrc }}" alt="" class="site-footer-logo">
            </div>
            @if($socialLinks->isNotEmpty())
                <div class="social-icons site-footer-social">
                    @foreach($socialLinks as $social)
                        <a href="{{ $social['url'] }}" class="text-white me-3"><i class="{{ $social['icon'] ?? 'fa-solid fa-link' }}"></i></a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="row g-4">
            @if($upcomingEvents->isNotEmpty())
                <div class="col-lg-3 col-md-6">
                    @if(trim((string) ($frontendFooter['upcoming_events_title'] ?? '')) !== '')
                        <h4 class="fs-24 mb-3 text-white">{{ $frontendFooter['upcoming_events_title'] }}</h4>
                    @endif
                    @foreach($upcomingEvents as $index => $event)
                        <div class="{{ $index === 0 ? 'mb-4' : '' }}">
                            @if(trim((string) ($event['datetime'] ?? '')) !== '')
                                <div class="small" style="color:rgba(255,255,255,0.7);">{{ $event['datetime'] }}</div>
                            @endif
                            @if(trim((string) ($event['name'] ?? '')) !== '')
                                <div class="fw-bold text-white mb-2">{{ $event['name'] }}</div>
                            @endif
                            @if(trim((string) ($event['cta_text'] ?? '')) !== '')
                                <a href="{{ $event['cta_link'] ?? '#' }}" style="color:rgba(255,255,255,0.85);">
                                    {{ $event['cta_text'] }} <i class="fa fa-arrow-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if($quickLinks->isNotEmpty())
                <div class="col-lg-3 col-md-6">
                    @if(trim((string) ($frontendFooter['quick_links_title'] ?? '')) !== '')
                        <h4 class="fs-24 mb-3 text-white">{{ $frontendFooter['quick_links_title'] }}</h4>
                    @endif
                    <ul class="list-unstyled mb-0">
                        @foreach($quickLinks as $link)
                            <li class="mb-2">
                                <a href="{{ $link['url'] ?? '#' }}" style="color:rgba(255,255,255,0.85);">{{ $link['text'] ?? '' }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($companyLinks->isNotEmpty())
                <div class="col-lg-3 col-md-6">
                    @if(trim((string) ($frontendFooter['company_links_title'] ?? '')) !== '')
                        <h4 class="fs-24 mb-3 text-white">{{ $frontendFooter['company_links_title'] }}</h4>
                    @endif
                    <ul class="list-unstyled mb-0">
                        @foreach($companyLinks as $link)
                            <li class="mb-2">
                                <a href="{{ $link['url'] ?? '#' }}" style="color:rgba(255,255,255,0.85);">{{ $link['text'] ?? '' }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($hasContactInfo)
                <div class="col-lg-3 col-md-6">
                    @if(trim((string) ($frontendFooter['contact_title'] ?? '')) !== '')
                        <h4 class="fs-24 mb-3 text-white">{{ $frontendFooter['contact_title'] }}</h4>
                    @endif
                    @if(trim((string) ($frontendFooter['contact_email'] ?? '')) !== '')
                        <div class="d-flex align-items-start mb-3">
                            <span class="me-2 mt-1"><i class="{{ $frontendFooter['contact_email_icon'] ?? 'fa fa-envelope' }}"></i></span>
                            <span>{{ $frontendFooter['contact_email'] }}</span>
                        </div>
                    @endif
                    @if(trim((string) ($frontendFooter['contact_address'] ?? '')) !== '')
                        <div class="d-flex align-items-start mb-3">
                            <span class="me-2 mt-1"><i class="{{ $frontendFooter['contact_address_icon'] ?? 'fa fa-map-marker' }}"></i></span>
                            <span>{{ $frontendFooter['contact_address'] }}</span>
                        </div>
                    @endif
                    @if(trim((string) ($frontendFooter['contact_phone'] ?? '')) !== '')
                        <div class="d-flex align-items-start">
                            <span class="me-2 mt-1"><i class="{{ $frontendFooter['contact_phone_icon'] ?? 'fa fa-phone' }}"></i></span>
                            <span>{{ $frontendFooter['contact_phone'] }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if(trim((string) ($frontendFooter['copyright_text'] ?? '')) !== '' || $bottomLinks->isNotEmpty())
        <div style="border-top:1px solid rgba(255,255,255,0.12);">
            <div class="container pt-3 pb-4">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between">
                    <div class="mb-2 mb-lg-0" style="color:rgba(255,255,255,0.8);">{!! $frontendFooter['copyright_text'] ?? '' !!}</div>
                    @if($bottomLinks->isNotEmpty())
                        <div class="site-footer-bottom-links">
                            @foreach($bottomLinks as $index => $link)
                                <a href="{{ $link['url'] ?? '#' }}" style="color:rgba(255,255,255,0.85);">
                                    {{ $link['text'] ?? '' }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</footer>

<style>
    html,
    body {
        top: 0 !important;
        margin-top: 0 !important;
    }
    iframe.skiptranslate {
        display: none !important;
    }
    .goog-te-banner-frame.skiptranslate,
    .goog-te-menu-frame.skiptranslate,
    #goog-gt-tt,
    body > .skiptranslate {
        display: none !important;
    }
</style>
<script>
    function googleTranslateElementInit() {
        var mountNode = document.getElementById('google_translate_element');
        if (!mountNode || !window.google || !window.google.translate || !window.google.translate.TranslateElement) {
            return;
        }

        new window.google.translate.TranslateElement(
            {
                pageLanguage: 'en',
                autoDisplay: false,
            },
            'google_translate_element'
        );
    }

    (function () {
        var mountNode = document.getElementById('google_translate_element');
        if (!mountNode || !document.body) {
            return;
        }

        mountNode.classList.add('notranslate');

        if (window.google && window.google.translate && window.google.translate.TranslateElement) {
            googleTranslateElementInit();
            return;
        }

        var existingScript = document.querySelector('script[data-google-translate-script="1"]');
        if (existingScript) {
            return;
        }

        var script = document.createElement('script');
        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        script.async = true;
        script.defer = true;
        script.setAttribute('data-google-translate-script', '1');
        document.body.appendChild(script);
    })();
</script>
