@extends('layouts.booking')
@section('title', 'Booking details')
@section('booking-content')
@if(session('booking_confirmed'))<div class="admin-notice" role="status">{{ $booking->status === 'confirmed' ? 'Your reservation is confirmed.' : ($booking->status === 'waitlisted' ? 'Your group is on the waitlist. Seats are not confirmed yet.' : 'This booking was already processed.') }}</div>@endif
<div class="page-heading"><div><span class="eyebrow">{{ ucfirst($booking->status) }} · {{ $booking->travel_class }}</span><h1>Your journey.</h1><p class="booking-reference">Reference: {{ $booking->reference }}</p></div><a class="text-link" href="{{ route('bookings.index') }}">Your booking history →</a></div>
@include('bookings.partials.journey', ['trip' => $booking->trip])
@if($booking->status === 'waitlisted')
<section class="panel railway-form railway-list"><h2>Waitlist confirmation estimate</h2>
@if($prediction['available'])<p class="prediction-value">{{ $prediction['probability'] < 0.01 ? '<1' : ($prediction['probability'] > 0.99 ? '>99' : number_format($prediction['probability'] * 100, 1)) }}% estimated chance</p><p>{{ $prediction['message'] }}</p><small class="muted">Join state recorded {{ $prediction['observed_at']->timezone(config('smartseat.railway_timezone'))->format('d-M-Y H:i A') }} · Model {{ $prediction['version'] }}</small>
@else<p>{{ $prediction['message'] }}</p>@endif
</section>@endif
@if($booking->status === 'waitlisted')<div class="admin-notice railway-list">Your group will be promoted together when enough seats become available. Queue positions below are original request numbers, not a guarantee of confirmation.</div>@endif
<section class="panel railway-list"><div class="panel-heading"><h2>Passengers</h2><span>Whole-group booking</span></div>
<div class="table-scroll"><table><thead><tr><th>Name</th><th>Age</th><th>Gender</th><th>Status</th><th>Seat / waitlist</th></tr></thead><tbody>
@foreach($booking->passengers as $person)
@php($active = $person->assignments->firstWhere('status', 'active'))
@php($waiting = $person->waitlistEntries->firstWhere('status', 'waiting'))
<tr><td>{{ $person->name }}</td><td>{{ $person->age }}</td><td>{{ str_replace('_', ' ', $person->gender) }}</td><td>{{ ucfirst($person->status) }}</td><td>
@if($active){{ $active->inventory->seat->coach->label }} / {{ $active->inventory->seat->number }} · {{ str_replace('_', ' ', $active->inventory->seat->berth_type) }}
@elseif($waiting)Waitlist request {{ $waiting->position }}
@else{{ $person->status === 'cancelled' ? 'Cancelled' : 'No active assignment' }}@endif</td></tr>
@endforeach
</tbody></table></div></section>
<section class="panel railway-list"><div class="panel-heading"><h2>Booking activity</h2></div>
<div class="table-scroll"><table><thead><tr><th>Time</th><th>Action</th><th>Status</th></tr></thead><tbody>
@forelse($events as $event)<tr><td>{{ $event->created_at->timezone(config('smartseat.railway_timezone'))->format('d-M-Y H:i A') }}</td><td>{{ ucfirst($event->action) }}</td><td>{{ $event->from_status ?? 'New' }} → {{ $event->to_status }}</td></tr>@empty<tr><td colspan="3">No activity recorded.</td></tr>@endforelse
</tbody></table></div>{{ $events->links('components.pagination') }}</section>
@if($booking->status !== 'cancelled')<p class="railway-list"><a class="button button-outline" href="{{ route('bookings.cancellation', $booking) }}">Cancel this {{ $booking->status === 'waitlisted' ? 'waitlist request' : 'booking' }}</a></p>
@else<p class="muted railway-list">Cancelled {{ $booking->cancelled_at?->timezone(config('smartseat.railway_timezone'))->format('d-M-Y H:i A') }}. The history is preserved.</p>@endif
@endsection
