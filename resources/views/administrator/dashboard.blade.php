@extends('layouts.app')
@section('title', 'Administrator overview')
@section('content')
<div class="shell dashboard">
    <div class="page-heading"><div><span class="eyebrow">SmartSeat administration</span><h1>A clear view ahead.</h1><p class="muted">Welcome, {{ auth('web')->user()->name }}. Your workspace is ready.</p></div><span class="badge badge-admin">Administrator access</span></div>
    <div class="admin-notice"><span aria-hidden="true">◇</span><div><strong>A secure starting point.</strong><p>This area is reserved for administrators. Passenger accounts cannot access it.</p></div></div>
    <section class="panel" aria-labelledby="operations-heading">
        <div class="admin-tools"><a class="button button-small" href="{{ route('administrator.stations.index') }}">Manage stations</a><a class="button button-small button-outline" href="{{ route('administrator.trains.index') }}">Train catalogue</a><a class="text-link" href="{{ route('administrator.trips.index') }}">Trips & inventory →</a></div>
        <div class="panel-heading"><h2 id="operations-heading">Railway operations</h2><span class="muted">Overview</span></div>
        <div class="admin-tools"><a class="text-link" href="{{ route('administrator.intelligence.index') }}">Prediction models & training data →</a></div>
        <div class="empty-state"><span class="empty-icon" aria-hidden="true">≋</span><h3>Your railway control room</h3><p>Manage stations, configure routes and seating, then schedule journeys and initialize their inventory using the tools above.</p><span class="coming-soon">Booking details include authorized cancellation and activity history</span></div>
    </section>
    <section class="account-summary"><div><h2>Access with confidence.</h2><p class="muted">Signed in as {{ auth('web')->user()->email }}</p></div><a class="text-link" href="{{ route('password.confirm') }}">Confirm your password →</a></section>
</div>
@endsection
