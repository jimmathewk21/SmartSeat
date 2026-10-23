<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'Hotel Reservation';
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
        body.venue-reserve-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.venue-reserve-page > footer { margin-top: 0 !important; }
        body.venue-reserve-page { background:#f8f9fa; color:#111; }
        body.venue-reserve-page .venue-reserve-world-section {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(111, 70, 245, 0.10), transparent 30%),
                linear-gradient(180deg, #f9fbff 0%, #f6f8fc 100%);
        }
        body.venue-reserve-page .venue-reserve-world-map {
            position: absolute;
            top: 120px;
            left: 50%;
            width: min(1280px, 96vw);
            aspect-ratio: 2.1 / 1;
            transform: translateX(-50%);
            background-image: url('{{ asset('images/worldmap.png') }}');
            background-repeat: no-repeat;
            background-position: center 28px;
            background-size: contain;
            opacity: 0.34;
            pointer-events: none;
            z-index: 0;
        }
        body.venue-reserve-page .venue-reserve-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .reserve-hero {
            display: flex;
            justify-content: space-between;
            gap: 28px;
            align-items: flex-start;
            margin-bottom: 30px;
            padding: 10px 0 14px;
        }
        .reserve-hero-main {
            flex: 1 1 auto;
            min-width: 0;
        }
        .reserve-hero-copy {
            flex: 1 1 auto;
            max-width: 760px;
        }
        .reserve-hero-topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
        }
        .reserve-back-btn {
            display: inline-flex;
            align-items: center;
            min-width: 0;
            min-height: 44px;
            justify-content: center;
            white-space: nowrap;
            border: 1px solid rgba(111, 66, 245, 0.5);
            background: transparent;
            color: #6f42f5;
            padding: 0 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0;
            text-transform: none;
            text-decoration: none;
            line-height: 1;
            box-shadow: none;
        }
        .reserve-back-btn span {
            color: inherit;
            white-space: nowrap;
        }
        .reserve-back-btn:hover {
            background: rgba(111, 66, 245, 0.08);
            border-color: rgba(111, 66, 245, 0.5);
            color: #6f42f5;
            text-decoration: none;
        }
        .reserve-back-btn:hover span {
            color: inherit;
        }
        .reserve-hero-copy .inside-page-hero {
            margin-bottom: 18px;
        }
        .reserve-hero-copy .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        .reserve-hero-copy .inside-page-hero-description {
            max-width: 980px;
        }
        .reserve-hero-aside {
            width: min(360px, 100%);
            padding: 18px;
            border: 1px solid rgba(111, 70, 245, 0.14);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.84);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 54px rgba(18, 29, 55, 0.10);
        }
        .reserve-room-preview {
            display: flex;
            gap: 16px;
            align-items: stretch;
        }
        .reserve-room-preview-image {
            width: 112px;
            min-width: 112px;
            height: 160px;
            border-radius: 18px;
            object-fit: cover;
            box-shadow: 0 14px 28px rgba(18, 29, 55, 0.16);
        }
        .reserve-room-preview-copy {
            flex: 1 1 auto;
            min-width: 0;
        }
        .reserve-room-preview-label {
            display: inline-block;
            margin-bottom: 8px;
            color: #6f46f5;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .reserve-room-preview-title {
            margin: 0 0 6px;
            color: #111827;
            font-size: 24px;
            line-height: 1.1;
            font-weight: 700;
        }
        .reserve-room-preview-text {
            margin: 0;
            color: #516076;
            font-size: 14px;
            line-height: 1.6;
        }
        .reserve-info-chips {
            display: grid;
            gap: 12px;
            margin-top: 16px;
            width: 100%;
        }
        .reserve-info-chip {
            padding: 10px 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(111, 70, 245, 0.10), rgba(58, 114, 200, 0.08));
            color: #223046;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.4;
            width: 100%;
        }
        .reserve-hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
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
        .reserve-currency-toolbar {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 18px;
        }
        .reserve-currency-note {
            color: #607089;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
        }
        .reserve-hero-meta-item {
            min-width: 220px;
            padding: 14px 18px;
            border: 1px solid rgba(111, 70, 245, 0.14);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(8px);
            box-shadow: 0 16px 44px rgba(24, 39, 75, 0.08);
        }
        .reserve-hero-meta-item .label {
            display: block;
            margin-bottom: 6px;
            color: #6f7d92;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .reserve-hero-meta-item strong {
            display: block;
            color: #121826;
            font-size: 17px;
            line-height: 1.45;
            font-weight: 700;
        }
        .reserve-card {
            background: rgba(255, 255, 255, 0.82);
            border:1px solid rgba(215, 222, 237, 0.95);
            border-radius:28px;
            padding:24px;
            backdrop-filter: blur(10px);
            box-shadow: 0 24px 70px rgba(18, 29, 55, 0.08);
        }
        .summary-title { color:#000; margin-bottom:4px; }
        .summary-row { display:flex; justify-content:space-between; border-bottom:1px solid #edf0f6; padding:8px 0; font-size:15px; }
        .summary-row strong { color:#000; }
        .summary-total {
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #dfe7f4;
        }
        .summary-total-label {
            display: block;
            margin-bottom: 6px;
            color: #6a768c;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .summary-total-amount {
            color: #0f1728;
            font-size: 40px;
            line-height: 1;
            font-weight: 700;
        }
        .summary-total-note {
            margin-top: 8px;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }
        .summary-gst-note {
            margin-top: 10px;
            color: #334155;
            font-size: 13px;
            line-height: 1.5;
            font-weight: 600;
        }
        .reserve-section-title {
            margin: 0 0 8px;
            color: #0f1728;
            font-size: 34px;
            line-height: 1.04;
        }
        .reserve-section-subtitle {
            margin: 0 0 18px;
            color: #607089;
            font-size: 15px;
            line-height: 1.6;
        }
        .reserve-form-group {
            margin-bottom: 18px;
        }
        .reserve-form-note {
            display: block;
            margin-top: 6px;
            color: #7b869a;
            font-size: 12px;
            line-height: 1.45;
        }
        .reserve-special-note {
            margin-top: 8px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.45;
        }
        .reserve-special-note strong {
            color: #ef4444;
        }
        .venue-reserve-page .form-control {
            min-height: 54px;
            border-radius: 14px;
            border-color: #d8e1ef;
            background: #fdfefe;
            box-shadow: none;
            color: #122033;
        }
        .venue-reserve-page textarea.form-control {
            min-height: 132px;
        }
        .venue-reserve-page label.form-label {
            display: block;
            margin-bottom: 9px;
            color: #1e293b;
            font-size: 14px;
            font-weight: 700;
        }
        .venue-reserve-page .form-control:focus {
            border-color: #6f46f5;
            box-shadow: 0 0 0 4px rgba(111, 70, 245, 0.10);
        }
        .reserve-readonly-value {
            min-height: 54px;
            display: flex;
            align-items: center;
            border: 1px solid #d8e1ef;
            border-radius: 14px;
            background: #f4f6fb;
            color: #122033;
            padding: 0 14px;
            font-weight: 600;
            line-height: 1.4;
            text-align: left;
        }
        @media (max-width: 991px) {
            .reserve-hero {
                flex-direction: column;
            }
            .reserve-hero-topbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .reserve-hero-aside {
                width: 100%;
            }
        }
        @media (max-width: 767px) {
            body.venue-reserve-page .venue-reserve-world-map {
                top: 140px;
                width: 115vw;
                opacity: 0.16;
            }
            .reserve-room-preview {
                flex-direction: column;
            }
            .reserve-room-preview-image {
                width: 100%;
                min-width: 0;
                height: 220px;
            }
            .reserve-card {
                padding: 20px 16px;
                border-radius: 22px;
            }
        }
    </style>
</head>
<body class="venue-reserve-page">
<div id="wrapper">
    @include('frontend.partials.header')

    @php
        $summaryRateInr = (float) ($selectedRoom['single_rate_inr'] ?? 0);
        $summaryRateUsd = (float) ($selectedRoom['single_rate_usd'] ?? 0);
        $oldOccupancy = old('occupancy', 'single');
        $oldGuests = (int) old('number_of_guests', $oldOccupancy === 'double' ? 2 : 1);
        $oldRooms = max(1, (int) old('number_of_rooms', 1));
        $oldCheckIn = old('check_in_date');
        $oldCheckOut = old('check_out_date');
        $initialNights = 1;
        try {
            if ($oldCheckIn && $oldCheckOut) {
                $ci = \Carbon\Carbon::parse($oldCheckIn);
                $co = \Carbon\Carbon::parse($oldCheckOut);
                if ($co->greaterThan($ci)) {
                    $initialNights = max(1, $ci->diffInDays($co));
                }
            }
        } catch (\Throwable $e) {
            $initialNights = 1;
        }
        $initialRateInr = $oldOccupancy === 'double'
            ? (float) ($selectedRoom['double_rate_inr'] ?? 0)
            : (float) ($selectedRoom['single_rate_inr'] ?? 0);
        $initialRateUsd = $oldOccupancy === 'double'
            ? (float) ($selectedRoom['double_rate_usd'] ?? 0)
            : (float) ($selectedRoom['single_rate_usd'] ?? 0);
        $initialSubtotalInr = $initialRateInr * $oldRooms * $initialNights;
        $initialSubtotalUsd = $initialRateUsd * $oldRooms * $initialNights;
        $initialTotalInr = $initialSubtotalInr;
        $initialTotalUsd = $initialSubtotalUsd;
        $bookingWindowText = '-';
        if ($bookingFrom && $bookingTo) {
            $bookingWindowText = \Carbon\Carbon::parse($bookingFrom)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($bookingTo)->format('d M Y');
        } elseif ($bookingFrom) {
            $bookingWindowText = 'From ' . \Carbon\Carbon::parse($bookingFrom)->format('d M Y');
        } elseif ($bookingTo) {
            $bookingWindowText = 'Until ' . \Carbon\Carbon::parse($bookingTo)->format('d M Y');
        }
    @endphp
    <section class="pt-120 pb-80 venue-reserve-world-section">
        <div class="venue-reserve-world-map" aria-hidden="true"></div>
        <div class="container">
            @if(session('message'))
                <div class="alert alert-success mb-4">{{ session('message') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="reserve-hero">
                <div class="reserve-hero-main">
                    <div class="reserve-hero-topbar">
                        <div class="reserve-hero-copy">
                            @include('frontend.partials.inner-page-hero', [
                                'heroLabel' => $settings['reservation_eyebrow_text'] ?? 'VENUE RESERVATION',
                                'heroTitle' => $settings['reservation_heading'] ?? 'Reserve Your Stay',
                                'heroSuffix' => $settings['reservation_heading_suffix'] ?? '',
                                'heroDescription' => $settings['reservation_description'] ?? 'Please fill all the details carefully so the hotel reservation team can review your request and contact you directly with the next steps.',
                                'heroButtons' => [],
                            ])
                        </div>
                        <a href="{{ route('frontend.venue.show', $hotel) }}" class="reserve-back-btn">
                            <span>Back to Rooms</span>
                        </a>
                    </div>
                    <div class="reserve-hero-meta">
                        <div class="reserve-hero-meta-item">
                            <span class="label">Hotel</span>
                            <strong>{{ $hotel->name }}</strong>
                        </div>
                        <div class="reserve-hero-meta-item">
                            <span class="label">Room Type</span>
                            <strong>{{ $selectedRoom['name'] }}</strong>
                        </div>
                        <div class="reserve-hero-meta-item">
                            <span class="label">Starting Rate</span>
                            <strong id="reserve-starting-rate">INR {{ number_format($summaryRateInr, 2) }} / night</strong>
                        </div>
                    </div>
                    <div class="reserve-currency-toolbar">
                        <div class="ticket-currency-switch" role="tablist" aria-label="Select room rate currency">
                            <button type="button" class="ticket-currency-btn" data-venue-currency-toggle="INR" role="tab" aria-selected="false">INR</button>
                            <button type="button" class="ticket-currency-btn" data-venue-currency-toggle="USD" role="tab" aria-selected="false">USD</button>
                        </div>
                        <div class="reserve-currency-note">Rates shown in USD and are approximate; slight variations may apply..</div>
                    </div>
                </div>
                <div class="reserve-hero-aside">
                    <div class="reserve-room-preview">
                        <img src="{{ $selectedRoom['image'] }}" alt="{{ $selectedRoom['name'] }}" class="reserve-room-preview-image">
                        <div class="reserve-room-preview-copy">
                            <span class="reserve-room-preview-label">Selected Room Type</span>
                            <h3 class="reserve-room-preview-title">{{ $selectedRoom['name'] }}</h3>
                            <p class="reserve-room-preview-text">{{ $hotel->name }}</p>
                            @if($selectedRoom['caption'] !== '')
                                <p class="reserve-room-preview-text">{{ $selectedRoom['caption'] }}</p>
                            @endif
                            @if($selectedRoom['size'] !== '')
                                <p class="reserve-room-preview-text">{{ $selectedRoom['size'] }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="reserve-info-chips">
                        <span class="reserve-info-chip">{{ $taxSummaryNoteText }}</span>
                        <div class="reserve-room-preview-text">Discounted rates apply only on these dates.</div>
                        <span class="reserve-info-chip">{{ $bookingWindowText }}</span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="reserve-card">
                        <h3 class="reserve-section-title">Reservation Form</h3>
                        <p class="reserve-section-subtitle">Enter guest details, stay dates, and any special requests for your selected room type.</p>
                        <form method="POST" action="{{ route('frontend.venue.reserve.store', $hotel) }}">
                            @csrf
                            <input type="hidden" name="selected_room_index" value="{{ $selectedRoomIndex }}">
                            <div class="row">
                                <div class="col-md-6 reserve-form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required>
                                </div>
                                <div class="col-md-6 reserve-form-group">
                                    <label class="form-label">E-mail ID</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                </div>
                                <div class="col-md-6 reserve-form-group">
                                    <label class="form-label">Mobile No</label>
                                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
                                </div>
                                <div class="col-md-6 reserve-form-group">
                                    <label class="form-label">Country</label>
                                    <select class="form-control" name="country" id="reserve-country" required>
                                        <option value="">Select country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country }}" @selected(old('country', $hotel->country ?? '') === $country)>{{ $country }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 reserve-form-group">
                                    <label class="form-label">Room Category</label>
                                    <div class="reserve-readonly-value">{{ $selectedRoom['name'] }}</div>
                                    <span class="reserve-form-note">Auto selected from previous page.</span>
                                </div>
                                <div class="col-md-6 reserve-form-group">
                                    <label class="form-label">Rooms Required</label>
                                    <input type="number" class="form-control" name="number_of_rooms" value="{{ old('number_of_rooms', 1) }}" min="1" max="20" required>
                                    <span class="reserve-form-note">Choose between 1 and 20 rooms.</span>
                                </div>
                                <div class="col-md-3 reserve-form-group">
                                    <label class="form-label">Check-in Date</label>
                                    <input type="date" class="form-control" name="check_in_date" value="{{ old('check_in_date') }}" @if($bookingFrom) min="{{ $bookingFrom }}" @endif @if($bookingTo) max="{{ $bookingTo }}" @endif required>
                                    <span class="reserve-form-note">Calendar works only within the allowed booking period.</span>
                                </div>
                                <div class="col-md-3 reserve-form-group">
                                    <label class="form-label">Check-out Date</label>
                                    <input type="date" class="form-control" name="check_out_date" value="{{ old('check_out_date') }}" @if($bookingFrom) min="{{ $bookingFrom }}" @endif @if($bookingTo) max="{{ $bookingTo }}" @endif required>
                                    <span class="reserve-form-note">Calendar works only within the allowed booking period.</span>
                                </div>
                                <div class="col-md-4 reserve-form-group">
                                    <label class="form-label">Occupancy</label>
                                    <select class="form-control" name="occupancy" id="reserve-occupancy" required>
                                        <option value="single" @selected($oldOccupancy === 'single')>Single Occupancy</option>
                                        <option value="double" @selected($oldOccupancy === 'double')>Double Occupancy</option>
                                    </select>
                                    <input type="hidden" name="number_of_guests" id="reserve-number-of-guests" value="{{ $oldGuests }}">
                                    <span class="reserve-form-note">Auto sets guest count for single or double occupancy.</span>
                                </div>
                                <div class="col-md-4 reserve-form-group">
                                    <label class="form-label">Bed Size</label>
                                    <select class="form-control" name="bed_size">
                                        <option value="">Select bed size</option>
                                        <option value="King Size Bed" @selected(old('bed_size') === 'King Size Bed')>King Size Bed</option>
                                        <option value="Twin Bed" @selected(old('bed_size') === 'Twin Bed')>Twin Bed</option>
                                    </select>
                                    <span class="reserve-form-note">Choose your preferred bed setup.</span>
                                </div>
                                <div class="col-md-4 reserve-form-group">
                                    <label class="form-label">Preference</label>
                                    <select class="form-control" name="preference">
                                        <option value="">Select room preference</option>
                                        <option value="Smoking Room" @selected(old('preference') === 'Smoking Room')>Smoking Room</option>
                                        <option value="Non-Smoking Room" @selected(old('preference') === 'Non-Smoking Room')>Non-Smoking Room</option>
                                    </select>
                                    <span class="reserve-form-note">Choose your room preference.</span>
                                </div>
                                <div class="col-md-12 reserve-form-group">
                                    <label class="form-label">Special Requests</label>
                                    <textarea class="form-control" name="special_requests" rows="3">{{ old('special_requests') }}</textarea>
                                    <div class="reserve-special-note">
                                        <strong>Note:</strong> Special requests cannot be guaranteed, but the property team will do its best to meet your needs.
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn-main fx-slide"><span>Submit Reservation</span></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="reserve-card">
                        <h4 class="summary-title">Selected Package</h4>
                        <div class="summary-row"><span>Hotel Name</span><strong>{{ $hotel->name }}</strong></div>
                        <div class="summary-row"><span>Room Category</span><strong>{{ $selectedRoom['name'] }}</strong></div>
                        <div class="summary-row {{ $oldOccupancy === 'double' ? 'd-none' : '' }}" id="summary-single-row"><span id="summary-single-label">Single Occupancy Rate (INR)</span><strong id="summary-single-rate">INR {{ number_format((float) $selectedRoom['single_rate_inr'], 2) }}</strong></div>
                        <div class="summary-row {{ $oldOccupancy === 'double' ? '' : 'd-none' }}" id="summary-double-row"><span id="summary-double-label">Double Occupancy Rate (INR)</span><strong id="summary-double-rate">INR {{ number_format((float) $selectedRoom['double_rate_inr'], 2) }}</strong></div>
                        <div class="summary-row"><span>Selected Occupancy</span><strong id="summary-occupancy">{{ $oldOccupancy === 'double' ? 'Double' : 'Single' }}</strong></div>
                        <div class="summary-row"><span>Number of Nights</span><strong id="summary-nights">{{ $initialNights }}</strong></div>
                        <div class="summary-row"><span>Rooms Required</span><strong id="summary-rooms">{{ $oldRooms }}</strong></div>
                        <div class="summary-row"><span id="summary-subtotal-label">Subtotal (INR)</span><strong id="summary-subtotal">INR {{ number_format($initialSubtotalInr, 2) }}</strong></div>
                        <div class="summary-total">
                            <span class="summary-total-label" id="summary-total-label">Grand Total (INR)</span>
                            <div class="summary-total-amount" id="summary-total-amount">INR {{ number_format($initialTotalInr, 2) }}</div>
                            <div class="summary-total-note" id="summary-total-note">{{ $taxSummaryNoteText }}</div>
                        </div>
                        <div class="pt-2">
                            <strong>What is available</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($selectedRoom['benefits'] as $benefit)
                                    <li>{{ $benefit }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@include('frontend.partials.footer')
<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script>
    (function () {
        var currencyButtons = Array.prototype.slice.call(document.querySelectorAll('[data-venue-currency-toggle]'));
        var occupancy = document.getElementById('reserve-occupancy');
        var guests = document.getElementById('reserve-number-of-guests');
        var roomsInput = document.querySelector('input[name="number_of_rooms"]');
        var checkInInput = document.querySelector('input[name="check_in_date"]');
        var checkOutInput = document.querySelector('input[name="check_out_date"]');
        var startingRate = document.getElementById('reserve-starting-rate');
        var summarySingleRow = document.getElementById('summary-single-row');
        var summarySingleLabel = document.getElementById('summary-single-label');
        var summarySingleRate = document.getElementById('summary-single-rate');
        var summaryDoubleRow = document.getElementById('summary-double-row');
        var summaryDoubleLabel = document.getElementById('summary-double-label');
        var summaryDoubleRate = document.getElementById('summary-double-rate');
        var summaryOccupancy = document.getElementById('summary-occupancy');
        var summaryNights = document.getElementById('summary-nights');
        var summaryRooms = document.getElementById('summary-rooms');
        var summarySubtotalLabel = document.getElementById('summary-subtotal-label');
        var summarySubtotal = document.getElementById('summary-subtotal');
        var summaryTotalLabel = document.getElementById('summary-total-label');
        var summaryTotalAmount = document.getElementById('summary-total-amount');
        var summaryTotalNote = document.getElementById('summary-total-note');
        var summaryNoteText = @json($taxSummaryNoteText);
        var rates = {
            singleInr: {{ json_encode((float) $selectedRoom['single_rate_inr']) }},
            doubleInr: {{ json_encode((float) $selectedRoom['double_rate_inr']) }},
            singleUsd: {{ json_encode((float) $selectedRoom['single_rate_usd']) }},
            doubleUsd: {{ json_encode((float) $selectedRoom['double_rate_usd']) }}
        };
        var currentCurrency = 'INR';

        if (!occupancy || !guests) {
            return;
        }

        function formatAmount(amount, currency) {
            var locale = currency === 'USD' ? 'en-US' : 'en-IN';
            return currency + ' ' + Number(amount || 0).toLocaleString(locale, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function setCurrency(currency) {
            currentCurrency = currency === 'USD' ? 'USD' : 'INR';
            currencyButtons.forEach(function (button) {
                var isActive = button.getAttribute('data-venue-currency-toggle') === currentCurrency;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
            if (summarySingleLabel) {
                summarySingleLabel.textContent = 'Single Occupancy Rate (' + currentCurrency + ')';
            }
            if (summaryDoubleLabel) {
                summaryDoubleLabel.textContent = 'Double Occupancy Rate (' + currentCurrency + ')';
            }
            if (summarySubtotalLabel) {
                summarySubtotalLabel.textContent = 'Subtotal (' + currentCurrency + ')';
            }
            if (summaryTotalLabel) {
                summaryTotalLabel.textContent = 'Grand Total (' + currentCurrency + ')';
            }
            if (startingRate) {
                startingRate.textContent = formatAmount(currentCurrency === 'USD' ? rates.singleUsd : rates.singleInr, currentCurrency) + ' / night';
            }
            if (summarySingleRate) {
                summarySingleRate.textContent = formatAmount(currentCurrency === 'USD' ? rates.singleUsd : rates.singleInr, currentCurrency);
            }
            if (summaryDoubleRate) {
                summaryDoubleRate.textContent = formatAmount(currentCurrency === 'USD' ? rates.doubleUsd : rates.doubleInr, currentCurrency);
            }
            sessionStorage.setItem('venue_room_currency_{{ $hotel->id }}', currentCurrency);
            syncSummary();
        }

        function parseDate(value) {
            var parts = String(value || '').split('-');
            if (parts.length !== 3) {
                return null;
            }
            var year = Number(parts[0]);
            var month = Number(parts[1]) - 1;
            var day = Number(parts[2]);
            var date = new Date(year, month, day);
            return Number.isNaN(date.getTime()) ? null : date;
        }

        function getNights() {
            if (!checkInInput || !checkOutInput) {
                return 1;
            }
            var checkIn = parseDate(checkInInput.value);
            var checkOut = parseDate(checkOutInput.value);
            if (!checkIn || !checkOut) {
                return 1;
            }
            var diffInMs = checkOut.getTime() - checkIn.getTime();
            var diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
            return diffInDays > 0 ? diffInDays : 1;
        }

        function parsePositiveInt(value, fallback) {
            var parsed = Number.parseInt(value, 10);
            if (Number.isNaN(parsed) || parsed < 1) {
                return fallback;
            }
            return parsed;
        }

        function syncSummary() {
            var isDouble = occupancy.value === 'double';
            var selectedRate = currentCurrency === 'USD'
                ? (isDouble ? rates.doubleUsd : rates.singleUsd)
                : (isDouble ? rates.doubleInr : rates.singleInr);
            var nights = getNights();
            var rooms = parsePositiveInt(roomsInput ? roomsInput.value : '1', 1);
            var subtotal = selectedRate * nights * rooms;
            var total = subtotal;

            if (summarySingleRow) {
                summarySingleRow.classList.toggle('d-none', isDouble);
            }
            if (summaryDoubleRow) {
                summaryDoubleRow.classList.toggle('d-none', !isDouble);
            }
            summaryOccupancy.textContent = isDouble ? 'Double' : 'Single';
            if (summaryNights) {
                summaryNights.textContent = String(nights);
            }
            if (summaryRooms) {
                summaryRooms.textContent = String(rooms);
            }
            if (summarySubtotal) {
                summarySubtotal.textContent = formatAmount(subtotal, currentCurrency);
            }
            summaryTotalAmount.textContent = formatAmount(total, currentCurrency);
            summaryTotalNote.textContent = summaryNoteText;
        }

        function syncGuests() {
            guests.value = occupancy.value === 'double' ? '2' : '1';
            syncSummary();
        }

        occupancy.addEventListener('change', syncGuests);
        if (roomsInput) {
            roomsInput.addEventListener('input', syncSummary);
            roomsInput.addEventListener('change', syncSummary);
        }
        if (checkInInput) {
            checkInInput.addEventListener('change', syncSummary);
            checkInInput.addEventListener('input', syncSummary);
        }
        if (checkOutInput) {
            checkOutInput.addEventListener('change', syncSummary);
            checkOutInput.addEventListener('input', syncSummary);
        }
        currencyButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setCurrency(button.getAttribute('data-venue-currency-toggle') || 'INR');
            });
        });

        var initialCurrency = new URLSearchParams(window.location.search).get('currency')
            || sessionStorage.getItem('venue_room_currency_{{ $hotel->id }}')
            || 'INR';
        setCurrency(initialCurrency);
        syncGuests();
    })();
</script>
</body>
</html>
