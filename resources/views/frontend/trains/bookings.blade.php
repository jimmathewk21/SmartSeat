<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Train Bookings | SmartSeat</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/train-search.css') }}" rel="stylesheet">
    <style>.train-bookings-wrap{max-width:1000px;margin:0 auto;padding:28px 16px 48px}.booking-card{border:1px solid #e5e9f2;border-radius:12px;background:#fff;padding:20px;margin:16px 0}.seat-badge{display:inline-block;background:#f0f3f8;border-radius:6px;padding:5px 9px;margin:3px}@media(max-width:575px){.train-bookings-wrap{padding:20px 14px 36px}.booking-card{padding:15px}}</style>
</head>
<body style="background:#f6f8fc;color:#172033">
<div id="wrapper">
    @include('frontend.trains.partials.header')
    <main class="train-bookings-wrap">
        <h1 class="mb-3">My train bookings</h1>
        @if(session('message')) <div class="alert alert-success">{{ session('message') }}</div> @endif
        @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
        @if($confirmedBooking)
            @php
                $confirmationTrip = $confirmedBooking->trip;
                $confirmationStops = $confirmationTrip->train->routeStops;
                $confirmationHasJourney = $confirmedBooking->boarding_station_id && $confirmedBooking->destination_station_id;
                $confirmationOrigin = $confirmationHasJourney ? $confirmedBooking->boardingStation?->name : $confirmationStops->first()?->station?->name;
                $confirmationDestination = $confirmationHasJourney ? $confirmedBooking->destinationStation?->name : $confirmationStops->last()?->station?->name;
                $confirmationDeparture = $confirmedBooking->boarding_at ?? $confirmationTrip->starts_at;
                $confirmationArrival = $confirmedBooking->arrival_at ?? $confirmationTrip->ends_at;
            @endphp
            <section class="train-booking-confirmation" aria-labelledby="train-booking-confirmation-title" role="status">
                <div class="train-confirmation-heading">
                    <div>
                        <p class="train-confirmation-kicker">Booking confirmed</p>
                        <h2 id="train-booking-confirmation-title">Your seats are reserved</h2>
                    </div>
                    <span class="train-record-status is-confirmed">{{ ucfirst($confirmedBooking->status) }}</span>
                </div>
                <p class="train-confirmation-reference">Booking reference <strong>{{ $confirmedBooking->booking_code }}</strong></p>
                <div class="train-confirmation-journey">
                    <div>
                        <strong>{{ $confirmationTrip->train->name }} ({{ $confirmationTrip->train->code }})</strong>
                        <p>{{ $confirmationHasJourney ? 'Your journey' : 'Legacy full-trip schedule (selected stations unavailable)' }}: {{ $confirmationOrigin ?? 'Route details unavailable' }} <span aria-hidden="true">→</span> {{ $confirmationDestination ?? 'Route details unavailable' }}</p>
                    </div>
                    <div class="train-confirmation-times">
                        <span>Departs <strong>{{ $confirmationDeparture->format('D, j M Y · g:i A') }}</strong></span>
                        <span>Arrives <strong>{{ $confirmationArrival->format('D, j M Y · g:i A') }}</strong></span>
                    </div>
                </div>
                <div class="train-confirmation-details">
                    <div>
                        <h3>Passengers</h3>
                        <ul>
                            @foreach($confirmedBooking->passengers as $passenger)
                                <li>{{ $passenger->name }}@if($passenger->requires_accessible_seat) <span>Accessible seat requested</span>@endif</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h3>Assigned seats</h3>
                        <ul>
                            @foreach($confirmedBooking->seatAssignments as $assignment)
                                <li>
                                    {{ $assignment->passenger?->name ?? 'Passenger' }}:
                                    {{ $assignment->inventory->seat->coach->code }}-{{ $assignment->inventory->seat->number }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <a class="train-confirmation-link" href="#booking-{{ \Illuminate\Support\Str::slug($confirmedBooking->booking_code) }}">View in booking history</a>
            </section>
        @endif
        @forelse($bookings as $booking)
            <article class="booking-card" id="booking-{{ \Illuminate\Support\Str::slug($booking->booking_code) }}">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <h2 class="h5">{{ $booking->trip->train->name }} ({{ $booking->trip->train->code }})</h2>
                        <div>Booking code: <strong>{{ $booking->booking_code }}</strong></div>
                        @if($booking->boarding_station_id && $booking->destination_station_id)
                            <div>Journey: {{ $booking->boardingStation?->name ?? 'Station unavailable' }} → {{ $booking->destinationStation?->name ?? 'Station unavailable' }}</div>
                            <div>Travel: {{ ($booking->boarding_at ?? $booking->trip->starts_at)->format('d-M-Y h:i A') }} – {{ ($booking->arrival_at ?? $booking->trip->ends_at)->format('d-M-Y h:i A') }}</div>
                        @else
                            <div>Legacy full-trip schedule (selected stations unavailable): {{ $booking->trip->starts_at->format('d-M-Y h:i A') }} – {{ $booking->trip->ends_at->format('d-M-Y h:i A') }}</div>
                        @endif
                        <div>Status: <span class="train-record-status {{ $booking->status === \App\Models\TrainBooking::STATUS_CONFIRMED ? 'is-confirmed' : 'is-cancelled' }}">{{ ucfirst($booking->status) }}</span></div>
                        <div>Passengers: {{ $booking->passengers->pluck('name')->join(', ') }}</div>
                    </div>
                    <div>
                        @foreach($booking->seatAssignments as $assignment)
                            <span class="seat-badge">{{ $assignment->inventory->seat->coach->code }}-{{ $assignment->inventory->seat->number }}</span>
                        @endforeach
                    </div>
                </div>
                @if($booking->status === \App\Models\TrainBooking::STATUS_CONFIRMED && ($booking->boarding_at ?? $booking->trip->starts_at)->isFuture())
                    <form class="mt-3" method="POST" action="{{ route('frontend.trains.cancel', $booking) }}" onsubmit="return confirm('Cancel this train booking?');">
                        @csrf
                        <button class="btn btn-outline-danger" type="submit">Cancel booking</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="booking-card">No train bookings yet. <a href="{{ route('frontend.trains.search') }}">Search trains</a>.</div>
        @endforelse
        {{ $bookings->links() }}

        <h2 class="h4 mt-5">My waitlist requests</h2>
        @forelse($waitlistEntries as $entry)
            <article class="booking-card">
                <div><strong>{{ $entry->trip->train->name }} ({{ $entry->trip->train->code }})</strong></div>
                @if($entry->boarding_station_id && $entry->destination_station_id)
                    <div>Journey: {{ $entry->boardingStation?->name ?? 'Station unavailable' }} → {{ $entry->destinationStation?->name ?? 'Station unavailable' }}</div>
                    <div>Travel: {{ ($entry->boarding_at ?? $entry->trip->starts_at)->format('d-M-Y h:i A') }} – {{ ($entry->arrival_at ?? $entry->trip->ends_at)->format('d-M-Y h:i A') }}</div>
                @else
                    <div>Legacy full-trip waitlist request (selected stations unavailable).</div>
                @endif
                <div>Passengers: {{ $entry->passengers->pluck('name')->join(', ') }}</div>
                <div>Status: <span class="train-record-status {{ $entry->status === \App\Models\TrainWaitlistEntry::STATUS_WAITING ? 'is-waiting' : ($entry->status === \App\Models\TrainWaitlistEntry::STATUS_PROMOTED ? 'is-promoted' : 'is-cancelled') }}">{{ ucfirst($entry->status) }}</span></div>
                @if($entry->status === \App\Models\TrainWaitlistEntry::STATUS_WAITING)
                    <p class="train-waitlist-note">Waiting for an eligible seat offer. This is not a confirmed booking.</p>
                @endif
                @if($entry->promoted_booking_id)
                    <div>Promoted booking reference: <strong>{{ $entry->promotedBooking?->booking_code ?? 'See your bookings above' }}</strong></div>
                @endif
                @if($entry->status === \App\Models\TrainWaitlistEntry::STATUS_WAITING)
                    <form class="mt-2" method="POST" action="{{ route('frontend.trains.waitlist.cancel', $entry) }}">
                        @csrf
                        <button class="btn btn-outline-danger" type="submit">Cancel waitlist request</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="booking-card">No waitlist requests.</div>
        @endforelse
        {{ $waitlistEntries->links() }}
    </main>
</div>
@include('frontend.trains.partials.footer')
</body>
</html>
