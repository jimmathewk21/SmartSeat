<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $sponsor->name . ' - Sponsor Profile';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);

        $toAssetUrl = static function (?string $url): string {
            $url = trim((string) $url);
            if ($url === '') {
                return '';
            }

            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '//') || str_starts_with($url, '/')) {
                return $url;
            }

            return asset(ltrim($url, '/'));
        };

        $logoSrc = $toAssetUrl($sponsor->logo_url) ?: asset('images/logo.webp');
        $websiteHref = trim((string) ($sponsor->website_url ?? ''));
        $linkedinHref = trim((string) ($sponsor->linkedin_url ?? ''));
        $advertHref = $toAssetUrl($sponsor->advert_url);
        $brochureHref = $toAssetUrl($sponsor->brochure_url);
        $videoUrl = trim((string) ($sponsor->sponsor_video_url ?? ''));
        $subCategoryText = ($sponsor->use_sub_category_text ?? false) ? trim((string) ($sponsor->sub_category_text ?? '')) : '';

        $videoEmbedUrl = '';
        if ($videoUrl !== '') {
            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\?\/]+)/', $videoUrl, $matches)) {
                $videoEmbedUrl = 'https://www.youtube.com/embed/' . $matches[1];
            } elseif (str_contains($videoUrl, 'vimeo.com/')) {
                $videoId = preg_replace('/\D+/', '', basename(parse_url($videoUrl, PHP_URL_PATH) ?: ''));
                if ($videoId !== '') {
                    $videoEmbedUrl = 'https://player.vimeo.com/video/' . $videoId;
                }
            } else {
                $videoEmbedUrl = $videoUrl;
            }
        }
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
        body.sponsor-profile-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.sponsor-profile-page > footer { margin-top: 0 !important; }
        body.sponsor-profile-page {
            background: #fff;
            color: #111;
        }
        .sponsor-profile-left {
            border: 1px solid #e1e5ec;
            background: #fff;
            padding: 22px;
            border-radius: 4px;
        }
        .sponsor-profile-logo {
            width: 100%;
            max-height: 120px;
            object-fit: contain;
            margin-bottom: 18px;
        }
        .label-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: #111;
            letter-spacing: .02em;
            margin-bottom: 2px;
        }
        .label-value {
            color: #6c757d;
            margin-bottom: 12px;
            word-break: break-word;
        }
        .sponsor-profile-main h2 {
            margin-bottom: 8px;
        }
        .download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.2;
            text-decoration: none;
            border: 1px solid transparent;
            transform: none !important;
            transition: none !important;
            animation: none !important;
            box-shadow: none !important;
            margin-right: 8px;
            margin-bottom: 8px;
        }
        .download-btn-advert {
            background: #ffc107;
            border-color: #ffc107;
            color: #111;
        }
        .download-btn-brochure {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }
        .download-btn:hover,
        .download-btn:focus,
        .download-btn:active {
            transform: none !important;
            transition: none !important;
            animation: none !important;
            box-shadow: none !important;
            text-decoration: none;
            opacity: 0.95;
            color: inherit;
        }
        .sponsor-profile-world-section {
            position: relative;
            overflow: hidden;
        }
        .sponsor-profile-world-map {
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
        .sponsor-profile-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .speaker-autoscroll {
            overflow: hidden;
            position: relative;
            margin-top: 12px;
            padding-bottom: 8px;
        }
        .speaker-autoscroll-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 18px;
        }
        .speaker-autoscroll-title {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
            color: #171717;
            white-space: nowrap;
        }
        .speaker-autoscroll-line {
            height: 3px;
            flex: 1 1 auto;
            border-radius: 999px;
            background: linear-gradient(90deg, #7c4dff 0%, #b39dff 100%);
        }
        .speaker-autoscroll-track {
            display: flex;
            width: max-content;
            animation: sponsorSpeakerScroll 35s linear infinite;
        }
        .speaker-autoscroll:hover .speaker-autoscroll-track {
            animation-play-state: paused;
        }
        .speaker-chip {
            display: block;
            margin-right: 18px;
            width: 170px;
            min-width: 170px;
            text-decoration: none;
            color: #171717;
            line-height: 1;
        }
        .speaker-chip-image {
            display: block;
            border-radius: 8px;
            overflow: hidden;
            background: #edf1f6;
            box-shadow: 0 12px 30px rgba(20, 24, 45, 0.08);
            margin-bottom: 8px;
        }
        .speaker-chip img {
            width: 100%;
            height: 168px;
            object-fit: cover;
            display: block;
        }
        .speaker-chip-meta {
            display: block;
            padding: 0;
            text-align: center;
            line-height: 1.1;
        }
        .speaker-chip-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
            color: #1b1b1b;
            display: block;
        }
        .speaker-chip-role {
            font-size: 14px;
            color: #555;
            margin: 2px 0 0;
            line-height: 1.2;
            display: block;
        }
        @keyframes sponsorSpeakerScroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @media (max-width: 768px) {
            .sponsor-profile-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: 0.18;
            }
            .speaker-autoscroll-title {
                font-size: 24px;
            }
            .speaker-chip {
                width: 145px;
                min-width: 145px;
            }
            .speaker-chip img {
                height: 145px;
            }
        }
    </style>
</head>
<body class="sponsor-profile-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80 sponsor-profile-world-section">
        <div class="sponsor-profile-world-map" aria-hidden="true"></div>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="sponsor-profile-left">
                        <img src="{{ $logoSrc }}" alt="{{ $sponsor->name }}" class="sponsor-profile-logo">

                        <div class="label-title">Category</div>
                        <div class="label-value">{{ $sponsor->sponsorCategory->name ?? '-' }}</div>

                        @if($subCategoryText !== '')
                            <div class="label-title">Sub Category</div>
                            <div class="label-value">{{ $subCategoryText }}</div>
                        @endif

                        <div class="label-title">Name</div>
                        <div class="label-value">{{ $sponsor->name }}</div>

                        @if(($sponsor->show_website_url ?? true) && $websiteHref !== '')
                            <div class="label-title">Website</div>
                            <div class="label-value"><a href="{{ $websiteHref }}" target="_blank" rel="noopener">{{ $websiteHref }}</a></div>
                        @endif

                        @if(($sponsor->show_linkedin_url ?? true) && $linkedinHref !== '')
                            <div class="label-title">Follow</div>
                            <div class="label-value">
                                <a href="{{ $linkedinHref }}" target="_blank" rel="noopener"><i class="fa-brands fa-linkedin me-1"></i> LinkedIn</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-9 sponsor-profile-main">
                    <h2>{{ strtoupper($sponsor->name) }}</h2>

                    @if(($sponsor->show_contact_details ?? true) && ($sponsor->contact_name || $sponsor->contact_designation || $sponsor->contact_mobile || $sponsor->contact_email))
                        <h5 class="mb-3">Contact Us - Sponsor Details</h5>
                        <div class="row g-3 mb-3">
                            @if($sponsor->contact_name)
                                <div class="col-md-6"><strong>Name:</strong> {{ $sponsor->contact_name }}</div>
                            @endif
                            @if($sponsor->contact_designation)
                                <div class="col-md-6"><strong>Designation:</strong> {{ $sponsor->contact_designation }}</div>
                            @endif
                            @if($sponsor->contact_mobile)
                                <div class="col-md-6"><strong>Mobile:</strong> {{ $sponsor->contact_mobile }}</div>
                            @endif
                            @if($sponsor->contact_email)
                                <div class="col-md-6"><strong>Email:</strong> {{ $sponsor->contact_email }}</div>
                            @endif
                        </div>
                    @endif

                    <div class="mb-4">
                        @if(($sponsor->show_advert ?? true) && $advertHref !== '')
                            <a href="{{ $advertHref }}" target="_blank" rel="noopener" class="download-btn download-btn-advert" download>
                                <i class="fa fa-download me-2" aria-hidden="true"></i>Download Advert
                            </a>
                        @endif
                        @if($brochureHref !== '')
                            <a href="{{ $brochureHref }}" target="_blank" rel="noopener" class="download-btn download-btn-brochure" download>
                                <i class="fa fa-download me-2" aria-hidden="true"></i>Download Brochure
                            </a>
                        @endif
                    </div>

                    @if(($sponsor->show_bio ?? true) && $sponsor->detail)
                        <div class="mb-4">{!! $sponsor->detail !!}</div>
                    @endif

                    @if($videoEmbedUrl !== '')
                        <div class="mb-4">
                            <h5 class="mb-2">Sponsor Video</h5>
                            <div class="ratio ratio-16x9">
                                <iframe src="{{ $videoEmbedUrl }}" title="Sponsor Video" allowfullscreen loading="lazy"></iframe>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($speakers->isNotEmpty())
                <hr class="mt-4 mb-4">
                <div class="speaker-autoscroll-header">
                    <h4 class="speaker-autoscroll-title">Eminent Speakers</h4>
                    <span class="speaker-autoscroll-line" aria-hidden="true"></span>
                </div>
                <div class="speaker-autoscroll">
                    <div class="speaker-autoscroll-track">
                        @foreach([$speakers, $speakers] as $speakerLoop)
                            @foreach($speakerLoop as $speaker)
                                @php
                                    $speakerPhotoSrc = $speaker->display_photo_url;
                                @endphp
                                <a href="{{ route('frontend.speakers.show', $speaker) }}" class="speaker-chip">
                                    <span class="speaker-chip-image">
                                        <img src="{{ $speakerPhotoSrc }}" alt="{{ $speaker->name }}">
                                    </span>
                                    <span class="speaker-chip-meta">
                                        <span class="speaker-chip-name">{{ $speaker->name }}</span>
                                        <span class="speaker-chip-role">{{ $speaker->company_name ?: 'Company Name' }}</span>
                                    </span>
                                </a>
                            @endforeach
                        @endforeach
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
