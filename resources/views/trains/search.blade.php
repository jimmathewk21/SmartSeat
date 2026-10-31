@extends('layouts.app')
@section('title', 'Find your next journey')
@section('content')
<div class="shell dashboard">
    <div class="page-heading"><div><span class="eyebrow">Good journeys begin here</span><h1>Where to next?</h1><p class="muted">Explore scheduled trains between stations. All journey times are in India time.</p></div></div>
    <x-form-errors />
    <section class="panel railway-form">
        <form method="GET" action="{{ route('trains.search') }}">
            <div class="railway-fields">
                <div class="field"><label for="origin">From station code</label><input id="origin" name="origin" value="{{ \App\Support\FormValue::text(old('origin', request('origin'))) }}" placeholder="Origin code" maxlength="5" required><small>Use the uppercase railway station code.</small></div>
                <div class="field"><label for="destination">To station code</label><input id="destination" name="destination" value="{{ \App\Support\FormValue::text(old('destination', request('destination'))) }}" placeholder="Destination code" maxlength="5" required><small>Choose a different station on the train's onward route.</small></div>
                <div class="field"><label for="journey_date">Boarding date</label><input id="journey_date" name="journey_date" type="date" value="{{ \App\Support\FormValue::text(old('journey_date', request('journey_date'))) }}" required><small>The date you board, even if the train starts a day earlier.</small></div>
            </div>
            <button class="button" type="submit">Find trains <span aria-hidden="true">→</span></button>
        </form>
    </section>
    <section class="railway-list" aria-live="polite">
    @if($trips === null)
        <div class="panel empty-state"><span class="empty-icon" aria-hidden="true">↗</span><h2>Your next chapter awaits.</h2><p>Enter two station codes and a boarding date to explore the available schedule.</p></div>
    @elseif($trips->isEmpty())
        <div class="panel empty-state"><h2>No matching journeys</h2><p>No scheduled trains serve these stations in this order on your boarding date. Try another date or route.</p></div>
    @else
        <div class="results-heading"><h2>{{ $criteria['origin'] }} → {{ $criteria['destination'] }}</h2><span class="muted">{{ $trips->total() }} scheduled journeys</span></div>
        @foreach($trips as $trip)
        <article class="panel search-result">
            <div class="result-title"><div><span class="eyebrow">Train {{ $trip->train->number }}</span><h2>{{ $trip->train->name }}</h2></div><a class="button button-small" href="{{ route('bookings.create', ['trip' => $trip, 'origin' => $criteria['origin'], 'destination' => $criteria['destination']]) }}">Book or join waitlist →</a></div>
            <div class="journey-times"><div><strong>{{ $criteria['origin'] }}</strong><span>{{ $trip->boarding_at->format('d-M-Y H:i A') }}</span></div><span aria-hidden="true">──────── →</span><div><strong>{{ $criteria['destination'] }}</strong><span>{{ $trip->arrival_at->format('d-M-Y H:i A') }}</span></div></div>
            <div class="coach-availability">
            @forelse($coaches->get($trip->id, collect()) as $coach)
                <span class="availability-chip"><strong>{{ $coach->label }} · {{ $coach->travel_class }}</strong><span>{{ $coach->available_seats }} of {{ $coach->total_seats }} seats available</span></span>
            @empty<p class="muted">Seat inventory has not been initialized for this journey.</p>@endforelse
            </div>
            <small class="muted">Availability is a current snapshot. Seats are reserved for a whole trip in this foundation.</small>
        </article>
        @endforeach
        {{ $trips->links('components.pagination') }}
    @endif
    </section>
</div>
@endsection
