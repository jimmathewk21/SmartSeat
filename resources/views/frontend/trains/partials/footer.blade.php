<footer class="smartseat-footer" aria-label="SmartSeat railway information">
    <div class="smartseat-footer-inner">
        <div class="smartseat-footer-grid">
            <section class="smartseat-footer-brand" aria-labelledby="smartseat-footer-brand-title">
                <a class="smartseat-footer-wordmark" href="{{ route('frontend.trains.search') }}" aria-label="SmartSeat home">
                    <span class="smartseat-footer-mark" aria-hidden="true">
                        <svg viewBox="0 0 32 32" focusable="false"><path d="M9 4h14a4 4 0 0 1 4 4v12a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4Zm-4 11h22M10 9h4m4 0h4M10 28l3-4m9 4-3-4"/><circle cx="11" cy="19" r="1.5"/><circle cx="21" cy="19" r="1.5"/></svg>
                    </span>
                    <span id="smartseat-footer-brand-title">SmartSeat</span>
                </a>
                <p class="smartseat-footer-tagline">A good journey starts with a little curiosity.</p>
                <p class="smartseat-footer-description">Find a train. Choose your seat. Keep the journey close.</p>
            </section>

            <nav class="smartseat-footer-column" aria-labelledby="smartseat-footer-booking-title">
                <h2 id="smartseat-footer-booking-title">Booking</h2>
                <a href="{{ route('frontend.trains.search') }}">Find Trains</a>
                <a href="{{ route('frontend.faqs.index') }}">FAQs</a>
                <a href="{{ route('frontend.contact.index') }}">Contact &amp; support</a>
                @auth
                    <a href="{{ route('frontend.trains.bookings') }}">My Bookings</a>
                    <p>Manage eligible cancellations and waitlist requests from My Bookings.</p>
                @else
                    <a href="{{ route('frontend.trains.login', ['continue' => 'bookings']) }}">My Bookings — Sign in</a>
                    <p>Sign in to view bookings, eligible cancellations, and waitlist requests.</p>
                @endauth
            </nav>

        </div>

        <div class="smartseat-footer-bottom">
            <p>&copy; {{ now()->year }} SmartSeat. All rights reserved.</p>
            <p>SmartSeat Train Booking System</p>
        </div>
    </div>
</footer>
