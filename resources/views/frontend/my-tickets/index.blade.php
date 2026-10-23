<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'My Tickets';
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
        body.my-tickets-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.my-tickets-page > footer { margin-top: 0 !important; }
        body.my-tickets-page { background:#fff; color:#111; }
        .booking-card { border:1px solid #e6e9ef; border-radius:10px; padding:18px; margin-bottom:16px; }
        .ticket-code-badge { display:inline-block; margin:2px 4px 2px 0; padding:4px 8px; border-radius:4px; background:#f3f6fc; font-size:12px; }
    </style>
</head>
<body class="my-tickets-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80">
        <div class="container">
            <h2 class="mb-4" style="color:#000;">My Tickets</h2>

            @forelse($bookingsByEvent as $eventBookings)
                @php
                    $eventTitle = optional($eventBookings->first()->event)->title ?: 'Event';
                @endphp
                <h4 class="mb-3" style="color:#000;">{{ $eventTitle }}</h4>
                @foreach($eventBookings as $booking)
                    <div class="booking-card">
                        <div class="row g-3">
                            <div class="col-lg-9">
                                <div class="text-muted mb-2">Booking Code: {{ $booking->booking_code }}</div>
                                <div><strong>Plan:</strong> {{ $booking->plan?->name }}</div>
                                <div><strong>Quantity:</strong> {{ $booking->quantity }}</div>
                                <div><strong>Paid (INR):</strong> ₹{{ number_format($booking->total_amount_paise / 100, 2) }}</div>
                                <div>
                                    <strong>Displayed to user:</strong>
                                    @if($booking->display_currency === 'USD')
                                        ${{ number_format(($booking->display_total_amount_minor ?? 0) / 100, 2) }}
                                    @else
                                        ₹{{ number_format(($booking->display_total_amount_minor ?? $booking->total_amount_paise) / 100, 2) }}
                                    @endif
                                    ({{ $booking->display_currency ?: 'INR' }})
                                </div>
                                <div><strong>Booking Date:</strong> {{ optional($booking->created_at)->format('d-M-Y h:i A') }}</div>
                                <div><strong>Payment Status:</strong> {{ ucfirst($booking->status) }}</div>
                            </div>
                            <div class="col-lg-3">
                                <div><strong>Ticket Codes</strong></div>
                                @forelse($booking->tickets as $ticket)
                                    <span class="ticket-code-badge">{{ $ticket->ticket_code }}</span>
                                @empty
                                    <div class="text-muted">No ticket codes yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="alert alert-light border">No bookings found yet.</div>
            @endforelse

            {{ $bookings->links() }}
        </div>
    </section>
</div>

@include('frontend.partials.footer')
<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
</body>
</html>
