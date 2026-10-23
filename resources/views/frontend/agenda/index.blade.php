<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $settings['title'] ?? 'Agenda';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $theme = ($settings['theme_color'] ?? 'blue') === 'black' ? 'black' : 'blue';
        $themeBg = $theme === 'black' ? '#0b0b0f' : '#10154d';
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
        body.agenda-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.agenda-page > footer { margin-top: 0 !important; }
        body.agenda-page {
            background: #f6f7fb;
            color: #101010;
        }
        .agenda-hero {
            background: #f5f6f8;
            color: #111;
            padding: 120px 0 40px 0;
        }
        body.agenda-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.agenda-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.agenda-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.agenda-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        body.agenda-page .inside-page-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 0;
        }
        .agenda-world-section {
            position: relative;
            overflow: hidden;
        }
        .agenda-world-map {
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
        .agenda-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .agenda-day-picker-label {
            display: inline-flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(16, 21, 77, 0.06);
            color: #495057;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .agenda-day-circles {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 12px;
        }
        .agenda-day-circle {
            width: 130px;
            height: 130px;
            border-radius: 999px;
            border: 2px solid transparent;
            color: #fff;
            background: var(--agenda-day-gradient, linear-gradient(135deg, #6b7bff, #66d1ff));
            box-shadow: 0 14px 28px rgba(54, 72, 143, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease, border-color .2s ease;
            opacity: 0.7;
        }
        .agenda-day-circle:hover {
            transform: translateY(-2px);
            color: #fff;
            text-decoration: none;
            opacity: 0.88;
        }
        .agenda-day-circle.active {
            transform: translateY(-4px) scale(1.04);
            color: #fff;
            text-decoration: none;
            opacity: 1;
            border-color: rgba(255, 255, 255, 0.95);
            box-shadow:
                0 0 0 4px rgba(111, 70, 245, 0.28),
                0 18px 34px rgba(54, 72, 143, 0.32);
            filter: brightness(1.08);
        }
        .agenda-day-circle strong {
            font-size: 24px;
            line-height: 1;
        }
        .agenda-day-circle span {
            font-size: 12px;
            opacity: .9;
            margin-top: 5px;
            text-transform: uppercase;
        }
        .agenda-main {
            margin-top: 26px;
        }
        .agenda-time-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 18px;
        }
        .agenda-time-tab {
            background: {{ $themeBg }};
            color: #fff;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 700;
            font-size: 15px;
            border: 0;
            cursor: pointer;
        }
        .agenda-time-tab.active {
            background: linear-gradient(135deg, #6f46f5, #4c78ff);
        }
        .agenda-card {
            background: #fff;
            border: 1px solid rgba(16, 21, 77, 0.1);
            border-radius: 10px;
            padding: 18px 18px 18px 0;
            margin-bottom: 16px;
            overflow: hidden;
            display: flex;
            align-items: stretch;
        }
        .agenda-card-rail {
            width: 8px;
            flex: 0 0 8px;
            background: var(--agenda-day-solid, #6b7bff);
            margin-right: 18px;
            border-radius: 0 8px 8px 0;
        }
        .agenda-card-inner {
            flex: 1 1 auto;
        }
        .agenda-time {
            color: #6f46f5;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .agenda-day-meta {
            color: #5e6779;
            font-size: 13px;
            line-height: 1.5;
            font-weight: 600;
        }
        .agenda-side-panel {
            min-width: 200px;
            max-width: 220px;
            padding-right: 18px;
        }
        .agenda-main-panel {
            flex: 1 1 auto;
        }
        .agenda-moderator-card {
            margin-top: 16px;
            padding: 12px;
            border-radius: 12px;
            background: #f7f8fc;
            border: 1px solid #e4e9f2;
        }
        .agenda-moderator-card img {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            object-fit: cover;
            display: block;
            margin-bottom: 8px;
        }
        .agenda-moderator-name {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            margin-bottom: 2px;
        }
        .agenda-moderator-role {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #6f46f5;
        }
        .agenda-file-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .agenda-download-btn {
            margin-top: 0;
        }
        .agenda-file-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: #efe8ff;
            color: #5f33df;
            text-decoration: none;
            transition: background .2s ease, transform .2s ease;
        }
        .agenda-file-icon:hover,
        .agenda-file-icon:focus {
            background: #dfd1ff;
            color: #5f33df;
            text-decoration: none;
            transform: translateY(-1px);
        }
        .agenda-speaker {
            text-align: center;
            width: 94px;
        }
        .agenda-speaker img {
            width: 68px;
            height: 68px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 6px;
        }
        .agenda-speaker-name {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: 2px;
        }
        .agenda-speaker-company {
            font-size: 11px;
            line-height: 1.2;
            color: #6b7280;
            min-height: 13px;
            margin-bottom: 6px;
        }
        .agenda-speakers-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }
        .agenda-label {
            color: #495057;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .agenda-slot-panel {
            display: none;
        }
        .agenda-slot-panel.active {
            display: block;
        }
        .agenda-room-title {
            font-size: 22px;
            margin-bottom: 10px;
            color: #111;
        }
        .agenda-scroll-speaker-strip {
            margin-top: 44px;
        }
        .agenda-scroll-speaker-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 20px;
        }
        .agenda-scroll-speaker-title {
            margin: 0;
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
            color: #171717;
            white-space: nowrap;
        }
        .agenda-scroll-speaker-line {
            height: 3px;
            flex: 1 1 auto;
            border-radius: 999px;
            background: linear-gradient(90deg, #7c4dff 0%, #b39dff 100%);
        }
        .agenda-scroll-speaker-wrap {
            overflow: hidden;
            position: relative;
        }
        .agenda-scroll-speaker-track {
            display: flex;
            width: max-content;
            animation: agendaSpeakerScroll 35s linear infinite;
        }
        .agenda-scroll-speaker-wrap:hover .agenda-scroll-speaker-track {
            animation-play-state: paused;
        }
        .agenda-scroll-speaker-card {
            display: block;
            margin-right: 18px;
            width: 170px;
            min-width: 170px;
            text-decoration: none;
            color: #171717;
            line-height: 1;
        }
        .agenda-scroll-speaker-image {
            display: block;
            border-radius: 8px;
            overflow: hidden;
            background: #edf1f6;
            box-shadow: 0 12px 30px rgba(20, 24, 45, 0.08);
            margin-bottom: 8px;
        }
        .agenda-scroll-speaker-image img {
            width: 100%;
            height: 168px;
            object-fit: cover;
            display: block;
        }
        .agenda-scroll-speaker-meta {
            display: block;
            padding: 0;
            text-align: center;
            line-height: 1.1;
        }
        .agenda-scroll-speaker-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
            color: #1b1b1b;
            display: block;
        }
        .agenda-scroll-speaker-role {
            font-size: 14px;
            color: #555;
            margin: 2px 0 0;
            line-height: 1.2;
            display: block;
        }
        @keyframes agendaSpeakerScroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @media (max-width: 768px) {
            .agenda-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: 0.18;
            }
            .agenda-day-circles {
                justify-content: flex-start;
            }
            .agenda-card {
                padding-right: 14px;
            }
            .agenda-card .row {
                display: block;
            }
            .agenda-side-panel {
                max-width: none;
                min-width: 0;
                padding-right: 0;
                margin-bottom: 14px;
            }
            .agenda-scroll-speaker-title {
                font-size: 24px;
            }
            .agenda-scroll-speaker-card {
                width: 145px;
                min-width: 145px;
            }
            .agenda-scroll-speaker-image img {
                height: 145px;
            }
        }
    </style>
</head>
<body class="agenda-page">
@php
    $agendaDayPalette = [
        ['solid' => '#5f46ff', 'gradient' => 'linear-gradient(135deg, #5f46ff, #79b4ff)'],
        ['solid' => '#e85f5f', 'gradient' => 'linear-gradient(135deg, #e85f5f, #ffb36b)'],
        ['solid' => '#14a37f', 'gradient' => 'linear-gradient(135deg, #14a37f, #62d9a8)'],
    ];
    $agendaDayStyles = collect($days ?? [])->values()->mapWithKeys(function ($day, $index) use ($agendaDayPalette) {
        return [$day->id => $agendaDayPalette[$index % count($agendaDayPalette)]];
    });
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
    <div class="float-text show-on-scroll">
        <span><a href="#">Scroll to top</a></span>
    </div>
    <div class="scrollbar-v show-on-scroll"></div>
    <div id="de-loader"></div>

    @include('frontend.partials.header')

    <section class="agenda-hero agenda-world-section">
        <div class="agenda-world-map" aria-hidden="true"></div>
        <div class="container">
            <div class="row align-items-start">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    @php
                        $agendaHeroButtons = [];
                        if (!empty($settings['agenda_download_url']) && $settings['agenda_download_url'] !== '#') {
                            $agendaHeroButtons[] = [
                                'type' => 'link',
                                'text' => $settings['agenda_download_text'] ?? 'Download Agenda',
                                'class' => 'btn-main text-uppercase',
                                'href' => $assetHref($settings['agenda_download_url']),
                                'target' => '_blank',
                                'rel' => 'noopener',
                            ];
                        }
                    @endphp
                    @include('frontend.partials.inner-page-hero', [
                        'heroLabel' => $settings['subtitle'] ?? 'SCHEDULE DETAILS',
                        'heroTitle' => $settings['title'] ?? 'INFORMATION OF EVENT SCHEDULES',
                        'heroSuffix' => $settings['title_suffix'] ?? '',
                        'heroDescription' => $settings['small_description'] ?? '',
                        'heroButtons' => $agendaHeroButtons,
                    ])
                </div>
                <div class="col-lg-6 text-lg-end">
                    <?php if ($days->isNotEmpty()): ?>
                        <div class="agenda-day-picker-label">Please select the day</div>
                        <div class="agenda-day-circles">
                            <?php foreach ($days as $day): ?>
                                <?php $dayStyle = $agendaDayStyles[$day->id] ?? $agendaDayPalette[0]; ?>
                                <a class="agenda-day-circle {{ (int) $activeDayId === (int) $day->id ? 'active' : '' }}"
                                   style="--agenda-day-solid: {{ $dayStyle['solid'] }}; --agenda-day-gradient: {{ $dayStyle['gradient'] }};"
                                   href="{{ route('frontend.agenda.index', ['event_id' => $event?->id, 'day_id' => $day->id]) }}">
                                    <strong>{{ $day->title }}</strong>
                                    <span>{{ $day->weekday }}</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="pt-40 pb-80">
        <div class="container">
            <?php if (! $event): ?>
                <div class="alert alert-info">No agenda event configured yet.</div>
            <?php else: ?>
                <div class="agenda-main">
                    @php
                        $timeSlots = $items
                            ->map(function ($item) {
                                $start = trim((string) ($item->start_time ?? ''));
                                $end = trim((string) ($item->end_time ?? ''));
                                $label = trim($start . ($end !== '' ? ' - ' . $end : ''));
                                return [
                                    'key' => md5($label),
                                    'label' => $label === '' ? 'Untimed Session' : $label,
                                    'start' => $start,
                                ];
                            })
                            ->unique('label')
                            ->values();

                        if ($timeSlots->isEmpty()) {
                            $timeSlots = collect([[
                                'key' => 'no-time',
                                'label' => 'Untimed Session',
                                'start' => '',
                            ]]);
                        }
                    @endphp

                    <div class="agenda-time-tabs" id="agenda-time-tabs">
                        <?php foreach ($timeSlots as $slotIndex => $slot): ?>
                            <button
                                type="button"
                                class="agenda-time-tab {{ $slotIndex === 0 ? 'active' : '' }}"
                                data-slot-target="slot-{{ $slot['key'] }}"
                            >
                                {{ $slot['label'] }}
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <?php foreach ($timeSlots as $slotIndex => $slot): ?>
                        @php
                            $slotItems = $items->filter(function ($item) use ($slot) {
                                $start = trim((string) ($item->start_time ?? ''));
                                $end = trim((string) ($item->end_time ?? ''));
                                $label = trim($start . ($end !== '' ? ' - ' . $end : ''));
                                $finalLabel = $label === '' ? 'Untimed Session' : $label;
                                return $finalLabel === $slot['label'];
                            })->values();
                        @endphp
                        <div class="agenda-slot-panel {{ $slotIndex === 0 ? 'active' : '' }}" id="slot-{{ $slot['key'] }}">
                            <?php foreach ($rooms as $room): ?>
                                @php
                                    $roomItems = $slotItems->where('agenda_room_id', $room->id)->values();
                                @endphp
                                <?php if ($roomItems->isNotEmpty()): ?>
                                    <h4 class="agenda-room-title">{{ $room->name }}</h4>
                                <?php endif; ?>
                                <?php foreach ($roomItems as $item): ?>
                                    @php
                                        $itemDay = $days->firstWhere('id', $item->agenda_day_id);
                                        $itemDayStyle = $agendaDayStyles[$item->agenda_day_id] ?? $agendaDayPalette[0];
                                        $itemTime = trim((string) ($item->start_time ?? '') . (($item->end_time ?? '') ? ' - ' . $item->end_time : ''));
                                        $itemDate = optional($itemDay?->agenda_date)->format('d M Y');
                                        $itemDayText = trim(($itemDate ?: '') . (($itemDay?->weekday) ? ' - ' . $itemDay->weekday : ''));
                                        $moderatorProfile = $item->moderatorSpeaker;
                                        $moderatorPhotoSrc = $moderatorProfile
                                            ? $moderatorProfile->display_photo_url
                                            : asset('images/team/1.webp');
                                        $moderatorName = $moderatorProfile?->name ?: ($item->moderator_name ?: '');
                                        $moderatorFileHref = $assetHref($item->moderator_ppt_url);
                                        $sessionFileHref = $assetHref($item->agenda_file_url);
                                    @endphp
                                    <div class="agenda-card" id="agenda-item-{{ $item->id }}" style="--agenda-day-solid: {{ $itemDayStyle['solid'] }};">
                                        <div class="agenda-card-rail"></div>
                                        <div class="agenda-card-inner">
                                            <div class="row">
                                                <div class="col-lg-3 agenda-side-panel">
                                                    <div class="agenda-label">Time</div>
                                                    <div class="agenda-time">{{ $itemTime ?: '-' }}</div>
                                                    <?php if ($itemDayText !== ''): ?>
                                                        <div class="agenda-day-meta">{{ $itemDayText }}</div>
                                                    <?php endif; ?>

                                                    <?php if ($moderatorName !== ''): ?>
                                                        <div class="agenda-moderator-card">
                                                            <div class="agenda-label mb-2">Moderator</div>
                                                            <?php if ($moderatorProfile): ?>
                                                                <a href="{{ route('frontend.speakers.show', $moderatorProfile->id) }}">
                                                                    <img src="{{ $moderatorPhotoSrc }}" alt="{{ $moderatorName }}">
                                                                </a>
                                                                <a href="{{ route('frontend.speakers.show', $moderatorProfile->id) }}" style="text-decoration:none;color:inherit;">
                                                                    <div class="agenda-moderator-name">{{ $moderatorName }}</div>
                                                                </a>
                                                            <?php else: ?>
                                                                <img src="{{ $moderatorPhotoSrc }}" alt="{{ $moderatorName }}">
                                                                <div class="agenda-moderator-name">{{ $moderatorName }}</div>
                                                            <?php endif; ?>
                                                            <?php if ($moderatorProfile?->company_name): ?>
                                                                <div class="agenda-speaker-company mb-0">{{ $moderatorProfile->company_name }}</div>
                                                            <?php endif; ?>
                                                            <?php if ($moderatorFileHref !== ''): ?>
                                                                <a class="agenda-file-icon mt-2" href="{{ $moderatorFileHref }}" target="_blank" rel="noopener" download aria-label="Download moderator presentation">
                                                                    <i class="fa fa-download"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-lg-9 agenda-main-panel">
                                                @php
                                                    $titleSpeakerLink = null;
                                                    if ($item->speakers->count() === 1) {
                                                        $titleSpeakerLink = route('frontend.speakers.show', $item->speakers->first()->id);
                                                    } elseif ($item->speakers->isEmpty() && $moderatorProfile) {
                                                        $titleSpeakerLink = route('frontend.speakers.show', $moderatorProfile->id);
                                                    }
                                                @endphp
                                                <h5 class="mb-1">
                                                    @if($titleSpeakerLink)
                                                        <a href="{{ $titleSpeakerLink }}" style="text-decoration:none;color:inherit;">{{ $item->title }}</a>
                                                    @else
                                                        {{ $item->title }}
                                                    @endif
                                                </h5>
                                                <?php if (! empty($item->panel_theme)): ?>
                                                    <div class="agenda-label mb-1">Theme: {{ $item->panel_theme }}</div>
                                                <?php endif; ?>
                                                <?php if (! empty($item->topic_description)): ?>
                                                    <p class="mb-2">{{ $item->topic_description }}</p>
                                                <?php endif; ?>

                                                <?php if ($item->speakers->isNotEmpty()): ?>
                                                    <div class="agenda-speakers-wrap">
                                                        <?php foreach ($item->speakers as $speaker): ?>
                                                            @php
                                                                $photoSrc = $speaker->display_photo_url;
                                                                $speakerPresentationHref = $assetHref($speaker->presentation_file_url);
                                                            @endphp
                                                            <div class="agenda-speaker">
                                                                <a href="{{ route('frontend.speakers.show', $speaker->id) }}">
                                                                    <img src="{{ $photoSrc }}" alt="{{ $speaker->name }}">
                                                                </a>
                                                                <div class="agenda-speaker-name">{{ $speaker->name }}</div>
                                                                <div class="agenda-speaker-company">{{ $speaker->company_name ?: '' }}</div>
                                                                <?php if ($speakerPresentationHref !== ''): ?>
                                                                    <a class="agenda-file-icon" href="{{ $speakerPresentationHref }}" target="_blank" rel="noopener" download aria-label="Download presentation for {{ $speaker->name }}">
                                                                        <i class="fa fa-download"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="agenda-file-actions">
                                                    <?php if ($sessionFileHref !== ''): ?>
                                                        <a class="btn-main fx-slide agenda-download-btn" href="{{ $sessionFileHref }}" target="_blank" rel="noopener" download><span>Download Session File</span></a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <?php if ($slotItems->isEmpty()): ?>
                                <div class="alert alert-light border">No sessions in this time slot.</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            @if(($speakers ?? collect())->isNotEmpty())
                <div class="agenda-scroll-speaker-strip">
                    <div class="agenda-scroll-speaker-header">
                        <h4 class="agenda-scroll-speaker-title">Eminent Speakers</h4>
                        <span class="agenda-scroll-speaker-line" aria-hidden="true"></span>
                    </div>
                    <div class="agenda-scroll-speaker-wrap">
                        <div class="agenda-scroll-speaker-track">
                            @foreach([$speakers, $speakers] as $speakerLoop)
                                @foreach($speakerLoop as $speaker)
                                    <a href="{{ route('frontend.speakers.show', $speaker) }}" class="agenda-scroll-speaker-card">
                                        <span class="agenda-scroll-speaker-image">
                                            <img src="{{ $speaker->display_photo_url }}" alt="{{ $speaker->name }}">
                                        </span>
                                        <span class="agenda-scroll-speaker-meta">
                                            <span class="agenda-scroll-speaker-name">{{ $speaker->name }}</span>
                                            <span class="agenda-scroll-speaker-role">{{ $speaker->company_name ?: 'Company Name' }}</span>
                                        </span>
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
<script src="{{ asset('js/countdown-custom.js') }}"></script>
<script src="{{ asset('js/custom-marquee.js') }}"></script>
<script>
    (function () {
        var tabsRoot = document.getElementById('agenda-time-tabs');
        if (!tabsRoot) return;

        var tabs = Array.prototype.slice.call(tabsRoot.querySelectorAll('.agenda-time-tab'));
        var panels = Array.prototype.slice.call(document.querySelectorAll('.agenda-slot-panel'));

        function activate(targetId) {
            tabs.forEach(function (tab) {
                tab.classList.toggle('active', tab.getAttribute('data-slot-target') === targetId);
            });
            panels.forEach(function (panel) {
                panel.classList.toggle('active', panel.id === targetId);
            });
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var targetId = tab.getAttribute('data-slot-target');
                activate(targetId);
            });
        });
    })();
</script>
</body>
</html>
