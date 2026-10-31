@extends('layouts.booking')
@section('title', 'Choose your seats')
@section('booking-content')
<div class="page-heading"><div><span class="eyebrow">02 · Seat selection</span><h1>A place for everyone.</h1><p class="muted">{{ $details['travel_class'] }} · Choose a distinct seat for each passenger. Seats are not held until confirmation.</p></div></div>
@include('bookings.partials.journey')
<section class="panel railway-form railway-list"><span class="eyebrow">Your preferences, considered</span><h2>Recommended seats</h2>
@if($recommendations === [])<p>No currently available group of seats matches all required accessibility needs. Update your preferences, choose manually where appropriate, or review a waitlist request.</p>
@else
<form method="POST" action="{{ route('booking-drafts.review.store', $draft) }}">@csrf<input type="hidden" name="mode" value="reserve">
@foreach($recommendations as $i => $suggestion)
@php($item = $suggestionInventory->get($suggestion['id']))
<div class="recommendation-item"><strong>{{ $details['passengers'][$i]['name'] }} · {{ $item->seat->coach->label }} / {{ $item->seat->number }}</strong>
<ul>@foreach($suggestion['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul>
<input type="hidden" name="seats[{{ $i }}]" value="{{ $suggestion['id'] }}"></div>
@endforeach
<button class="button button-outline" type="submit">Use these suggestions for review</button></form>
@endif
<p class="muted">Suggestions never replace your selection automatically. Availability is checked again when the booking transaction commits.</p></section>
<section class="panel railway-form railway-list"><h2>Choose coaches</h2>
@if($coaches->isEmpty())<p class="muted">No seat inventory has been initialized for this class. You can request a place on the waitlist.</p>
@else
<form method="GET" action="{{ route('booking-drafts.seats', $draft) }}"><div class="railway-fields">
@foreach($details['passengers'] as $i => $person)<div class="field"><label for="coach-{{ $i }}">{{ $person['name'] }} · Coach</label><select id="coach-{{ $i }}" name="coach_ids[{{ $i }}]">@foreach($coaches as $coach)<option value="{{ $coach->id }}" @selected($coachIds[$i] == $coach->id)>{{ $coach->label }} · {{ $coach->available_seats }} available</option>@endforeach</select></div>@endforeach
</div><button class="button button-small button-outline" type="submit">Show seats in these coaches</button></form>
@endif</section>
<section class="panel railway-form railway-list"><h2>Select your seats</h2>
<form method="POST" action="{{ route('booking-drafts.review.store', $draft) }}">@csrf<input type="hidden" name="mode" value="reserve"><div class="railway-fields">
@foreach($details['passengers'] as $i => $person)<div class="field"><label for="seat-{{ $i }}">{{ $person['name'] }} · Seat</label><select id="seat-{{ $i }}" name="seats[{{ $i }}]" required><option value="">Choose an available seat</option>
@foreach($choices->get($coachIds[$i], collect()) as $inventory)<option value="{{ $inventory->id }}" @selected(old('seats.'.$i) == $inventory->id)>{{ $inventory->seat->coach->label }} / {{ $inventory->seat->number }} · {{ str_replace('_', ' ', $inventory->seat->berth_type) }}</option>@endforeach</select></div>@endforeach
</div><p class="muted">All selected seats must still be available when you confirm. Earlier waitlist groups have priority.</p><button class="button" type="submit">Review reservation →</button></form></section>
<section class="panel railway-form railway-list"><span class="eyebrow">No seats for your group?</span><h2>Join the waitlist.</h2><p class="muted">Join only when there are too few seats or earlier groups are waiting. Your group is promoted together, in FIFO order within {{ $details['travel_class'] }}, when enough seats become available. Automatic promotion may choose any coach in that class.</p>
<form method="POST" action="{{ route('booking-drafts.review.store', $draft) }}">@csrf<input type="hidden" name="mode" value="waitlist"><button class="button button-outline" type="submit">Review waitlist request →</button></form></section>
@endsection
