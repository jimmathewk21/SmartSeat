<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SmartSeat — a considered way to plan your next railway journey across India.">
    <title>@yield('title', 'A better journey begins here') · SmartSeat</title>
    <link rel="stylesheet" href="{{ asset('css/smartseat.css') }}">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <div class="shell nav">
        <a class="brand" href="{{ route('home') }}" aria-label="SmartSeat home"><span class="brand-mark" aria-hidden="true">S<span>↗</span></span>SmartSeat<span class="brand-dot">.</span></a>
        <nav class="nav-links" aria-label="Main navigation">
            <a href="{{ route('trains.search') }}">Find trains</a>
            @auth('web')
                <a href="{{ route('bookings.index') }}">My bookings</a>
                <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard', 'administrator.dashboard')) aria-current="page" @endif>Dashboard</a>
                <span class="role-label">{{ auth('web')->user()->isAdministrator() ? 'Administrator' : 'Passenger' }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-small button-outline" type="submit">Log out</button></form>
            @else
                <a href="{{ route('login') }}" @if(request()->routeIs('login')) aria-current="page" @endif>Log in</a>
                <a class="button button-small" href="{{ route('register') }}">Create account <span aria-hidden="true">↗</span></a>
            @endauth
        </nav>
    </div>
</header>
<main id="main" tabindex="-1">@yield('content')</main>
<footer class="site-footer shell"><span><strong>SmartSeat.</strong> Made for the journey ahead.</span><span>An independent railway booking application.</span></footer>
</body>
</html>
