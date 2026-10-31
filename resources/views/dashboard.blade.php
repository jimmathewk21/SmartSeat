@extends('layouts.app')
@section('title', 'Your journeys')
@section('content')
<div class="shell dashboard">
    <div class="page-heading"><div><span class="eyebrow">Your personal travel space</span><h1>Hello, {{ auth('web')->user()->name }}.</h1><p class="muted">Here's to your next great journey.</p></div><span class="badge">Passenger account</span></div>
    <div class="dashboard-grid">
        <section class="panel journeys-panel" aria-labelledby="journeys-heading">
            <div class="panel-heading"><h2 id="journeys-heading">Your journeys</h2><span class="muted">All in one place</span></div>
            <div class="empty-state"><span class="empty-icon" aria-hidden="true">↗</span><h3>Your journeys, all in one place</h3><p>View your reservations, waitlist requests and cancellation history.</p><a class="button button-small" href="{{ route('bookings.index') }}">Open booking history →</a><p><a class="back-link" href="{{ route('trains.search') }}">Find your next train →</a></p></div>
        </section>
        <aside class="panel travel-note"><span class="eyebrow">A little inspiration</span><h2>The journey is<br>part of the joy.</h2><p>Leave room for a window view, a shared story and something unexpected along the way.</p><div class="note-line" aria-hidden="true">○ ─ ─ ─ ─ ○</div></aside>
    </div>
    <section class="account-summary"><div><h2>Your account, ready to go.</h2><p class="muted">Signed in as {{ auth('web')->user()->email }}</p></div><a class="text-link" href="{{ route('password.confirm') }}">Confirm your password →</a></section>
</div>
@endsection
