<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'Event Speakers';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $speakerSettings = $speakerPageSettings ?? [];
        $countryFlagUrl = function (?string $countryName) use ($countryCodeMap) {
            $countryName = mb_strtolower(trim((string) $countryName));
            $countryCode = trim((string) ($countryCodeMap[$countryName] ?? ''));

            if ($countryCode === '' || strlen($countryCode) !== 2) {
                return null;
            }

            return 'https://flagcdn.com/24x18/' . strtolower($countryCode) . '.png';
        };
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
        body.speakers-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.speakers-page > footer {
            margin-top: 0 !important;
            position: relative;
            z-index: 2;
        }
        body.speakers-page .speakers-world-section {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        body.speakers-page .speakers-pagination {
            margin-top: 24px !important;
            margin-bottom: 0 !important;
        }
        body.speakers-page .speakers-pagination-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 18px 20px;
            border: 1px solid #dce2ed;
            border-radius: 14px;
            background: #f4f6f9;
        }
        body.speakers-page .speakers-pagination-status {
            margin: 0;
            color: #4a5568;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 500;
        }
        body.speakers-page .speakers-pagination-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
        }
        body.speakers-page .speakers-page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
            min-height: 46px;
            padding: 0 20px;
            border: 1px solid #d6ddea;
            border-radius: 12px;
            background: #fff;
            color: #111;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            transition: background-color .2s ease, border-color .2s ease, color .2s ease, opacity .2s ease;
        }
        body.speakers-page .speakers-page-link:hover,
        body.speakers-page .speakers-page-link:focus {
            background: #6f42f5;
            border-color: #6f42f5;
            color: #fff;
            text-decoration: none;
        }
        body.speakers-page .speakers-page-link.is-disabled,
        body.speakers-page .speakers-page-link[aria-disabled="true"] {
            background: #eef1f6;
            border-color: #dce2ed;
            color: #8a94a6;
            pointer-events: none;
            cursor: default;
        }
        body.speakers-page .speakers-world-section > .container {
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }
        body.speakers-page .speaker-cards-row {
            margin-bottom: 0 !important;
        }
        body.speakers-page {
            background: #f8f9fa;
            color: #111;
        }
        body.speakers-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.speakers-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.speakers-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.speakers-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        body.speakers-page .inside-page-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        body.speakers-page .speaker-search-wrap {
            background: #f4f6f9;
            border: 1px solid #dce2ed;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        body.speakers-page .speaker-search-form {
            width: 100%;
        }
        body.speakers-page .speaker-search-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 14px;
            align-items: end;
        }
        body.speakers-page .speaker-search-grid .form-group {
            margin-bottom: 0;
            min-width: 0;
        }
        body.speakers-page .speaker-search-grid .form-label {
            margin-bottom: 8px;
            font-size: 16px;
            line-height: 1.2;
            font-weight: 400;
            color: #111;
        }
        body.speakers-page .speaker-search-button {
            color: #fff;
            min-width: 56px;
            min-height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: linear-gradient(90deg, #7c4dff 0%, #6a44f2 100%);
            border-radius: 12px;
            font-size: 18px;
            box-shadow: 0 10px 18px rgba(106, 68, 242, 0.16);
            transition: opacity .2s ease, transform .2s ease;
            width: 100%;
        }
        body.speakers-page .speaker-search-button:hover,
        body.speakers-page .speaker-search-button:focus {
            background: linear-gradient(90deg, #7c4dff 0%, #6a44f2 100%);
            color: #fff;
            opacity: 0.95;
            transform: translateY(-1px);
        }
        body.speakers-page .speakers-world-section {
            position: relative;
            overflow: hidden;
        }
        body.speakers-page .speakers-world-map {
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
        body.speakers-page .speakers-world-section > .container {
            position: relative;
            z-index: 1;
        }
        body.speakers-page .speaker-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
            background: #f1f2f4;
            border: 1px solid #e2e6ee;
            border-radius: 6px;
            padding: 12px;
            color: #111;
            text-decoration: none;
            margin-bottom: 0;
            height: 100%;
        }
        body.speakers-page .speaker-card-main {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
            flex: 1 1 auto;
            width: 100%;
            color: #111;
            text-decoration: none;
        }
        body.speakers-page .speaker-card-main:hover {
            color: #111;
            text-decoration: none;
        }
        body.speakers-page .speaker-cards-row {
            row-gap: 12px;
        }
        body.speakers-page .speaker-card:hover {
            color: #111;
            text-decoration: none;
            background: #eceef2;
        }
        body.speakers-page .speaker-image-wrap {
            position: relative;
            overflow: hidden;
            border-radius: 6px;
            width: 100%;
            min-width: 100%;
            height: 220px;
            min-height: 220px;
        }
        body.speakers-page .speaker-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        body.speakers-page .speaker-meta {
            margin-top: 0;
            text-align: left;
            padding: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        body.speakers-page .speaker-name {
            font-size: 15px;
            line-height: 1.15;
            margin-bottom: 3px;
            color: #000;
            font-weight: 700;
        }
        body.speakers-page .speaker-designation {
            display: block;
            font-size: 11px;
            line-height: 1.25;
            margin-bottom: 4px;
            color: #6f42f5;
            font-weight: 500;
        }
        body.speakers-page .speaker-company {
            display: block;
            font-size: 12px;
            line-height: 1.25;
            margin-bottom: 1px;
            color: #111;
            font-weight: 700;
        }
        body.speakers-page .speaker-country {
            display: block;
            font-size: 12px;
            line-height: 1.3;
            color: #111;
            font-weight: 500;
            margin-top: 0;
            margin-bottom: 8px;
        }
        body.speakers-page .speaker-topic {
            font-size: 12px;
            line-height: 1.4;
            margin-top: 0;
            margin-bottom: 8px;
            color: #111;
            max-width: 95%;
        }
        body.speakers-page .speaker-topic strong {
            font-weight: 700;
            color: #000;
        }
        body.speakers-page .speaker-icons {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
            margin-top: auto;
            padding-top: 4px;
        }
        body.speakers-page .speaker-icon-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            color: #111;
            font-size: 14px;
            line-height: 1;
            text-decoration: none;
            background: transparent;
            border: 0;
            padding: 0;
            cursor: pointer;
        }
        body.speakers-page .speaker-icon-item:hover {
            color: #6f42f5;
            text-decoration: none;
        }
        body.speakers-page .speaker-icon-item.is-active {
            color: #6f42f5;
        }
        body.speakers-page .speaker-icon-item.is-country {
            width: auto;
            min-width: 20px;
            line-height: 1;
        }
        body.speakers-page .speaker-flag {
            display: block;
            width: 18px;
            height: 14px;
            object-fit: cover;
            border-radius: 2px;
            box-shadow: 0 0 0 1px rgba(17, 17, 17, 0.12);
        }
        body.speakers-page .speaker-icon-item i {
            display: block;
        }
        body.speakers-page .spk-btn {
            border: 0;
            cursor: pointer;
        }
        body.speakers-page .spk-btn.w-100 {
            width: 100%;
        }
        body.speakers-page .guidelines-body {
            color: #111;
            font-size: 16px;
            line-height: 1.7;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        body.speakers-page .modal-content {
            border: 0;
            border-radius: 12px;
            overflow: hidden;
            max-height: calc(100vh - 56px);
        }
        body.speakers-page .modal-header {
            background: #141a3a;
            color: #fff;
            border-bottom: 0;
            padding: 14px 18px;
        }
        body.speakers-page .modal-header .modal-title {
            color: #fff !important;
        }
        body.speakers-page .modal-header .btn-close {
            filter: invert(1);
            opacity: .8;
        }
        body.speakers-page .modal-body.guidelines-body {
            flex: 1 1 auto;
            position: relative;
            background: #f7f8fc;
            padding: 20px;
            padding-right: 20px;
            max-height: calc(100vh - 136px);
            height: auto;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgba(71, 85, 105, 0.75) rgba(203, 213, 225, 0.55);
        }
        body.speakers-page .modal-body.guidelines-body::-webkit-scrollbar {
            width: 10px;
        }
        body.speakers-page .modal-body.guidelines-body::-webkit-scrollbar-track {
            background: rgba(203, 213, 225, 0.55);
            border-radius: 999px;
        }
        body.speakers-page .modal-body.guidelines-body::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.75);
            border-radius: 999px;
            border: 2px solid rgba(247, 248, 252, 0.95);
        }
        body.speakers-page .modal-body.guidelines-body::-webkit-scrollbar-thumb:hover {
            background: rgba(51, 65, 85, 0.9);
        }
        body.speakers-page .modal-body.guidelines-body ul,
        body.speakers-page .modal-body.guidelines-body ol {
            margin: 0 0 12px;
            padding-left: 1.3rem;
        }
        body.speakers-page .modal-body.guidelines-body li {
            margin-bottom: 6px;
        }
        body.speakers-page .modal-body.guidelines-body h1,
        body.speakers-page .modal-body.guidelines-body h2,
        body.speakers-page .modal-body.guidelines-body h3,
        body.speakers-page .modal-body.guidelines-body h4,
        body.speakers-page .modal-body.guidelines-body h5,
        body.speakers-page .modal-body.guidelines-body h6 {
            color: #111;
            font-weight: 700;
            line-height: 1.25;
            margin: 0 0 12px;
        }
        body.speakers-page .modal-body.guidelines-body p {
            margin: 0 0 12px;
            color: #111;
        }
        body.speakers-page .modal-body.guidelines-body p:last-child,
        body.speakers-page .modal-body.guidelines-body ul:last-child,
        body.speakers-page .modal-body.guidelines-body ol:last-child {
            margin-bottom: 0;
        }
        @media (max-width: 991.98px) {
            body.speakers-page .speakers-world-section {
                padding-top: 90px !important;
            }
            body.speakers-page .inside-page-hero-main {
                font-size: 34px;
                line-height: 1.08;
            }
            body.speakers-page .inside-page-hero-description {
                font-size: 16px;
                line-height: 1.7;
            }
            body.speakers-page .inside-page-hero-actions {
                margin-bottom: 22px;
            }
            body.speakers-page .speaker-search-wrap {
                padding: 16px;
            }
            body.speakers-page .speaker-search-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            body.speakers-page .speaker-card {
                min-height: 0;
            }
            body.speakers-page .speaker-image-wrap {
                height: 200px;
                min-height: 200px;
            }
            body.speakers-page .speaker-meta {
                width: 100%;
            }
            body.speakers-page .speaker-topic {
                max-width: 100%;
            }
        }
        @media (max-width: 767px) {
            body.speakers-page .speakers-world-section {
                padding-top: 90px !important;
            }
            body.speakers-page .speakers-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: 0.18;
            }
            body.speakers-page .speaker-image-wrap {
                height: 190px;
                min-height: 190px;
            }
            body.speakers-page .speaker-name {
                font-size: 16px;
            }
            body.speakers-page .speaker-designation {
                font-size: 12px;
            }
            body.speakers-page .speaker-company { font-size: 15px; }
            body.speakers-page .speaker-country { font-size: 14px; }
            body.speakers-page .speaker-topic {
                font-size: 13px;
            }
            body.speakers-page .speaker-icons {
                gap: 8px;
            }
            body.speakers-page .inside-page-hero-main {
                font-size: 30px;
            }
            body.speakers-page .inside-page-hero-description {
                font-size: 15px;
            }
            body.speakers-page .inside-page-hero-actions {
                gap: 10px;
            }
            body.speakers-page .inside-page-hero-actions .btn-main {
                width: 100%;
                text-align: center;
            }
            body.speakers-page .speaker-search-grid {
                grid-template-columns: 1fr;
            }
            body.speakers-page .modal-dialog {
                margin: 12px;
            }
            body.speakers-page .modal-content {
                max-height: calc(100vh - 24px);
            }
            body.speakers-page .modal-body.guidelines-body {
                max-height: calc(100vh - 104px);
            }
            body.speakers-page .speakers-pagination-nav {
                padding: 16px;
            }
            body.speakers-page .speakers-pagination-actions {
                width: 100%;
                margin-left: 0;
            }
            body.speakers-page .speakers-page-link {
                flex: 1 1 0;
                min-width: 0;
                padding: 0 14px;
            }
        }
    </style>
</head>
<body class="speakers-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-0 bg-light speakers-world-section">
        <div class="speakers-world-map" aria-hidden="true"></div>
        <div class="container">
            @php
                $speakerHeroButtons = [];
                if (!empty($speakerSettings['guidelines_button_text'])) {
                    $speakerHeroButtons[] = [
                        'type' => 'button',
                        'text' => $speakerSettings['guidelines_button_text'],
                        'class' => 'btn-main btn-line text-uppercase spk-btn',
                        'toggle' => 'modal',
                        'target' => '#speakerGuidelinesModal',
                    ];
                }
                if (!empty($speakerSettings['become_button_text'])) {
                    $speakerHeroButtons[] = [
                        'type' => 'link',
                        'text' => $speakerSettings['become_button_text'],
                        'class' => 'btn-main text-uppercase spk-btn',
                        'href' => route('frontend.speakers.apply.create'),
                    ];
                }
            @endphp
            @include('frontend.partials.inner-page-hero', [
                'heroLabel' => $speakerSettings['page_title'] ?? 'SPEAKERS + PARTNERS',
                'heroTitle' => $speakerSettings['subtitle'] ?? 'EVENT SPEAKERS',
                'heroSuffix' => $speakerSettings['subtitle_suffix'] ?? '',
                'heroDescription' => $speakerSettings['description'] ?? '',
                'heroButtons' => $speakerHeroButtons,
            ])

            @if(session('message'))
                <div class="alert alert-info border mb-4">{{ session('message') }}</div>
            @endif

            <div class="speaker-search-wrap mb-4">
                <form method="GET" action="{{ route('frontend.speakers.index') }}" class="speaker-search-form">
                    <div class="speaker-search-grid">
                        <div class="form-group">
                            <label class="form-label">Select Country</label>
                            <select name="country" class="form-control">
                                <option value="">Select</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country }}" @selected(request('country') === $country)>{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select Category</label>
                            <select name="category" class="form-control">
                                <option value="">Select</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gender</label>
                            <select name="speaker_type" class="form-control">
                                <option value="">All Speakers</option>
                                <option value="male" @selected(request('speaker_type') === 'male')>Male</option>
                                <option value="female" @selected(request('speaker_type') === 'female')>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Speaker / Moderator</label>
                            <select name="speaker_role" class="form-control">
                                <option value="">All</option>
                                <option value="speaker" @selected(request('speaker_role') === 'speaker')>Speaker</option>
                                <option value="moderator" @selected(request('speaker_role') === 'moderator')>Moderator</option>
                                <option value="both" @selected(request('speaker_role') === 'both')>Speaker & Moderator</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="speaker-keyword">Search</label>
                            <input
                                id="speaker-keyword"
                                type="text"
                                class="form-control"
                                name="keyword"
                                value="{{ request('keyword') }}"
                                placeholder="Search by name, company, designation, email, or topic"
                            >
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sort By</label>
                            <select name="sort_by" class="form-control">
                                <option value="">Default</option>
                                <option value="name" @selected(request('sort_by') === 'name')>Name</option>
                                <option value="company_name" @selected(request('sort_by') === 'company_name')>Company Name</option>
                                <option value="country" @selected(request('sort_by') === 'country')>Country</option>
                                <option value="topic" @selected(request('sort_by') === 'topic')>Topic</option>
                                <option value="email" @selected(request('sort_by') === 'email')>Email</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="speaker-search-button spk-btn" aria-label="Search">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row g-4 speaker-cards-row">
                @forelse($speakers as $speaker)
                    @php
                        $keyFigureFilterUrl = request('is_key_figure') === '1'
                            ? route('frontend.speakers.index', array_merge(request()->except(['page', 'is_key_figure'])))
                            : route('frontend.speakers.index', array_merge(request()->except('page'), ['is_key_figure' => '1']));
                        $speakerPhotoSrc = $speaker->display_photo_url;
                        $designation = trim((string) ($speaker->designation ?? ''));
                        $country = trim((string) ($speaker->country ?? ''));
                        $gender = strtolower(trim((string) ($speaker->gender ?? '')));
                        $speakerRole = (string) ($speaker->speaker_role ?? 'speaker');
                        $speakerRoleLabel = match ($speakerRole) {
                            'moderator' => 'Moderator',
                            'both' => 'Speaker & Moderator',
                            default => 'Speaker',
                        };
                        $countryFlagUrlValue = $countryFlagUrl($country);
                        $presentationFile = trim((string) ($speaker->presentation_file_url ?? ''));
                        $advertFile = trim((string) ($speaker->advert_url ?? ''));
                        $linkedinUrl = trim((string) ($speaker->linkedin_url ?? ''));
                        $resourceUrl = trim((string) ($speaker->resource_url ?? ''));
                        $speakerProfileUrl = route('frontend.speakers.show', $speaker);
                        $presentationHref = ($presentationFile !== '' && !str_starts_with($presentationFile, 'http://') && !str_starts_with($presentationFile, 'https://') && !str_starts_with($presentationFile, '//') && !str_starts_with($presentationFile, '/'))
                            ? asset(ltrim($presentationFile, '/'))
                            : $presentationFile;
                        $advertHref = ($advertFile !== '' && !str_starts_with($advertFile, 'http://') && !str_starts_with($advertFile, 'https://') && !str_starts_with($advertFile, '//') && !str_starts_with($advertFile, '/'))
                            ? asset(ltrim($advertFile, '/'))
                            : $advertFile;
                        $resourceHref = ($resourceUrl !== '' && !str_starts_with($resourceUrl, 'http://') && !str_starts_with($resourceUrl, 'https://') && !str_starts_with($resourceUrl, '//') && !str_starts_with($resourceUrl, '/'))
                            ? asset(ltrim($resourceUrl, '/'))
                            : $resourceUrl;
                    @endphp
                    <div class="col-xxl-2 col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12">
                        <div class="speaker-card">
                            <a href="{{ $speakerProfileUrl }}" class="speaker-card-main">
                            <div class="speaker-image-wrap">
                                <img src="{{ $speakerPhotoSrc }}" alt="{{ $speaker->name }}">
                            </div>
                            <div class="speaker-meta">
                                <h5 class="speaker-name">{{ $speaker->name }}</h5>
                                @if($designation !== '')
                                    <div class="speaker-designation">[{{ $designation }}]</div>
                                @endif
                                @if(!empty($speaker->company_name))
                                    <div class="speaker-company">{{ $speaker->company_name }}</div>
                                @endif
                                @if(!empty($speaker->category))
                                    <div class="speaker-designation" style="margin-bottom: 6px;">{{ $speaker->category }}</div>
                                @endif
                                @if(!empty($speaker->topic_category))
                                    <div class="speaker-topic"><strong>Topic Category:</strong> {{ $speaker->topic_category }}</div>
                                @endif
                                @if(!empty($speaker->topic))
                                    <div class="speaker-topic"><strong>Topic ({{ $speakerRoleLabel }}):</strong> {{ $speaker->topic }}</div>
                                @endif
                                <div class="speaker-icons">
                                    @if($country !== '')
                                        <button type="button" class="speaker-icon-item is-country" title="Country: {{ $country }}" aria-label="Country: {{ $country }}" onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('frontend.speakers.index', array_filter(['country' => $country])) }}';">
                                            @if($countryFlagUrlValue)
                                                <img src="{{ $countryFlagUrlValue }}" alt="{{ $country }}" class="speaker-flag" loading="lazy">
                                            @else
                                                <i class="fa fa-globe"></i>
                                            @endif
                                        </button>
                                    @endif
                                    @if(in_array($gender, ['male', 'female'], true))
                                        <button type="button" class="speaker-icon-item" title="{{ ucfirst($gender) }} (Click to filter)" aria-label="{{ ucfirst($gender) }} (Click to filter)" onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('frontend.speakers.index', array_merge(request()->except('page'), ['speaker_type' => $gender])) }}';">
                                            <i class="fa {{ $gender === 'female' ? 'fa-venus' : 'fa-mars' }}"></i>
                                        </button>
                                    @endif
                                    @if($speaker->is_key_figure)
                                        <button type="button" class="speaker-icon-item is-key-figure {{ request('is_key_figure') === '1' ? 'is-active' : '' }}" title="Key Figure (Click to filter)" aria-label="Key Figure (Click to filter)" onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ $keyFigureFilterUrl }}';">
                                            <i class="fa fa-crown"></i>
                                        </button>
                                    @endif
                                    @if($presentationFile !== '')
                                        <button type="button" class="speaker-icon-item" title="Presentation" aria-label="Presentation" onclick="event.preventDefault(); event.stopPropagation(); window.open('{{ $presentationHref }}', '_blank', 'noopener');">
                                            <i class="fas fa-file-powerpoint"></i>
                                        </button>
                                    @endif
                                    @if($advertFile !== '')
                                        <button type="button" class="speaker-icon-item" title="Advert" aria-label="Advert" onclick="event.preventDefault(); event.stopPropagation(); window.open('{{ $advertHref }}', '_blank', 'noopener');">
                                            <i class="fas fa-bullhorn"></i>
                                        </button>
                                    @endif
                                    @if($linkedinUrl !== '')
                                        <button type="button" class="speaker-icon-item" title="LinkedIn" aria-label="LinkedIn" onclick="event.preventDefault(); event.stopPropagation(); window.open('{{ $linkedinUrl }}', '_blank', 'noopener');">
                                            <i class="fa-brands fa-linkedin-in"></i>
                                        </button>
                                    @endif
                                    @if($resourceUrl !== '')
                                        <button type="button" class="speaker-icon-item" title="Bio" aria-label="Bio" onclick="event.preventDefault(); event.stopPropagation(); window.open('{{ $resourceHref }}', '_blank', 'noopener');">
                                            <i class="fas fa-id-card"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border">No speakers found.</div>
                    </div>
                @endforelse
            </div>

            @if($speakers->hasPages())
                <div class="speakers-pagination">
                    <div class="speakers-pagination-nav">
                        <p class="speakers-pagination-status">
                            Showing {{ $speakers->firstItem() }} to {{ $speakers->lastItem() }} of {{ $speakers->total() }} speakers
                        </p>
                        <div class="speakers-pagination-actions">
                            @if($speakers->onFirstPage())
                                <span class="speakers-page-link is-disabled" aria-disabled="true">Previous</span>
                            @else
                                <a href="{{ $speakers->previousPageUrl() }}" class="speakers-page-link" rel="prev">Previous</a>
                            @endif

                            @if($speakers->hasMorePages())
                                <a href="{{ $speakers->nextPageUrl() }}" class="speakers-page-link" rel="next">Next</a>
                            @else
                                <span class="speakers-page-link is-disabled" aria-disabled="true">Next</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

@include('frontend.partials.footer')

<div class="modal fade" id="speakerGuidelinesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Speaker Guidelines</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body guidelines-body">
                {!! $speakerPageSettings['guidelines_content'] ?? '' !!}
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('speakerGuidelinesModal');
        const modalBody = modal ? modal.querySelector('.modal-body.guidelines-body') : null;
        let lockedScrollY = 0;

        if (!modal || !modalBody) {
            return;
        }

        modal.addEventListener('show.bs.modal', function () {
            lockedScrollY = window.scrollY || document.documentElement.scrollTop || 0;
            document.body.style.position = 'fixed';
            document.body.style.top = '-' + lockedScrollY + 'px';
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
        });

        modal.addEventListener('hidden.bs.modal', function () {
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';
            document.body.style.overflow = '';
            window.scrollTo(0, lockedScrollY);
        });

        modal.addEventListener('wheel', function (event) {
            const canScroll = modalBody.scrollHeight > modalBody.clientHeight;

            if (!canScroll) {
                return;
            }

            event.preventDefault();
            modalBody.scrollTop += event.deltaY;
        }, { passive: false });
    });
</script>
</body>
</html>
