@extends('layouts.app')
@section('content')
<div class="shell railway-workspace">
    <aside class="railway-sidebar" aria-label="Railway administration">
        <span class="eyebrow">Control room</span>
        <a href="{{ route('administrator.dashboard') }}">◇ Overview</a>
        <a href="{{ route('administrator.stations.index') }}">⌖ Stations</a>
        <a href="{{ route('administrator.trains.index') }}">≋ Train catalogue</a>
        <a href="{{ route('administrator.trips.index') }}">▦ Trips & inventory</a>
        <a href="{{ route('administrator.intelligence.index') }}">◇ Prediction models</a>
        @isset($train)
            <span class="sidebar-context">{{ $train->number }} · {{ $train->name }}</span>
            <a href="{{ route('administrator.trains.stops.index', $train) }}">↗ Route stops</a>
            <a href="{{ route('administrator.trains.coaches.index', $train) }}">▤ Coaches & seats</a>
            <a href="{{ route('administrator.trains.trips.index', $train) }}">◷ Schedule trips</a>
        @endisset
        <a href="{{ route('trains.search') }}">→ Passenger search</a>
    </aside>
    <div class="railway-main">
        @if(session('status'))<div class="admin-notice" role="status">{{ session('status') }}</div>@endif
        <x-form-errors />
        @yield('management')
    </div>
</div>
@endsection
