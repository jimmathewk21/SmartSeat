<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $settings['hero_title'] ?? 'Contact Us';
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
        body.contact-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.contact-page > footer { margin-top: 0 !important; }
        body.contact-page { background: #f5f6f8; color: #111; }
        body.contact-page .inside-page-hero-headline { color: #000 !important; -webkit-text-fill-color: #000 !important; background: none !important; text-shadow: none !important; opacity: 1 !important; margin-bottom: 10px; line-height: 1.1; }
        body.contact-page .inside-page-hero-main { color: #000; font-size: 42px; font-weight: 700; line-height: 1; }
        body.contact-page .inside-page-hero-headline small { color: #000; font-size: .6em; font-weight: 400; }
        body.contact-page .inside-page-hero-description { font-size: 18px; line-height: 1.8; color: #111; margin-bottom: 0; max-width: 1120px; }
        .contact-world-section { position: relative; overflow: hidden; padding: 120px 0 80px; }
        .contact-world-map { position: absolute; top: 140px; left: 50%; width: min(1411px, 100vw); aspect-ratio: 1411 / 702; height: auto; transform: translateX(-50%); background-image: url('{{ asset('images/worldmap.png') }}'); background-repeat: no-repeat; background-position: center 28px; background-size: contain; opacity: 0.40; pointer-events: none; z-index: 0; }
        .contact-world-section > .container { position: relative; z-index: 1; }
        .contact-layout { display: grid; grid-template-columns: minmax(280px, 430px) minmax(340px, 1fr); gap: 40px; align-items: start; margin-top: 34px; }
        .contact-organizer-heading, .contact-info-heading { font-size: 22px; font-weight: 800; line-height: 1.2; color: #111; margin: 0 0 18px; }
        .contact-organizer-logo { max-width: 320px; width: 100%; height: auto; display: block; margin-bottom: 34px; }
        .contact-info-card { margin-bottom: 34px; }
        .contact-info-iconline { display: flex; align-items: flex-start; gap: 14px; }
        .contact-info-icon { flex: 0 0 auto; font-size: 28px; line-height: 1; color: #111; margin-top: 2px; }
        .contact-info-text { white-space: pre-line; color: #171717; font-size: 17px; line-height: 1.5; }
        .contact-social-links { display: flex; flex-wrap: wrap; gap: 12px; }
        .contact-social-link { width: 42px; height: 42px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #d7d8f3; color: #111; font-size: 22px; transition: transform .2s ease, color .2s ease, box-shadow .2s ease; }
        .contact-social-link:hover, .contact-social-link:focus { color: #6d47f5; transform: translateY(-2px); box-shadow: 0 12px 24px rgba(56, 52, 120, .08); text-decoration: none; }
        .contact-form-card { border: 2px solid rgba(17, 17, 17, .72); border-radius: 34px; padding: 42px 44px 34px; background: rgba(255, 255, 255, .22); backdrop-filter: blur(1px); }
        .contact-form-title { margin: 0 0 28px; font-size: 32px; font-weight: 900; line-height: 1; color: #111; text-transform: uppercase; }
        .contact-field { width: 100%; min-height: 56px; padding: 14px 22px; border-radius: 18px; border: 1px solid rgba(17, 17, 17, .28); background: rgba(255, 255, 255, .52); color: #111; font-size: 16px; outline: none; }
        .contact-field::placeholder { color: #222; opacity: .88; }
        .contact-field:focus { border-color: #6f47f5; box-shadow: 0 0 0 4px rgba(111, 71, 245, .12); }
        .contact-textarea { min-height: 230px; resize: vertical; }
        .contact-captcha-row { display: inline-flex; align-items: stretch; gap: 12px; flex-wrap: nowrap; max-width: 100%; margin-bottom: 1rem; }
        .contact-captcha-box { display: inline-flex; align-items: center; min-width: 180px; max-width: 100%; min-height: 56px; padding: 14px 16px; border-radius: 18px; border: 1px solid rgba(17, 17, 17, .28); background: rgba(255, 255, 255, .52); color: #111; font-size: 18px; font-weight: 700; line-height: 1.2; }
        .contact-captcha-answer { width: 180px; max-width: 100%; }
        .contact-submit-wrap { display: flex; justify-content: flex-end; margin-top: 26px; }
        .contact-submit-btn { min-width: 162px; padding: 15px 28px; border: 0; border-radius: 14px; background: linear-gradient(90deg, #7a47f4 0%, #5f3ff0 100%); color: #fff; font-size: 17px; font-weight: 800; letter-spacing: .03em; text-transform: uppercase; box-shadow: 0 18px 28px rgba(106, 67, 244, .22); }
        .contact-submit-btn:hover, .contact-submit-btn:focus { color: #fff; }
        .contact-testimonial-strip { margin-top: 78px; }
        .contact-testimonial-header { display: flex; align-items: center; gap: 18px; margin-bottom: 20px; }
        .contact-testimonial-title { margin: 0; font-size: 34px; font-weight: 800; line-height: 1; color: #171717; white-space: nowrap; }
        .contact-testimonial-line { height: 3px; flex: 1 1 auto; border-radius: 999px; background: linear-gradient(90deg, #7c4dff 0%, #b39dff 100%); }
        .contact-testimonial-scroll { overflow: hidden; position: relative; padding-top: 40px; }
        .contact-testimonial-track { display: flex; gap: 16px; width: max-content; animation: contactTestimonialScroll 34s linear infinite; }
        .contact-testimonial-scroll:hover .contact-testimonial-track { animation-play-state: paused; }
        .contact-testimonial-card { width: 240px; min-width: 240px; padding: 18px 16px 14px; border-radius: 24px; background: #f7f7fb; border: 1px solid #cfc8f0; box-shadow: none; color: #1b1b1b; overflow: visible; }
        .contact-testimonial-top { display: grid; grid-template-columns: 60px minmax(0, 1fr); gap: 12px; align-items: start; margin-bottom: 12px; padding-left: 10px; }
        .contact-testimonial-top img { width: 55px; height: 55px; border-radius: 10px; object-fit: cover; object-position: center top; flex: 0 0 55px; box-shadow: none; margin-top: -40px; }
        .contact-testimonial-name { font-size: 13px; font-weight: 700; line-height: 1.2; }
        .contact-testimonial-company { font-size: 10px; color: #6c7285; line-height: 1.2; margin-top: 2px; }
        .contact-testimonial-message { font-size: 11px; line-height: 1.45; color: #5a6175; min-height: 78px; display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical; overflow: hidden; }
        .contact-testimonial-rating { color: #f3b300; letter-spacing: 1px; font-size: 11px; margin-top: 10px; }
        @keyframes contactTestimonialScroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @media (max-width: 991px) { .contact-layout { grid-template-columns: 1fr; } .contact-form-card { padding: 34px 30px 28px; } }
        @media (max-width: 767px) { body.contact-page .inside-page-hero-main { font-size: 38px; } body.contact-page .inside-page-hero-description { font-size: 16px; } .contact-world-map { top: 160px; width: 118vw; aspect-ratio: 1411 / 702; height: auto; opacity: .18; } .contact-form-card { border-radius: 24px; padding: 24px 20px 22px; } .contact-form-title { font-size: 26px; } .contact-testimonial-title { font-size: 24px; white-space: normal; } .contact-testimonial-card { width: 210px; min-width: 210px; } .contact-captcha-row { display: flex; flex-wrap: wrap; width: 100%; } .contact-captcha-answer { width: 100%; } .contact-submit-wrap { justify-content: stretch; } .contact-submit-btn { width: 100%; } }
    </style>
</head>
<body class="contact-page">
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
    $socialLinks = [
        ['url' => $settings['social_linkedin_url'] ?? '', 'icon' => 'fab fa-linkedin', 'label' => 'LinkedIn'],
        ['url' => $settings['social_facebook_url'] ?? '', 'icon' => 'fab fa-facebook-square', 'label' => 'Facebook'],
        ['url' => $settings['social_x_url'] ?? '', 'icon' => 'fa-brands fa-x-twitter', 'label' => 'X'],
        ['url' => $settings['social_instagram_url'] ?? '', 'icon' => 'fab fa-instagram', 'label' => 'Instagram'],
        ['url' => $settings['social_whatsapp_url'] ?? '', 'icon' => 'fab fa-whatsapp', 'label' => 'WhatsApp'],
    ];
@endphp
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="contact-world-section">
        <div class="contact-world-map" aria-hidden="true"></div>
        <div class="container">
            @include('frontend.partials.inner-page-hero', [
                'heroLabel' => $settings['hero_label'] ?? '[Contact US]',
                'heroTitle' => $settings['hero_title'] ?? 'Get In Touch',
                'heroSuffix' => $settings['hero_title_suffix'] ?? '',
                'heroDescription' => $settings['hero_description'] ?? '',
                'heroButtons' => [],
            ])

            @if(session('message'))
                <div class="alert alert-success border-0 shadow-sm mt-4 mb-0">{{ session('message') }}</div>
            @endif

            <div class="contact-layout">
                <div class="contact-info-block">
                    <div class="contact-organizer-heading">SmartSeat Support</div>

                    @if(trim((string) ($settings['address_text'] ?? '')) !== '')
                    <div class="contact-info-card">
                        <div class="contact-info-iconline">
                            <div class="contact-info-icon"><i class="fa fa-map-marker-alt"></i></div>
                            <div>
                                <div class="contact-info-heading">{{ $settings['address_heading'] ?? 'Office Address:' }}</div>
                                <div class="contact-info-text">{{ $settings['address_text'] ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(trim((string) ($settings['contact_email'] ?? '')) !== '' || trim((string) ($settings['contact_phone'] ?? '')) !== '')
                    <div class="contact-info-card">
                        <div class="contact-info-iconline">
                            <div class="contact-info-icon"><i class="fa fa-envelope-open"></i></div>
                            <div>
                                <div class="contact-info-heading">{{ $settings['contact_heading'] ?? 'Contact Details:' }}</div>
                                <div class="contact-info-text">@if(trim((string) ($settings['contact_email'] ?? '')) !== '')Email: {{ $settings['contact_email'] }}@endif@if(trim((string) ($settings['contact_phone'] ?? '')) !== ''){{ PHP_EOL }}Mobile: {{ $settings['contact_phone'] }}@endif</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="contact-info-card">
                        <div class="contact-info-iconline">
                            <div class="contact-info-icon"><i class="fa fa-calendar-alt"></i></div>
                            <div>
                                <div class="contact-info-heading">{{ $settings['hours_heading'] ?? 'Business Hours:' }}</div>
                                <div class="contact-info-text">{{ $settings['hours_text'] ?? '' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="contact-info-card mb-0">
                        <div class="contact-info-iconline">
                            <div class="contact-info-icon"><i class="fa fa-globe"></i></div>
                            <div>
                                <div class="contact-info-heading">{{ $settings['social_heading'] ?? 'Social Media:' }}</div>
                                <div class="contact-social-links">
                                    @foreach($socialLinks as $socialLink)
                                        @if(trim((string) $socialLink['url']) !== '')
                                            <a href="{{ $socialLink['url'] }}" class="contact-social-link" target="_blank" rel="noopener" aria-label="{{ $socialLink['label'] }}">
                                                <i class="{{ $socialLink['icon'] }}"></i>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-form-card">
                    <h3 class="contact-form-title">{{ $settings['form_title'] ?? 'SEND MESSAGE' }}</h3>
                    <form method="POST" action="{{ route('frontend.contact.store') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <input type="text" name="name" class="contact-field @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="{{ $settings['form_name_placeholder'] ?? 'Enter Your Full Name' }}">
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-3">
                            <input type="email" name="email" class="contact-field @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="{{ $settings['form_email_placeholder'] ?? 'Enter Your E-mail id' }}">
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-3">
                            <input type="text" name="subject" class="contact-field @error('subject') is-invalid @enderror" value="{{ old('subject') }}" placeholder="{{ $settings['form_subject_placeholder'] ?? 'Enter Subject' }}">
                            @error('subject')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <textarea name="message" class="contact-field contact-textarea @error('message') is-invalid @enderror" placeholder="{{ $settings['form_message_placeholder'] ?? 'Enter Your Message' }}">{{ old('message') }}</textarea>
                            @error('message')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold mb-2" for="captcha">Captcha</label>
                            <div class="contact-captcha-row">
                                <div class="contact-captcha-box">{{ $question ?? session('contact_captcha_question') }}</div>
                                <input type="text" id="captcha" name="captcha" class="contact-field contact-captcha-answer @error('captcha') is-invalid @enderror" value="{{ old('captcha') }}" required>
                            </div>
                            @error('captcha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="contact-submit-wrap">
                            <button type="submit" class="contact-submit-btn">{{ $settings['form_button_text'] ?? 'Submit' }}</button>
                        </div>
                    </form>
                </div>
            </div>

            @if(($testimonials ?? collect())->isNotEmpty())
                <div class="contact-testimonial-strip">
                    <div class="contact-testimonial-header">
                        <h4 class="contact-testimonial-title">{{ $settings['testimonial_title'] ?? 'What People Say...' }}</h4>
                        <span class="contact-testimonial-line" aria-hidden="true"></span>
                    </div>
                    <div class="contact-testimonial-scroll">
                        <div class="contact-testimonial-track">
                            @foreach([$testimonials, $testimonials] as $testimonialLoop)
                                @foreach($testimonialLoop as $testimonial)
                                    @php
                                        $testimonialPhotoSrc = $assetHref($testimonial->photo_url ?: 'images/team/1.webp');
                                        $testimonialMessage = trim((string) ($testimonial->message ?? ''));
                                        $testimonialStars = max(3, min(5, (int) ($testimonial->stars ?? 5)));
                                    @endphp
                                    <div class="contact-testimonial-card">
                                        <div class="contact-testimonial-top">
                                            <img src="{{ $testimonialPhotoSrc }}" alt="{{ $testimonial->name }}">
                                            <div>
                                                <div class="contact-testimonial-name">{{ $testimonial->name }}</div>
                                                <div class="contact-testimonial-company">{{ $testimonial->company ?: 'Company Name' }}</div>
                                            </div>
                                        </div>
                                        <div class="contact-testimonial-message">{{ $testimonialMessage !== '' ? $testimonialMessage : 'A short attendee review will appear here once testimonial content is added.' }}</div>
                                        <div class="contact-testimonial-rating" aria-label="{{ $testimonialStars }} out of 5 stars">{{ str_repeat('★', $testimonialStars) }}</div>
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
