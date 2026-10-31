@extends('layouts.booking')
@section('title', 'Who is travelling?')
@section('booking-content')
<div class="page-heading"><div><span class="eyebrow">01 · Passengers / 02 · Seats / 03 · Review</span><h1>Who is coming along?</h1><p class="muted">Reserve for up to {{ config('smartseat.booking.max_passengers') }} passengers in one group.</p></div></div>
@include('bookings.partials.journey')
<section class="panel railway-form railway-list">
    <form method="GET" action="{{ route('bookings.create', $trip) }}" class="passenger-count">
        <input type="hidden" name="origin" value="{{ $journey['origin']->station->code }}">
        <input type="hidden" name="destination" value="{{ $journey['destination']->station->code }}">
        <div class="field"><label for="count">Number of passengers</label><select id="count" name="count">@for($i = 1; $i <= config('smartseat.booking.max_passengers'); $i++)<option value="{{ $i }}" @selected($count == $i)>{{ $i }}</option>@endfor</select></div>
        <button class="button button-small button-outline" type="submit">Set group size</button>
        <small class="muted">Set this before entering names. Changing it starts a fresh form.</small>
    </form>
</section>
<form method="POST" action="{{ route('booking-drafts.details', $trip) }}">
    @csrf<input type="hidden" name="draft_key" value="{{ $key }}">
    <section class="panel railway-form railway-list"><h2>Your journey</h2><div class="railway-fields">
        <div class="field"><label for="boarding_order">Boarding station</label><select id="boarding_order" name="boarding_order" required>@foreach($journey['stops'] as $stop)<option value="{{ $stop->stop_order }}" @selected(old('boarding_order', $journey['origin']->stop_order) == $stop->stop_order)>{{ $stop->station->code }} · {{ $stop->station->name }}</option>@endforeach</select></div>
        <div class="field"><label for="alighting_order">Destination station</label><select id="alighting_order" name="alighting_order" required>@foreach($journey['stops'] as $stop)<option value="{{ $stop->stop_order }}" @selected(old('alighting_order', $journey['destination']->stop_order) == $stop->stop_order)>{{ $stop->station->code }} · {{ $stop->station->name }}</option>@endforeach</select></div>
        <div class="field"><label for="travel_class">Travel class</label><select id="travel_class" name="travel_class" required>@foreach($classes as $class)<option value="{{ $class }}" @selected(old('travel_class') === $class)>{{ $class }}</option>@endforeach</select><small>The whole group travels in one class. Seats may be in different coaches.</small></div>
    </div></section>
    <section class="panel railway-form railway-list"><h2>Passenger details</h2>
    @for($i = 0; $i < $count; $i++)
        <fieldset class="passenger-card"><legend>Passenger {{ $i + 1 }}</legend><div class="railway-fields">
            <div class="field"><label for="name-{{ $i }}">Full name</label><input id="name-{{ $i }}" name="passengers[{{ $i }}][name]" value="{{ \App\Support\FormValue::text(old('passengers.'.$i.'.name')) }}" maxlength="150" required></div>
            <div class="field"><label for="age-{{ $i }}">Age</label><input id="age-{{ $i }}" name="passengers[{{ $i }}][age]" type="number" value="{{ \App\Support\FormValue::text(old('passengers.'.$i.'.age')) }}" min="0" max="120" required></div>
            <div class="field"><label for="gender-{{ $i }}">Gender</label><select id="gender-{{ $i }}" name="passengers[{{ $i }}][gender]" required>@foreach(['female'=>'Female','male'=>'Male','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $value => $label)<option value="{{ $value }}" @selected(old('passengers.'.$i.'.gender') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label for="berth-{{ $i }}">Preferred berth</label><select id="berth-{{ $i }}" name="passengers[{{ $i }}][preferred_berth]">@foreach(['any'=>'No preference','lower'=>'Lower','middle'=>'Middle','upper'=>'Upper','side_lower'=>'Side lower','side_upper'=>'Side upper','chair'=>'Chair'] as $value => $label)<option value="{{ $value }}" @selected(old('passengers.'.$i.'.preferred_berth', 'any') === $value)>{{ $label }}</option>@endforeach</select><small>Used for suggestions. Your explicit seat choice can override this preference.</small></div>
        </div>
        <label class="checkbox"><input type="checkbox" name="passengers[{{ $i }}][accessible_seat_required]" value="1" @checked(old('passengers.'.$i.'.accessible_seat_required'))> Require an administrator-verified accessible seat</label>
        <small class="muted">No medical details are needed. Verify suitability with the railway operator; software cannot verify physical accessibility.</small></fieldset>
    @endfor
    <label class="checkbox"><input type="checkbox" name="keep_group_together" value="1" @checked(old('keep_group_together'))> Prefer seats close together in the same coach</label>
    <p class="muted">Reservations and waitlist promotions apply to everyone in the group together. No payment is collected in this release.</p>
    <button class="button" type="submit">Choose seats →</button></section>
</form>
@endsection
