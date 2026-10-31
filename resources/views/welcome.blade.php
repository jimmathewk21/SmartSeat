@extends('layouts.app')
@section('title', 'Go places. Make memories.')
@section('content')
<section class="shell hero">
    <div class="hero-copy">
        <span class="eyebrow"><span class="status-dot"></span> A fresh perspective on rail travel</span>
        <h1>India, one<br>journey <em>closer.</em></h1>
        <p class="lead">The window seat. The chai break. The people waiting at the other end. Your next chapter starts with SmartSeat.</p>
        <div class="button-row">
            @auth('web')
                <a class="button" href="{{ route('dashboard') }}">Your dashboard <span aria-hidden="true">↗</span></a>
            @else
                <a class="button" href="{{ route('register') }}">Start your journey <span aria-hidden="true">↗</span></a>
                <a class="text-link" href="{{ route('login') }}">Already on board? Log in →</a>
            @endauth
        </div>
        <div class="hero-note"><span aria-hidden="true">◇</span> One account. A world of journeys ahead.</div>
    </div>
    <div class="journey-art" role="img" aria-label="Original illustration of a blue train travelling through green hills beneath an orange sun">
        <div class="art-caption"><span class="eyebrow">The scenic route</span><span aria-hidden="true">↗</span></div>
        <svg viewBox="0 0 600 490" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs><clipPath id="scene"><rect width="600" height="490" rx="24"/></clipPath></defs>
            <g clip-path="url(#scene)">
                <path fill="#e2eae0" d="M0 0h600v490H0z"/>
                <circle cx="418" cy="139" r="65" fill="#e98c50"/>
                <path d="M0 250Q90 110 230 253T600 221V490H0Z" fill="#bbcdb9"/>
                <path d="M0 314Q190 197 322 310T600 285V490H0Z" fill="#8eac94"/>
                <path d="M-30 404Q242 246 640 358" fill="none" stroke="#f7f4eb" stroke-width="55"/>
                <path d="M-30 404Q242 246 640 358" fill="none" stroke="#8a9489" stroke-width="3" stroke-dasharray="4 12"/>
                <path d="M-30 418Q242 260 640 372M-30 390Q242 232 640 344" fill="none" stroke="#627966" stroke-width="3"/>
                <g transform="translate(80 218) rotate(-5 200 70)">
                    <path d="M0 42Q0 18 24 18H333Q370 18 395 56L417 94H0Z" fill="#254d68"/>
                    <path d="M0 77H408L417 94H0Z" fill="#e5b76b"/>
                    <path d="M335 29Q359 33 377 59H335Z" fill="#b4d8dc"/>
                    <g fill="#c8e1de"><rect x="20" y="35" width="42" height="29" rx="5"/><rect x="77" y="35" width="42" height="29" rx="5"/><rect x="134" y="35" width="42" height="29" rx="5"/><rect x="191" y="35" width="42" height="29" rx="5"/><rect x="248" y="35" width="42" height="29" rx="5"/></g>
                    <path d="M310 23V87" stroke="#809ba6" stroke-width="2"/>
                    <g fill="#254339"><circle cx="45" cy="97" r="12"/><circle cx="105" cy="97" r="12"/><circle cx="276" cy="97" r="12"/><circle cx="350" cy="97" r="12"/></g>
                    <path d="M28 9H284" stroke="#254d68" stroke-width="5" stroke-linecap="round"/>
                </g>
                <g fill="#537c60"><path d="M33 277l16-47 16 47ZM525 311l20-60 20 60ZM551 324l15-44 15 44Z"/></g>
                <path d="M0 470Q166 389 320 431T600 418V490H0Z" fill="#73957a"/>
                <path d="M82 106h50m-25-8h50" stroke="#f7f4eb" stroke-width="5" stroke-linecap="round"/>
            </g>
        </svg>
        <div class="art-footer"><strong>Every journey has a story.</strong><span>Where will yours take you?</span></div>
    </div>
</section>
<section class="shell intro-strip" aria-label="Getting started">
    <div><span class="step-number">01</span><h2>Make yourself at home</h2><p>Create your passenger account in a few simple steps.</p></div>
    <div><span class="step-number">02</span><h2>Keep it all together</h2><p>A personal space for your travel plans and future bookings.</p></div>
    <div><span class="step-number">03</span><h2>Find your next journey</h2><p><a href="{{ route('trains.search') }}">Explore scheduled trains</a>, reserve seats or join a waitlist for your group.</p></div>
</section>
@endsection
