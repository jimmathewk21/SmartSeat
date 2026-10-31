@extends('layouts.app')
@section('content')
<div class="shell auth-layout">
    <aside class="auth-story"><span class="eyebrow">Your ticket to what's next</span><h1>Great journeys<br>start with<br><em>a hello.</em></h1><p>A familiar platform. An unfamiliar city. A little adventure. Make room for the journeys that matter.</p><div class="route-motif" aria-hidden="true"><span></span><i></i><span></span><i></i><span></span></div><span class="story-footnote">A simpler start. A better way forward.</span></aside>
    <section class="auth-card" aria-labelledby="auth-heading">@yield('form')</section>
</div>
@endsection
