<footer class="smartseat-shared-footer" aria-label="SmartSeat railway information">
    <style>
        .smartseat-shared-footer { margin-top: 0; padding: 32px 0 18px; background: #103e38; color: #fff; }
        .smartseat-shared-footer-inner { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }
        .smartseat-shared-footer-grid { display: grid; grid-template-columns: minmax(0, 2fr) repeat(2, minmax(130px, 1fr)); gap: 28px; }
        .smartseat-shared-footer h2 { margin: 0 0 10px; color: #d5ed99; font-size: 13px; font-weight: 800; letter-spacing: .04em; }
        .smartseat-shared-footer p { max-width: 400px; margin: 8px 0 0; color: rgba(255,255,255,.78); font-size: 13px; line-height: 1.6; }
        .smartseat-shared-footer a { display: block; width: fit-content; margin: 0 0 8px; color: #fff !important; font-size: 13px; text-decoration: none !important; }
        .smartseat-shared-footer a:hover { color: #d5ed99 !important; text-decoration: underline !important; }
        .smartseat-shared-footer-brand { color: #fff; font-size: 22px; font-weight: 800; letter-spacing: -.03em; text-decoration: none !important; }
        .smartseat-shared-footer-bottom { margin-top: 26px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,.16); color: rgba(255,255,255,.7); font-size: 11px; }
        @media (max-width: 640px) {
            .smartseat-shared-footer { padding-top: 26px; }
            .smartseat-shared-footer-grid { grid-template-columns: 1fr 1fr; gap: 20px; }
            .smartseat-shared-footer-brand-block { grid-column: 1 / -1; }
        }
    </style>
    <div class="smartseat-shared-footer-inner">
        <div class="smartseat-shared-footer-grid">
            <section class="smartseat-shared-footer-brand-block" aria-labelledby="smartseat-footer-title">
                <a id="smartseat-footer-title" class="smartseat-shared-footer-brand" href="{{ route('frontend.trains.home') }}">SmartSeat</a>
                <p>Search scheduled Indian railway journeys, book seats, and manage your travel in one place.</p>
            </section>
            <nav aria-label="Train booking links">
                <h2>Booking</h2>
                <a href="{{ route('frontend.trains.search') }}">Find Trains</a>
                @if(auth()->check() && auth()->user()->participant?->train_access_enabled)
                    <a href="{{ route('frontend.trains.bookings') }}">My Bookings</a>
                @else
                    <a href="{{ route('frontend.trains.login') }}">Sign In</a>
                    <a href="{{ route('frontend.trains.register') }}">Register</a>
                @endif
            </nav>
            <nav aria-label="Help links">
                <h2>Help</h2>
                <a href="{{ route('frontend.faqs.index') }}">FAQs</a>
                <a href="{{ route('frontend.contact.index') }}">Contact</a>
            </nav>
        </div>
        <div class="smartseat-shared-footer-bottom">&copy; {{ now()->year }} SmartSeat. All rights reserved.</div>
    </div>
</footer>

<style>
    html, body { top: 0 !important; margin-top: 0 !important; }
    iframe.skiptranslate { display: none !important; }
    .goog-te-banner-frame.skiptranslate, .goog-te-menu-frame.skiptranslate, #goog-gt-tt, body > .skiptranslate { display: none !important; }
</style>
<script>
    function googleTranslateElementInit() {
        var mountNode = document.getElementById('google_translate_element');
        if (!mountNode || !window.google || !window.google.translate || !window.google.translate.TranslateElement) return;

        new window.google.translate.TranslateElement({ pageLanguage: 'en', autoDisplay: false }, 'google_translate_element');
    }

    (function () {
        var mountNode = document.getElementById('google_translate_element');
        if (!mountNode || !document.body) return;
        mountNode.classList.add('notranslate');

        if (window.google && window.google.translate && window.google.translate.TranslateElement) {
            googleTranslateElementInit();
            return;
        }

        if (document.querySelector('script[data-google-translate-script="1"]')) return;
        var script = document.createElement('script');
        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        script.async = true;
        script.defer = true;
        script.setAttribute('data-google-translate-script', '1');
        document.body.appendChild(script);
    })();
</script>
