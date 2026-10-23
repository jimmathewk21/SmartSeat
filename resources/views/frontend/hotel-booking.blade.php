<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'Hotel Booking';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $fromDate = trim((string) ($hotelSettings['available_from_date'] ?? ''));
        $toDate = trim((string) ($hotelSettings['available_to_date'] ?? ''));
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
        body.hotel-booking-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.hotel-booking-page > footer { margin-top: 0 !important; }
        body.hotel-booking-page { background: #f5f6f8; color: #111; }
        .hotel-card {
            background: #fff;
            border: 1px solid #e4e7ef;
            border-radius: 12px;
            padding: 24px;
        }
        .hotel-meta {
            color: #5a6577;
            font-size: 15px;
            line-height: 1.65;
        }
        .hotel-meta strong {
            color: #111;
        }
    </style>
</head>
<body class="hotel-booking-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80">
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
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="hotel-card h-100">
                        <div class="subtitle">{{ $hotelSettings['hotel_name'] !== '' ? $hotelSettings['hotel_name'] : 'Hotel Booking' }}</div>
                        <h2 style="color:#000;" class="mb-2">Book Your Stay</h2>
                        <p class="hotel-meta mb-2">
                            <strong>No payment required.</strong> Submit your request and our team will contact you.
                        </p>
                        @if(trim((string) ($hotelSettings['hotel_address'] ?? '')) !== '')
                            <p class="hotel-meta mb-2"><strong>Address:</strong> {{ $hotelSettings['hotel_address'] }}</p>
                        @endif
                        @if($fromDate !== '' || $toDate !== '')
                            <p class="hotel-meta mb-2">
                                <strong>Available Dates:</strong>
                                {{ $fromDate !== '' ? \Carbon\Carbon::parse($fromDate)->format('d-M-Y') : 'N/A' }}
                                -
                                {{ $toDate !== '' ? \Carbon\Carbon::parse($toDate)->format('d-M-Y') : 'N/A' }}
                            </p>
                        @endif
                        @if($directionUrl !== '')
                            <div class="mt-3">
                                <a href="{{ $directionUrl }}" target="_blank" rel="noopener noreferrer" class="btn-main fx-slide">
                                    <span>Get Direction</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="hotel-card">
                        <h4 style="color:#000;" class="mb-3">Booking Form</h4>
                        <form method="POST" action="{{ route('frontend.hotel-booking.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Check-in Date</label>
                                    <input type="date" class="form-control" name="check_in_date" value="{{ old('check_in_date') }}" @if($fromDate !== '') min="{{ $fromDate }}" @endif @if($toDate !== '') max="{{ $toDate }}" @endif required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Check-out Date</label>
                                    <input type="date" class="form-control" name="check_out_date" value="{{ old('check_out_date') }}" @if($fromDate !== '') min="{{ $fromDate }}" @endif @if($toDate !== '') max="{{ $toDate }}" @endif required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Guests</label>
                                    <input type="number" class="form-control" name="number_of_guests" value="{{ old('number_of_guests', 1) }}" min="1" max="20" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Rooms</label>
                                    <input type="number" class="form-control" name="number_of_rooms" value="{{ old('number_of_rooms', 1) }}" min="1" max="20" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Special Requests</label>
                                    <textarea class="form-control" name="special_requests" rows="3">{{ old('special_requests') }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn-main fx-slide">
                                <span>Submit Booking</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@include('frontend.partials.footer')
<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
</body>
</html>
