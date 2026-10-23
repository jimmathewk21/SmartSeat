<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = trim((string) ($pageSettings['hero_title'] ?? 'Testimonials')) ?: 'Testimonials';
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
        body.testimonials-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.testimonials-page > footer { margin-top: 0 !important; }
        body.testimonials-page {
            background: #f5f6f8;
            color: #111;
        }
        body.testimonials-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.testimonials-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.testimonials-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.testimonials-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        .testimonials-world-section {
            position: relative;
            overflow: hidden;
        }
        .testimonials-hero-shell {
            padding-right: 40px;
            background: rgba(255,255,255,0.24);
        }
        .testimonials-world-map {
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
        .testimonials-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .testimonial-filter-shell {
            border: 1px solid #daddec;
            border-radius: 14px;
            background: rgba(255,255,255,0.42);
            padding: 12px 14px;
            margin: 14px 0 20px;
        }
        .testimonial-filter-title {
            color: #6b7280;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .testimonial-filter-label {
            display: block;
            color: #111;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .testimonial-search-wrap {
            display: flex;
            align-items: stretch;
            gap: 8px;
        }
        .testimonial-search-wrap .testimonial-filter-field {
            padding-right: 16px;
        }
        .testimonial-search-button {
            color: #fff;
            min-width: 46px;
            min-height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: linear-gradient(90deg, #7c4dff 0%, #6a44f2 100%);
            border-radius: 12px;
            font-size: 16px;
            box-shadow: 0 10px 18px rgba(106, 68, 242, 0.16);
            transition: opacity .2s ease, transform .2s ease;
        }
        .testimonial-search-button:hover,
        .testimonial-search-button:focus {
            background: linear-gradient(90deg, #7c4dff 0%, #6a44f2 100%);
            color: #fff;
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .testimonial-filter-field {
            width: 100%;
            min-height: 46px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid #dde1ee;
            background: rgba(255,255,255,0.56);
            color: #111;
            font-size: 14px;
        }
        .testimonial-main-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 330px;
            gap: 34px;
            align-items: start;
        }
        .testimonial-card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 34px;
        }
        .testimonial-list-card {
            position: relative;
            background: #f7f7fb;
            border: 1px solid #cfc8f0;
            border-radius: 24px;
            padding: 18px 14px 14px;
            min-height: 308px;
            box-shadow: 0 8px 20px rgba(47, 40, 93, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .testimonial-card-head {
            display: grid;
            grid-template-columns: 60px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
            margin-bottom: 12px;
            padding-left: 10px;
        }
        .testimonial-list-avatar {
            width: 55px;
            height: 55px;
            border-radius: 10px;
            object-fit: cover;
            object-position: center top;
            flex: 0 0 55px;
            box-shadow: 0 6px 12px rgba(17, 24, 39, 0.08);
            margin-top: -40px;
        }
        .testimonial-list-name {
            color: #111;
            font-size: 15px;
            line-height: 1.05;
            margin: 10px 0 2px;
            font-weight: 800;
        }
        .testimonial-list-role {
            color: #111;
            font-size: 10px;
            line-height: 1.15;
            margin-bottom: 0;
        }
        .testimonial-list-rating-label {
            text-align: center;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.02em;
            color: #111;
            margin: 4px 0 12px;
        }
        .testimonial-list-message {
            color: #303846;
            font-size: 14px;
            line-height: 1.85;
            margin-bottom: 12px;
            min-height: 134px;
        }
        .testimonial-card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: auto;
        }
        .testimonial-stars {
            color: #f0ba00;
            font-size: 16px;
            letter-spacing: 0;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }
        .testimonial-badge {
            min-width: 128px;
            text-align: center;
            padding: 7px 10px;
            border-radius: 9px;
            background: linear-gradient(90deg, #7c4dff 0%, #6a44f2 100%);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            line-height: 1.1;
        }
        .testimonial-feedback-panel {
            position: sticky;
            top: 120px;
            border: 1px solid #cfc8f0;
            border-radius: 28px;
            padding: 28px 22px 20px;
            background: rgba(255,255,255,0.74);
            box-shadow: 0 8px 24px rgba(47, 40, 93, 0.06);
        }
        .testimonial-feedback-title {
            text-align: center;
            color: #111;
            font-size: 28px;
            font-weight: 900;
            line-height: 1.05;
            text-transform: uppercase;
            margin: 0 0 24px;
        }
        .testimonial-feedback-label {
            display: block;
            color: #111;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .testimonial-feedback-field {
            width: 100%;
            min-height: 38px;
            padding: 9px 12px;
            border-radius: 8px;
            border: 1px solid #e0e3ef;
            background: rgba(255,255,255,0.84);
            color: #111;
            font-size: 14px;
        }
        .testimonial-feedback-textarea {
            min-height: 164px;
            resize: vertical;
        }
        .testimonial-feedback-file {
            padding: 8px 10px;
            min-height: 46px;
        }
        .testimonial-feedback-help {
            display: block;
            margin-top: 6px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.35;
        }
        .testimonial-star-options {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .testimonial-star-option {
            position: relative;
            min-width: 0;
        }
        .testimonial-star-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .testimonial-star-option span {
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 56px;
            padding: 4px 8px;
            border-radius: 10px;
            border: 1px solid #d8dced;
            background: rgba(255,255,255,0.84);
            color: #111;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.25;
            text-align: center;
            white-space: normal;
            cursor: pointer;
            transition: all .2s ease;
        }
        .testimonial-star-option input:checked + span {
            background: linear-gradient(90deg, #7c4dff 0%, #6a44f2 100%);
            border-color: #6a44f2;
            color: #fff;
            box-shadow: 0 10px 18px rgba(106, 68, 242, 0.18);
        }
        .testimonial-feedback-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 132px;
            min-height: 40px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(90deg, #7c4dff 0%, #6a44f2 100%);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            box-shadow: 0 12px 22px rgba(106, 68, 242, 0.16);
        }
        .testimonial-feedback-submit:hover,
        .testimonial-feedback-submit:focus {
            color: #fff;
        }
        @media (max-width: 1199px) {
            .testimonial-main-grid {
                grid-template-columns: 1fr;
            }
            .testimonial-feedback-panel {
                position: static;
            }
        }
        @media (max-width: 991px) {
            .testimonial-card-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            .testimonials-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: 0.18;
            }
            .testimonials-hero-shell {
                padding-right: 0;
            }
            .testimonial-card-head {
                grid-template-columns: 56px minmax(0, 1fr);
                gap: 10px;
                padding-left: 6px;
            }
            .testimonial-list-avatar {
                width: 54px;
                height: 54px;
                border-radius: 10px;
                margin-top: -28px;
            }
            .testimonial-card-bottom {
                flex-direction: column;
                align-items: flex-start;
            }
            .testimonial-badge {
                min-width: 0;
                width: 100%;
            }
            .testimonial-star-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="testimonials-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80 testimonials-world-section">
        <div class="testimonials-world-map" aria-hidden="true"></div>
        <div class="container">
            <div class="testimonials-hero-shell">
                @include('frontend.partials.inner-page-hero', [
                    'heroLabel' => $pageSettings['hero_label'] ?? 'TESTIMONIALS + FEEDBACK',
                    'heroTitle' => $pageSettings['hero_title'] ?? 'WHAT PEOPLE SAY',
                    'heroSuffix' => $pageSettings['hero_suffix'] ?? 'real attendee voices',
                    'heroDescription' => $pageSettings['hero_description'] ?? 'Read attendee and speaker experiences from recent sessions, workshops, and event highlights.',
                    'heroButtons' => [],
                ])
            </div>

            @if(session('message'))
                <div class="alert alert-success border-0 shadow-sm mt-4 mb-0">{{ session('message') }}</div>
            @endif

            <form method="GET" action="{{ route('frontend.testimonials.index') }}" class="testimonial-filter-shell">
                <div class="testimonial-filter-title">{{ $pageSettings['filter_title'] ?? 'Filters' }}</div>
                <div class="row g-3 align-items-end">
                    <div class="col-lg-2 col-md-6">
                        <label class="testimonial-filter-label" for="event_id">Event Name</label>
                        <select class="testimonial-filter-field" name="event_id" id="event_id">
                            <option value="">Select</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="testimonial-filter-label" for="country_id">Country</label>
                        <select class="testimonial-filter-field" name="country_id" id="country_id">
                            <option value="">Select</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" @selected((string) request('country_id') === (string) $country->id)>{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="testimonial-filter-label" for="year">Select Year</label>
                        <select class="testimonial-filter-field" name="year" id="year">
                            <option value="">Select</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="testimonial-filter-label" for="sort">Sort By</label>
                        <select class="testimonial-filter-field" name="sort" id="sort">
                            <option value="latest" @selected($sort === 'latest')>Latest First</option>
                            <option value="oldest" @selected($sort === 'oldest')>Oldest First</option>
                            <option value="event_name" @selected($sort === 'event_name')>Event Name A to Z</option>
                            <option value="name_asc" @selected($sort === 'name_asc')>Name A to Z</option>
                            <option value="name_desc" @selected($sort === 'name_desc')>Name Z to A</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label class="testimonial-filter-label" for="search">Search Bar</label>
                        <div class="testimonial-search-wrap">
                            <input class="testimonial-filter-field" type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ $pageSettings['search_placeholder'] ?? 'Search testimonials' }}">
                            <button type="submit" class="testimonial-search-button" aria-label="Search testimonials">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="testimonial-main-grid">
                <div class="testimonial-card-grid">
                @forelse($testimonials as $testimonial)
                    @php
                        $photo = trim((string) ($testimonial->photo_url ?? ''));
                        $isAbsolutePhoto = str_starts_with($photo, 'http://')
                            || str_starts_with($photo, 'https://')
                            || str_starts_with($photo, '//')
                            || str_starts_with($photo, '/');
                        $photoSrc = $photo
                            ? ($isAbsolutePhoto ? $photo : asset(ltrim($photo, '/')))
                            : asset('images/testimonial/1.webp');
                        $displayDate = $testimonial->testimonial_date
                            ? $testimonial->testimonial_date->format('j F, Y')
                            : null;
                        if (! $displayDate && ! empty($testimonial->year)) {
                            $displayDate = '1 January, ' . $testimonial->year;
                        }
                        $designation = trim((string) ($testimonial->designation ?? ''));
                        $company = trim((string) ($testimonial->company ?? ''));
                        $roleLine = trim($designation . ($company !== '' ? ', ' . $company : ''));
                        $stars = max(3, min(5, (int) ($testimonial->stars ?? 5)));
                        $badge = match ($stars) {
                            3 => 'Average',
                            4 => 'Recommended',
                            default => 'Highly Recommended',
                        };
                    @endphp
                        <article class="testimonial-list-card">
                            <div class="testimonial-card-head">
                                <img src="{{ $photoSrc }}" alt="{{ $testimonial->name }}" class="testimonial-list-avatar">
                                <div>
                                    <h5 class="testimonial-list-name">{{ $testimonial->name }}</h5>
                                    @if($roleLine !== '')
                                        <p class="testimonial-list-role">{{ $roleLine }}</p>
                                    @endif
                                </div>
                            </div>
                            <p class="testimonial-list-message">{{ $testimonial->message }}</p>
                            <div class="testimonial-card-bottom">
                                <div class="testimonial-stars" aria-label="{{ $stars }} out of 5 stars">
                                    @for($i = 1; $i <= $stars; $i++)
                                        <span>&#9733;</span>
                                    @endfor
                                </div>
                                <div class="testimonial-badge">{{ $badge }}</div>
                            </div>
                        </article>
                @empty
                    <div class="alert alert-light border mb-0">{{ $pageSettings['empty_state_text'] ?? 'No testimonials available right now.' }}</div>
                @endforelse
                </div>

                <aside class="testimonial-feedback-panel" id="testimonial-feedback">
                    <h3 class="testimonial-feedback-title">{{ $pageSettings['feedback_title'] ?? 'Submit Feedback' }}</h3>
                    @if(session('message'))
                        <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('message') }}</div>
                    @endif
                    <form method="POST" action="{{ route('frontend.testimonials.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="testimonial-feedback-label">Name</label>
                            <input type="text" name="name" class="testimonial-feedback-field @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="testimonial-feedback-label">Company Name</label>
                            <input type="text" name="company_name" class="testimonial-feedback-field @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}">
                            @error('company_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="testimonial-feedback-label">Email</label>
                            <input type="email" name="email" class="testimonial-feedback-field @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="testimonial-feedback-label">Country</label>
                            <select name="country_id" class="testimonial-feedback-field @error('country_id') is-invalid @enderror">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" @selected((string) old('country_id') === (string) $country->id)>{{ $country->name }}</option>
                                @endforeach
                            </select>
                            @error('country_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="testimonial-feedback-label">Upload Picture</label>
                            <input type="file" name="picture_file" class="testimonial-feedback-field testimonial-feedback-file @error('picture_file') is-invalid @enderror" accept="image/*">
                            <small class="testimonial-feedback-help">Recommended image size: 256x256px.</small>
                            @error('picture_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="testimonial-feedback-label">Experience in one word</label>
                            <input type="text" name="experience_word" class="testimonial-feedback-field @error('experience_word') is-invalid @enderror" value="{{ old('experience_word') }}">
                            @error('experience_word')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="testimonial-feedback-label">Select Stars</label>
                            <div class="testimonial-star-options">
                                <label class="testimonial-star-option">
                                    <input type="radio" name="stars" value="3" @checked(old('stars') == 3)>
                                    <span>Average</span>
                                </label>
                                <label class="testimonial-star-option">
                                    <input type="radio" name="stars" value="4" @checked(old('stars') == 4)>
                                    <span>Recommended</span>
                                </label>
                                <label class="testimonial-star-option">
                                    <input type="radio" name="stars" value="5" @checked(old('stars', 5) == 5)>
                                    <span>Highly Recommended</span>
                                </label>
                            </div>
                            @error('stars')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-4">
                            <label class="testimonial-feedback-label">Your Comment within 200 words</label>
                            <textarea name="comment" class="testimonial-feedback-field testimonial-feedback-textarea @error('comment') is-invalid @enderror">{{ old('comment') }}</textarea>
                            @error('comment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-4">
                            <label class="testimonial-feedback-label" for="captcha">Captcha</label>
                            <div class="testimonial-feedback-field d-flex align-items-center" style="min-height: 42px; font-weight: 700;">
                                {{ $captchaQuestion ?? session('testimonial_captcha_question') }}
                            </div>
                            <input type="text" id="captcha" name="captcha" class="testimonial-feedback-field mt-2 @error('captcha') is-invalid @enderror" value="{{ old('captcha') }}" required>
                            @error('captcha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="submit" class="testimonial-feedback-submit">Submit</button>
                        </div>
                    </form>
                </aside>
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
