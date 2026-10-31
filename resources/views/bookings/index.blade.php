@extends('layouts.booking')
@section('title', 'Your bookings')
@section('booking-content')
<div class="page-heading"><div><span class="eyebrow">Your journeys, all together</span><h1>Booking history.</h1><p class="muted">Reservations, waitlist requests and cancellations for your account.</p></div><a class="button button-small" href="{{ route('trains.search') }}">Find trains →</a></div>
<section class="panel">
@if($bookings->isEmpty())<div class="empty-state"><h2>No bookings yet</h2><p>Search for a journey to reserve seats or join a waitlist.</p></div>
@else<div class="table-scroll"><table><thead><tr><th>Details</th><th>Train</th><th>Origin date</th><th>Passengers</th><th>Status</th></tr></thead><tbody>
@foreach($bookings as $booking)<tr><td><a href="{{ route('bookings.show', $booking) }}">View booking</a></td><td>{{ $booking->trip->train->number }} · {{ $booking->trip->train->name }}</td><td>{{ $booking->trip->service_date->format('d-M-Y') }}</td><td>{{ $booking->passengers_count }}</td><td>{{ ucfirst($booking->status) }}</td></tr>@endforeach
</tbody></table></div>{{ $bookings->links('components.pagination') }}@endif
</section>
@endsection
