<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $speaker->name . ' - Speaker Profile';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);

        $speakerPhotoSrc = $speaker->display_photo_url;

        $advertUrl = trim((string) ($speaker->advert_url ?? ''));
        $presentationUrl = trim((string) ($speaker->presentation_file_url ?? ''));
        $bioUrl = trim((string) ($speaker->resource_url ?? ''));
        $normalizeSpeakerAssetUrl = function (string $url): string {
            return ($url !== '' && !str_starts_with($url, 'http://') && !str_starts_with($url, 'https://') && !str_starts_with($url, '//') && !str_starts_with($url, '/'))
                ? asset(ltrim($url, '/'))
                : $url;
        };
        $isExternalSpeakerAssetUrl = function (string $url): bool {
            return str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '//');
        };

        $advertHref = $normalizeSpeakerAssetUrl($advertUrl);
        $presentationHref = $normalizeSpeakerAssetUrl($presentationUrl);
        $bioHref = $normalizeSpeakerAssetUrl($bioUrl);
        $isExternalAdvertHref = $isExternalSpeakerAssetUrl($advertHref);
        $isExternalPresentationHref = $isExternalSpeakerAssetUrl($presentationHref);
        $isExternalBioHref = $isExternalSpeakerAssetUrl($bioHref);
        $shouldDownloadBio = in_array((string) ($speaker->biography_type ?? ''), ['pdf', 'upload'], true) && ! $isExternalBioHref;
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
        body.speaker-profile-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.speaker-profile-page > footer { margin-top: 0 !important; }
        body.speaker-profile-page {
            background: #fff;
            color: #111;
        }
        .profile-left-card {
            background: linear-gradient(180deg, #4a28bf 0%, #31106f 100%);
            border-radius: 8px;
            color: #fff;
            padding: 24px;
            height: 100%;
        }
        .profile-left-card .avatar {
            width: 210px;
            height: 210px;
            border-radius: 12px;
            border: 2px solid rgba(255,255,255,0.55);
            margin: 0 auto 14px;
            overflow: hidden;
        }
        .profile-left-card .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-main-title {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            font-size: 38px;
            line-height: 1.12;
            margin-bottom: 8px;
        }
        .profile-main-title-crown {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            color: #5a31d6;
            font-size: 24px;
            vertical-align: middle;
        }
        .speaker-profile-designation {
            color: #6f46f5;
            font-size: 17px;
            line-height: 1.35;
            margin-bottom: 10px;
        }
        .speaker-profile-company {
            color: #111;
            font-size: 22px;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: 2px;
        }
        .speaker-profile-country {
            color: #2f3747;
            font-size: 18px;
            line-height: 1.3;
            margin-bottom: 12px;
        }
        .speaker-profile-topic {
            color: #111;
            font-size: 15px;
            line-height: 1.45;
            margin-bottom: 14px;
        }
        .speaker-main-bio {
            font-size: 18px;
            line-height: 1.7;
            color: #111;
            margin-bottom: 0;
        }
        .profile-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.2;
            text-transform: none;
            text-decoration: none;
            border: 1px solid transparent;
            transform: none !important;
            transition: none !important;
            animation: none !important;
            box-shadow: none !important;
        }
        .profile-btn-main {
            background: #ffc107;
            border-color: #ffc107;
            color: #111;
        }
        .profile-btn-main:hover,
        .profile-btn-main:focus {
            opacity: 0.92;
            text-decoration: none;
            color: #111;
        }
        .profile-btn-outline {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }
        .profile-btn-outline:hover,
        .profile-btn-outline:focus {
            color: #fff;
            opacity: 0.92;
            text-decoration: none;
        }
        .profile-btn-presentation {
            background: #198754;
            border-color: #198754;
            color: #fff;
        }
        .profile-btn-presentation:hover,
        .profile-btn-presentation:focus {
            color: #fff;
            opacity: 0.92;
            text-decoration: none;
        }
        .speaker-profile-world-section {
            position: relative;
            overflow: hidden;
        }
        .speaker-profile-world-map {
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
        .speaker-profile-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .schedule-card {
            border: 1px solid #e5e8ef;
            border-radius: 8px;
            padding: 18px;
            background: #f8f9fb;
        }
        .speaker-agenda-row + .speaker-agenda-row {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #dfe5f0;
        }
        .speaker-agenda-meta {
            color: #5b6477;
            font-size: 14px;
            line-height: 1.5;
        }
        .speaker-agenda-time {
            font-weight: 700;
            color: #6f46f5;
        }
        .speaker-agenda-role {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: #ece8ff;
            color: #4d2bbd;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .speaker-sponsor-strip {
            margin-top: 36px;
        }
        .speaker-sponsor-strip-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 18px;
        }
        .speaker-sponsor-strip-title {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
            color: #171717;
            white-space: nowrap;
            letter-spacing: 0.01em;
        }
        .speaker-sponsor-strip-line {
            height: 3px;
            flex: 1 1 auto;
            border-radius: 999px;
            background: linear-gradient(90deg, #7c4dff 0%, #b39dff 100%);
        }
        .speaker-sponsor-scroll {
            overflow: hidden;
            position: relative;
        }
        .speaker-sponsor-scroll-track {
            display: flex;
            align-items: center;
            gap: 18px;
            width: max-content;
            animation: speakerSponsorScroll 30s linear infinite;
        }
        .speaker-sponsor-scroll:hover .speaker-sponsor-scroll-track {
            animation-play-state: paused;
        }
        .speaker-sponsor-logo {
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
        .speaker-sponsor-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        @keyframes speakerSponsorScroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @media (max-width: 991.98px) {
            .speaker-profile-world-section {
                padding-top: 90px !important;
            }
        }
        @media (max-width: 768px) {
            .speaker-profile-world-section {
                padding-top: 90px !important;
            }
            .speaker-profile-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: 0.18;
            }
            .profile-main-title {
                font-size: 30px;
            }
            .speaker-profile-designation {
                font-size: 15px;
            }
            .speaker-profile-company {
                font-size: 18px;
            }
            .speaker-profile-country {
                font-size: 16px;
            }
            .speaker-main-bio {
                font-size: 16px;
            }
            .profile-left-card .avatar {
                width: 165px;
                height: 165px;
            }
            .profile-btn {
                width: 100%;
            }
            .speaker-sponsor-strip-title {
                font-size: 24px;
                white-space: normal;
            }
            .speaker-sponsor-logo {
                width: 150px;
                min-width: 150px;
                height: 78px;
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body class="speaker-profile-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80 speaker-profile-world-section">
        <div class="speaker-profile-world-map" aria-hidden="true"></div>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="profile-left-card text-center">
                        <div class="avatar">
                            <img src="{{ $speakerPhotoSrc }}" alt="{{ $speaker->name }}">
                        </div>
                        <h4 class="mb-1 text-white">{{ $speaker->name }}</h4>
                        <div class="opacity-75">{{ $speaker->designation }}</div>
                        @if($speaker->company_name)
                            <div class="opacity-75">{{ $speaker->company_name }}</div>
                        @endif
                        @if($speaker->country)
                            <div class="opacity-75">{{ $speaker->country }}</div>
                        @endif
                        <hr style="border-color: rgba(255,255,255,0.2);">
                        @if($speaker->show_contact_details && $speaker->email)<div class="mb-2"><i class="fa fa-envelope me-2"></i>{{ $speaker->email }}</div>@endif
                        @if($speaker->show_contact_details && $speaker->phone)<div class="mb-2"><i class="fa fa-phone me-2"></i>{{ $speaker->phone }}</div>@endif
                        @if($speaker->linkedin_url)
                            <div class="mb-2"><i class="fa-brands fa-linkedin me-2"></i><a href="{{ $speaker->linkedin_url }}" target="_blank" rel="noopener" class="text-white">LinkedIn Profile</a></div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-9">
                    <h2 class="profile-main-title">
                        {{ $speaker->name }}
                        @if($speaker->is_key_figure)
                            <span class="profile-main-title-crown" aria-label="Key figure"><i class="fa fa-crown" aria-hidden="true"></i></span>
                        @endif
                    </h2>
                    @if($speaker->designation)
                        <div class="speaker-profile-designation">[{{ $speaker->designation }}]</div>
                    @endif
                    @if($speaker->company_name)
                        <div class="speaker-profile-company">{{ $speaker->company_name }}</div>
                    @endif
                    @if($speaker->country)
                        <div class="speaker-profile-country">{{ $speaker->country }}</div>
                    @endif
                    @if($speaker->topic)
                        <div class="speaker-profile-topic"><strong>Topic:</strong> {{ $speaker->topic }}</div>
                    @endif
                    @if($speaker->topic_category)
                        <div class="speaker-profile-topic"><strong>Topic Category:</strong> {{ $speaker->topic_category }}</div>
                    @endif

                    <div class="row g-3 mb-4 mt-2">
                        @if($advertHref !== '')
                            <div class="col-auto">
                                <a href="{{ $advertHref }}" class="profile-btn profile-btn-main" target="_blank" rel="noopener" @if(!$isExternalAdvertHref) download @endif>
                                    <i class="fas fa-bullhorn me-2" aria-hidden="true"></i>Advert
                                </a>
                            </div>
                        @endif

                        @if($bioHref !== '')
                            <div class="col-auto">
                                <a href="{{ $bioHref }}" class="profile-btn profile-btn-outline" target="_blank" rel="noopener" @if($shouldDownloadBio) download @endif>
                                    <i class="fas fa-id-card me-2" aria-hidden="true"></i>Bio
                                </a>
                            </div>
                        @endif

                        @if($presentationHref !== '')
                            <div class="col-auto">
                                <a href="{{ $presentationHref }}" class="profile-btn profile-btn-presentation" target="_blank" rel="noopener" @if(!$isExternalPresentationHref) download @endif>
                                    <i class="fas fa-file-powerpoint me-2" aria-hidden="true"></i>Presentation
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="schedule-card">
                        <h4 class="mb-3">Event Schedule</h4>
                        @if(($agendaSessions ?? collect())->isNotEmpty())
                            @foreach($agendaSessions as $session)
                                @php
                                    $isModerator = (int) ($session->moderator_speaker_id ?? 0) === (int) $speaker->id;
                                    $sessionDate = optional($session->day?->agenda_date)->format('d M Y');
                                    $sessionWeekday = $session->day?->weekday ?: '';
                                    $sessionTime = trim((string) ($session->start_time ?? '') . (($session->end_time ?? '') ? ' - ' . $session->end_time : ''));
                                @endphp
                                <div class="speaker-agenda-row">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                        <h5 class="mb-0">
                                            @if($session->day?->event_id && $session->day?->id)
                                                <a href="{{ route('frontend.agenda.index', ['event_id' => $session->day->event_id, 'day_id' => $session->day->id]) }}#agenda-item-{{ $session->id }}" style="text-decoration:none;color:inherit;">
                                                    {{ $session->title }}
                                                </a>
                                            @else
                                                {{ $session->title }}
                                            @endif
                                        </h5>
                                        <span class="speaker-agenda-role">{{ $isModerator ? 'Moderator' : 'Speaker' }}</span>
                                    </div>
                                    <div class="speaker-agenda-meta">
                                        <div><strong>Room:</strong> {{ $session->room?->name ?: '-' }}</div>
                                        <div><strong>Date:</strong> {{ trim($sessionDate . ($sessionWeekday !== '' ? ' - ' . $sessionWeekday : '')) ?: '-' }}</div>
                                        <div><strong>Time:</strong> <span class="speaker-agenda-time">{{ $sessionTime ?: '-' }}</span></div>
                                        @if($session->panel_theme)
                                            <div><strong>Theme:</strong> {{ $session->panel_theme }}</div>
                                        @endif
                                    </div>
                                    @if($session->topic_description)
                                        <div class="mt-2">{{ $session->topic_description }}</div>
                                    @endif
                                </div>
                            @endforeach
                        @elseif($speaker->event)
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>Event:</strong>
                                    <div>{{ $speaker->event->title }}</div>
                                </div>
                                <div class="col-md-6">
                                    <strong>Venue:</strong>
                                    <div>{{ $speaker->event->venue ?: '-' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">No event schedule assigned yet.</div>
                        @endif
                    </div>

                    @if($speaker->bio)
                        <div class="speaker-main-bio mt-4">{!! $speaker->bio !!}</div>
                    @endif

                </div>
            </div>

            @if(($sponsors ?? collect())->isNotEmpty())
                <div class="speaker-sponsor-strip">
                    <div class="speaker-sponsor-strip-header">
                        <h4 class="speaker-sponsor-strip-title">Sponsors & Partners</h4>
                        <span class="speaker-sponsor-strip-line" aria-hidden="true"></span>
                    </div>
                    <div class="speaker-sponsor-scroll">
                        <div class="speaker-sponsor-scroll-track">
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
                                    <a href="{{ route('frontend.sponsors.show', $sponsor) }}" class="speaker-sponsor-logo" aria-label="{{ $sponsor->name }}">
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
