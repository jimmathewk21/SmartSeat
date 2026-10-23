<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $hotel->name . ' | Venue';
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
        body.venue-show-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.venue-show-page > footer { margin-top: 0 !important; }
        body.venue-show-page { background:#f5f6f8; color:#111; }
        body.venue-show-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.venue-show-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.venue-show-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.venue-show-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        body.venue-show-page .inside-page-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 0;
        }
        .venue-show-world-section {
            position: relative;
            overflow: hidden;
        }
        .venue-show-world-map {
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
        .venue-show-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .venue-info-card {
            background:#fff;
            border:1px solid #e4e7ef;
            border-radius:18px;
            padding:28px 28px 22px;
            margin-bottom:22px;
        }
        .venue-topbar {
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:20px;
            margin-bottom:10px;
        }
        .venue-kicker {
            font-size: 14px;
            line-height: 1.2;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #5e3df6;
            margin-bottom: 20px;
        }
        .venue-title {
            margin:0;
            color:#111;
            font-size:28px;
            line-height:1.05;
            font-weight:800;
            text-transform:uppercase;
        }
        .venue-title-row {
            display:flex;
            align-items:center;
            gap:12px;
            flex-wrap:wrap;
            margin-bottom:8px;
        }
        .venue-rating {
            display:flex;
            align-items:center;
            gap:2px;
            color:#f4c430;
            font-size:15px;
        }
        .venue-address {
            color:#1e293b;
            font-size:17px;
            line-height:1.35;
            font-weight:700;
            margin-bottom: 16px;
        }
        .venue-address span {
            font-weight:500;
        }
        .venue-direction-btn {
            min-width:138px;
            justify-content:center;
            white-space:nowrap;
        }
        .venue-content-row {
            align-items:flex-start;
        }
        .venue-paragraph {
            color:#1f2937;
            font-size:17px;
            line-height:1.7;
            margin:0;
            white-space:pre-line;
            max-width:none;
            text-align:left;
            padding-top: 0;
        }
        .venue-main-image {
            width:100%;
            height:260px;
            object-fit:cover;
            border-radius:18px;
            cursor:pointer;
            box-shadow:0 12px 28px rgba(15, 23, 42, 0.10);
        }
        .venue-gallery-row {
            margin-top:22px;
        }
        .gallery-image {
            width:100%;
            height:88px;
            object-fit:cover;
            border-radius:14px;
            cursor:pointer;
            transition:transform .2s ease, box-shadow .2s ease;
        }
        .gallery-image:hover {
            transform:translateY(-2px);
            box-shadow:0 10px 20px rgba(15, 23, 42, 0.12);
        }
        .venue-show-page .gallery-modal .modal-content {
            border:0;
            border-radius:18px;
            overflow:hidden;
            background:#10172b;
        }
        .venue-show-page .gallery-modal .modal-header {
            background:#141a3a;
            border-bottom:0;
            padding:16px 20px;
        }
        .venue-show-page .gallery-modal .modal-title {
            color:#fff;
            font-weight:700;
        }
        .venue-show-page .gallery-modal .btn-close {
            filter:invert(1);
            opacity:.85;
        }
        .venue-show-page .gallery-modal .modal-body {
            padding:18px;
            text-align:center;
            background:#0f172a;
            overflow: auto;
        }
        .venue-show-page .gallery-zoom-stage {
            width: 100%;
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto;
            cursor: zoom-in;
        }
        .venue-show-page .gallery-zoom-stage.is-zoomed {
            cursor: zoom-out;
        }
        .venue-show-page .gallery-modal img {
            width:100%;
            max-height:70vh;
            object-fit:contain;
            border-radius:12px;
            transform-origin:center center;
            transition:transform .18s ease-out;
        }
        .venue-show-page .gallery-modal .gallery-modal-toolbar {
            display:flex;
            justify-content:flex-end;
            gap:8px;
            margin-bottom:10px;
        }
        .venue-show-page .gallery-modal .gallery-modal-zoom-btn {
            border:1px solid rgba(255,255,255,.28);
            background:#1e293b;
            color:#fff;
            border-radius:8px;
            width:34px;
            height:34px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            font-size:16px;
            line-height:1;
        }
        .ticket-currency-switch {
            display: inline-flex;
            align-items: center;
            padding: 4px;
            border-radius: 999px;
            background: #eef2ff;
            border: 1px solid #d8e1fb;
            gap: 4px;
        }
        .ticket-currency-btn {
            border: none;
            background: transparent;
            color: #52617d;
            border-radius: 999px;
            min-width: 74px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            transition: all .18s ease;
        }
        .ticket-currency-btn.is-active {
            background: linear-gradient(90deg, #6f46f5, #3a72c8);
            color: #fff;
            box-shadow: 0 8px 18px rgba(62, 84, 168, 0.22);
        }
        .ticket-currency-btn:focus-visible {
            outline: 2px solid rgba(58, 114, 200, 0.55);
            outline-offset: 1px;
        }
        .room-card { background:#fff; border:1px solid #e4e7ef; border-radius:14px; padding:14px 18px; margin-bottom:16px; }
        .room-image-wrap { position:relative; width:100%; max-width:240px; height:100%; }
        .room-section-toolbar {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 10px;
            margin: 0 auto 20px;
            flex-wrap: wrap;
        }
        .room-section-toolbar-note {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
            text-align: center;
        }
        .room-section-heading {
            color:#111;
            font-size:54px;
            line-height:1;
            font-weight:800;
            text-transform:uppercase;
            margin-bottom:18px;
        }
        .room-section-text {
            max-width:980px;
            margin:0 auto 22px;
            color:#1f2937;
            font-size:17px;
            line-height:1.55;
            text-align:left;
        }
        .room-card { padding:14px 18px; }
        .room-image { width:100%; max-width:240px; height:100%; min-height:100%; object-fit:cover; border-radius:12px; cursor:pointer; display:block; }
        .room-badge {
            position:absolute;
            right:10px;
            top:-8px;
            background:linear-gradient(90deg, #8b5cf6 0%, #6d4aff 100%);
            color:#fff;
            font-size:10px;
            line-height:1.15;
            font-weight:700;
            padding:5px 9px;
            border-radius:8px;
            text-align:center;
            box-shadow:0 8px 18px rgba(109, 74, 255, 0.18);
            white-space:pre-line;
        }
        .room-title { color:#111; font-size:28px; line-height:1.05; margin:0; font-weight:800; }
        .room-title-block {
            border-bottom:1px solid #1f2937;
            padding-bottom:8px;
            margin-bottom:10px;
            align-items:flex-start !important;
        }
        .room-caption { color:#475569; font-size:13px; line-height:1.4; margin-top:2px; margin-bottom:10px; }
        .room-size { color:#475569; font-size:13px; font-weight:600; }
        .room-benefits { font-size:14px; color:#1f2937; line-height:1.45; text-align:left; }
        .room-benefits ul { padding-left:18px; margin:6px 0 0; }
        .room-price-col { border-left:1px solid #cfd6e4; padding-left:18px; text-align:left; }
        .room-card-row { align-items:stretch; }
        .room-rate-line {
            display:flex;
            align-items:flex-start;
            gap:10px;
            font-size:14px;
            color:#111;
            margin-bottom:12px;
        }
        .room-rate-line i {
            color:#64748b;
            width:18px;
            text-align:center;
            font-size:16px;
            padding-top:3px;
        }
        .room-rate-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .room-rate-label {
            color: #111827;
            font-size: 18px;
            line-height: 1.2;
            font-weight: 700;
        }
        .room-rate-price {
            color: #111827;
            font-size: 18px;
            line-height: 1.3;
            font-weight: 500;
        }
        .room-rate-price[data-currency="USD"] {
            display: none;
        }
        .room-card[data-currency="USD"] .room-rate-price[data-currency="INR"],
        .room-card[data-currency="INR"] .room-rate-price[data-currency="USD"] {
            display: none;
        }
        .room-card[data-currency="USD"] .room-rate-price[data-currency="USD"],
        .room-card[data-currency="INR"] .room-rate-price[data-currency="INR"] {
            display: block;
        }
        .room-rate-gst { font-size:11px; line-height:15px; color:#475569; font-weight:700; margin:8px 0 10px; }
        .room-rate-main { font-size:28px; font-weight:800; color:#111; line-height:1; margin-bottom:12px; }
        .room-rate-caption { font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; margin-top:-6px; margin-bottom:12px; }
        .room-select-btn { min-width:116px; justify-content:center; }
        @media (max-width: 991px) {
            .venue-topbar {
                flex-direction:column;
                align-items:flex-start;
            }
            .venue-direction-btn {
                min-width:0;
            }
            .venue-address { font-size:20px; }
            .room-section-heading { font-size:38px; }
            .room-title { font-size:24px; }
            .room-rate-main { font-size:24px; }
            .room-price-col { border-left:0; padding-left:0; border-top:1px solid #cfd6e4; padding-top:14px; margin-top:14px; }
        }
        @media (max-width: 768px) {
            .venue-info-card {
                padding:22px 18px 18px;
            }
            .venue-title {
                font-size:22px;
            }
            .venue-address {
                font-size:16px;
            }
            .venue-main-image {
                height:220px;
                margin-top:10px;
            }
            .gallery-image {
                height:78px;
            }
            .room-section-heading {
                font-size:28px;
            }
            .room-section-text {
                font-size:15px;
                margin-bottom:18px;
            }
            .room-rate-label,
            .room-rate-price {
                font-size: 16px;
            }
            .venue-show-world-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: 0.18;
            }
        }
    </style>
</head>
<body class="venue-show-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80 venue-show-world-section">
        <div class="venue-show-world-map" aria-hidden="true"></div>
        <div class="container">
            @php
                $hero = trim((string) ($hotel->hotel_image ?? ''));
                $heroAbs = str_starts_with($hero, 'http://') || str_starts_with($hero, 'https://') || str_starts_with($hero, '//') || str_starts_with($hero, '/');
                $heroSrc = $hero !== '' ? ($heroAbs ? $hero : asset($hero)) : asset('images/misc/1.webp');
                $starCount = max(0, min(5, (int) ($hotel->stars ?? 0)));
                $addressText = $hotel->hotel_address ?: (($hotel->city ?: '') . (($hotel->country ?: '') ? ', ' . $hotel->country : ''));
                $galleryItems = array_values(array_unique(array_filter(array_merge([$heroSrc], $gallery))));
                $hotelDescriptionText = trim((string) ($hotel->hotel_description ?: 'Please note that these special rates are available for limited inventory and offered on first-come, first-served basis. Book early to secure preferential pricing for conference dates.'));
                $hotelDescriptionHasHtml = \Illuminate\Support\Str::contains($hotelDescriptionText, ['<p', '<br', '<ul', '<ol', '<strong', '<em', '<span', '<div']);
                $hotelDescription = $hotelDescriptionHasHtml ? strip_tags($hotelDescriptionText, '<p><br><strong><em><ul><ol><li><a><span><div><b><i><u>') : nl2br(e($hotelDescriptionText));

                $roomSectionTextValue = trim((string) $roomSectionText);
                $roomSectionTextHasHtml = \Illuminate\Support\Str::contains($roomSectionTextValue, ['<p', '<br', '<ul', '<ol', '<strong', '<em', '<span', '<div']);
                $roomSectionTextHtml = $roomSectionTextHasHtml ? strip_tags($roomSectionTextValue, '<p><br><strong><em><ul><ol><li><a><span><div><b><i><u>') : nl2br(e($roomSectionTextValue));
            @endphp
            <div class="venue-info-card">
                <div class="venue-topbar">
                    <div>
                        <div class="venue-kicker">[ EVENT VENUE ]</div>
                        <div class="venue-title-row">
                            <h1 class="venue-title">{{ strtoupper((string) $hotel->name) }}</h1>
                            @if($starCount > 0)
                                <div class="venue-rating" aria-label="{{ $starCount }} star rating">
                                    @for($i = 0; $i < $starCount; $i++)
                                        <i class="fa fa-star" aria-hidden="true"></i>
                                    @endfor
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($directionUrl !== '')
                        <a href="{{ $directionUrl }}" target="_blank" rel="noopener noreferrer" class="btn-main fx-slide venue-direction-btn">
                            <span>DIRECTION</span>
                        </a>
                    @endif
                </div>

                <div class="row g-4 venue-content-row"> 
                    <div class="col-lg-7">
                        <div class="venue-address">Address: <span>{{ $addressText }}</span></div>
                        <div class="venue-paragraph">{!! $hotelDescription !!}</div>
                    </div>
                    <div class="col-lg-5">
                        <img src="{{ $heroSrc }}" alt="{{ $hotel->name }}" class="venue-main-image js-gallery-trigger" data-gallery-src="{{ $heroSrc }}">
                    </div>
                </div>

                @if(count($galleryItems))
                    <div class="row g-3 venue-gallery-row">
                        @foreach($galleryItems as $image)
                            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                                <img src="{{ $image }}" alt="Hotel Gallery" class="gallery-image js-gallery-trigger" data-gallery-src="{{ $image }}">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="text-center mb-4">
                <h2 class="room-section-heading">{{ $roomSectionHeading }}</h2>
                <div class="room-section-text">{!! $roomSectionTextHtml !!}</div>
                <div class="room-section-toolbar">
                    <div class="ticket-currency-switch" role="tablist" aria-label="Select room rate currency">
                        <button type="button" class="ticket-currency-btn is-active" data-venue-currency-toggle="INR" role="tab" aria-selected="true">INR</button>
                        <button type="button" class="ticket-currency-btn" data-venue-currency-toggle="USD" role="tab" aria-selected="false">USD</button>
                    </div>
                    <div class="room-section-toolbar-note">Switch the room-rate display before you select a room.</div>
                </div>
            </div>

            @forelse($rooms as $room)
                <div class="room-card" data-currency="INR">
                    <div class="row g-4 room-card-row">
                        <div class="col-lg-3">
                            <div class="room-image-wrap">
                                <img src="{{ $room['image'] }}" alt="{{ $room['name'] }}" class="room-image js-gallery-trigger" data-gallery-src="{{ $room['image'] }}">
                                @if($room['badge'] !== '')
                                    <div class="room-badge">{{ $room['badge'] }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex align-items-end gap-2 room-title-block">
                                <h3 class="room-title">{{ $room['name'] }}</h3>
                                @if($room['size'] !== '')
                                    <div class="room-size">{{ $room['size'] }}</div>
                                @endif
                            </div>
                            @if($room['caption'] !== '')
                                <div class="room-caption">{{ $room['caption'] }}</div>
                            @endif
                            <div class="room-benefits">
                                <strong>Benefits:</strong>
                                @if(count($room['benefits']))
                                    <ul class="mb-0 mt-1">
                                        @foreach($room['benefits'] as $benefit)
                                            <li>{{ $benefit }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="mt-1">-</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-3 room-price-col">
                            <div class="room-rate-line">
                                <i class="fa fa-user" aria-hidden="true"></i>
                                <div class="room-rate-text">
                                    <span class="room-rate-label">Single Occupancy Rate</span>
                                    <span class="room-rate-price" data-currency="INR">INR {{ number_format((float) $room['single_rate_inr'], 0) }}* per night</span>
                                    <span class="room-rate-price" data-currency="USD">USD {{ number_format((float) $room['single_rate_usd'], 0) }}* per night</span>
                                </div>
                            </div>
                            <div class="room-rate-line">
                                <i class="fa fa-users" aria-hidden="true"></i>
                                <div class="room-rate-text">
                                    <span class="room-rate-label">Double Occupancy Rate</span>
                                    <span class="room-rate-price" data-currency="INR">INR {{ number_format((float) $room['double_rate_inr'], 0) }}* per night</span>
                                    <span class="room-rate-price" data-currency="USD">USD {{ number_format((float) $room['double_rate_usd'], 0) }}* per night</span>
                                </div>
                            </div>
                            <div class="room-rate-gst">{{ $taxNoteText }}</div>
                            <a
                                href="{{ route('frontend.venue.reserve', ['venueHotel' => $hotel->id, 'room' => $room['index']]) }}"
                                data-room-link
                                class="btn-main fx-slide room-select-btn"
                            ><span>Select Room</span></a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-light border">No rooms configured for this hotel yet.</div>
            @endforelse
        </div>
    </section>
</div>

@include('frontend.partials.footer')
<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<div class="modal fade gallery-modal" id="venueGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $hotel->name }} Gallery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="gallery-modal-toolbar">
                    <button type="button" class="gallery-modal-zoom-btn" id="venueZoomOutBtn" aria-label="Zoom out">-</button>
                    <button type="button" class="gallery-modal-zoom-btn" id="venueZoomInBtn" aria-label="Zoom in">+</button>
                    <button type="button" class="gallery-modal-zoom-btn" id="venueZoomResetBtn" aria-label="Reset zoom">&#8634;</button>
                </div>
                <div class="gallery-zoom-stage" id="venueGalleryZoomStage">
                    <img src="" alt="{{ $hotel->name }}" id="venueGalleryModalImage">
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const currencyButtons = Array.from(document.querySelectorAll('[data-venue-currency-toggle]'));
        const roomCards = Array.from(document.querySelectorAll('.room-card[data-currency]'));
        const roomLinks = Array.from(document.querySelectorAll('[data-room-link]'));
        const modalElement = document.getElementById('venueGalleryModal');
        const zoomStage = document.getElementById('venueGalleryZoomStage');
        const modalImage = document.getElementById('venueGalleryModalImage');
        const zoomInBtn = document.getElementById('venueZoomInBtn');
        const zoomOutBtn = document.getElementById('venueZoomOutBtn');
        const zoomResetBtn = document.getElementById('venueZoomResetBtn');
        const triggers = document.querySelectorAll('.js-gallery-trigger');
        let zoomScale = 1;

        function setCurrency(currency) {
            const normalized = currency === 'USD' ? 'USD' : 'INR';
            currencyButtons.forEach(function (button) {
                const isActive = button.getAttribute('data-venue-currency-toggle') === normalized;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
            roomCards.forEach(function (card) {
                card.setAttribute('data-currency', normalized);
            });
            roomLinks.forEach(function (link) {
                try {
                    const url = new URL(link.getAttribute('href'), window.location.origin);
                    url.searchParams.set('currency', normalized);
                    link.setAttribute('href', url.pathname + url.search);
                } catch (error) {
                    // Ignore malformed URLs and keep the existing href.
                }
            });
            sessionStorage.setItem('venue_room_currency_{{ $hotel->id }}', normalized);
        }

        if (currencyButtons.length) {
            currencyButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setCurrency(button.getAttribute('data-venue-currency-toggle') || 'INR');
                });
            });
            const initialCurrency = new URLSearchParams(window.location.search).get('currency')
                || sessionStorage.getItem('venue_room_currency_{{ $hotel->id }}')
                || 'INR';
            setCurrency(initialCurrency);
        }

        function applyZoom() {
            modalImage.style.transform = 'scale(' + zoomScale + ')';
            zoomStage.classList.toggle('is-zoomed', zoomScale > 1.01);
        }

        function resetZoom() {
            zoomScale = 1;
            applyZoom();
            zoomStage.scrollTop = 0;
            zoomStage.scrollLeft = 0;
        }

        if (modalElement && modalImage && zoomStage && triggers.length && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            const minZoom = 1;
            const maxZoom = 4;

            function setZoom(nextScale) {
                zoomScale = Math.min(maxZoom, Math.max(minZoom, nextScale));
                applyZoom();
            }

            zoomStage.addEventListener('click', function () {
                setZoom(zoomScale > 1.01 ? 1 : 1.9);
            });

            zoomStage.addEventListener('wheel', function (event) {
                event.preventDefault();
                const delta = event.deltaY > 0 ? -0.15 : 0.15;
                setZoom(zoomScale + delta);
            }, { passive: false });

            modalElement.addEventListener('hidden.bs.modal', resetZoom);

            triggers.forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    const source = trigger.getAttribute('data-gallery-src');
                    if (!source) {
                        return;
                    }

                    modalImage.setAttribute('src', source);
                    resetZoom();
                    modal.show();
                });
            });

            if (zoomInBtn) {
                zoomInBtn.addEventListener('click', function () {
                    setZoom(zoomScale + 0.2);
                });
            }

            if (zoomOutBtn) {
                zoomOutBtn.addEventListener('click', function () {
                    setZoom(zoomScale - 0.2);
                });
            }

            if (zoomResetBtn) {
                zoomResetBtn.addEventListener('click', resetZoom);
            }

            modalImage.addEventListener('wheel', function (event) {
                event.preventDefault();
                setZoom(zoomScale + (event.deltaY < 0 ? 0.15 : -0.15));
            }, { passive: false });
        }
    });
</script>
</body>
</html>
