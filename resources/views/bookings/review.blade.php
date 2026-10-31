@extends('layouts.booking')
@section('title', 'Review your journey')
@section('booking-content')
<div class="page-heading"><div><span class="eyebrow">03 · One last look</span><h1>{{ $review['mode'] === 'waitlist' ? 'Review your waitlist request.' : 'Your journey, ready to confirm.' }}</h1><p class="muted">Check every passenger and station before continuing.</p></div></div>
@include('bookings.partials.journey')
<section class="panel railway-list"><div class="panel-heading"><h2>Travelling together · {{ $review['details']['travel_class'] }}</h2></div>
<div class="table-scroll"><table><thead><tr><th>Passenger</th><th>Age</th><th>Gender</th><th>Seat / request</th></tr></thead><tbody>
@foreach($review['details']['passengers'] as $i => $person)<tr><td>{{ $person['name'] }}</td><td>{{ $person['age'] }}</td><td>{{ str_replace('_', ' ', $person['gender']) }}</td><td>@if($review['mode'] === 'reserve'){{ $selected->get($review['seats'][$i])?->seat->coach->label }} / {{ $selected->get($review['seats'][$i])?->seat->number }}@elseWaitlist · {{ $review['details']['travel_class'] }}@endif</td></tr>@endforeach
</tbody></table></div>
<div class="railway-form"><p class="muted">Your whole group is confirmed or waitlisted together. Availability is checked again at confirmation. No payment is collected.</p>
<form method="POST" action="{{ route('booking-drafts.confirm', $draft) }}">@csrf<input type="hidden" name="review_token" value="{{ $review['token'] }}"><button class="button" type="submit">{{ $review['mode'] === 'waitlist' ? 'Confirm waitlist request' : 'Confirm reservation' }} →</button></form>
<p><a class="back-link" href="{{ route('booking-drafts.seats', $draft) }}">← Return to seat selection</a></p></div></section>
@endsection
