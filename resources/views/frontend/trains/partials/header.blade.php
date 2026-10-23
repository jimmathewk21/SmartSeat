<header class="train-site-header">
    <div class="train-container train-site-header-inner">
        <a class="train-site-brand" href="{{ route('frontend.trains.home') }}" aria-label="SmartSeat Rail home">
            <span class="train-site-brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" focusable="false"><path d="M9 4h14a4 4 0 0 1 4 4v12a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4Zm-4 11h22M10 9h4m4 0h4M10 28l3-4m9 4-3-4"/><circle cx="11" cy="19" r="1.5"/><circle cx="21" cy="19" r="1.5"/></svg>
            </span>
            <span class="train-site-brand-copy">
                <strong>SmartSeat</strong>
                <small>INDIAN RAIL JOURNEYS</small>
            </span>
        </a>

        <nav class="train-site-nav" aria-label="Train booking">
            <a href="{{ route('frontend.trains.home') }}">Find trains</a>
            @auth
                <a class="train-site-nav-action" href="{{ route('frontend.trains.bookings') }}">My bookings</a>
            @else
                <a href="{{ route('frontend.trains.login') }}">Sign in</a>
                <a class="train-site-nav-action" href="{{ route('frontend.trains.register') }}">Create account</a>
            @endauth
        </nav>
    </div>
</header>
