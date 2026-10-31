@extends('layouts.booking')
@section('title', 'Confirm cancellation')
@section('booking-content')
<div class="page-heading"><div><span class="eyebrow">A change of plans</span><h1>Cancel this booking?</h1><p class="booking-reference">{{ $booking->reference }}</p></div></div>
<section class="panel railway-form"><h2>{{ $booking->trip->train->number }} · {{ $booking->trip->train->name }}</h2>
<p>This cancels <strong>every passenger</strong> in this booking. Active seat assignments and waiting requests are released together. Eligible waitlisted groups may be promoted automatically. Your cancellation history remains available.</p>
@if($booking->status === 'cancelled')<p>This booking is already cancelled.</p>
@else<form method="POST" action="{{ route('bookings.cancel', $booking) }}">@csrf<button class="button" type="submit">Confirm whole-booking cancellation</button></form>@endif
<a class="back-link" href="{{ route('bookings.show', $booking) }}">← Return to booking details</a>
</section>
@endsection
