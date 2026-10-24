@php
    $destinations = [
        ['Jaipur', 'Rajasthan', 'The pink city, in a different light.', 'Hawa Mahal’s intricate sandstone facade', 'jaipur'],
        ['Varanasi', 'Uttar Pradesh', 'A morning by the Ganga.', 'The riverfront ghats of Varanasi', 'varanasi'],
        ['Udaipur', 'Rajasthan', 'Palaces. Lakes. A slower pace.', 'Udaipur’s palaces beside Lake Pichola', 'udaipur'],
        ['Mumbai', 'Maharashtra', 'A city that keeps you moving.', 'The Gateway of India in Mumbai', 'mumbai'],
        ['Kochi', 'Kerala', 'Where the coast tells its stories.', 'Traditional Chinese fishing nets on the Kochi waterfront', 'kochi'],
        ['Darjeeling', 'West Bengal', 'Take the long way to the hills.', 'The Himalayan peaks seen from Darjeeling', 'darjeeling'],
    ];
@endphp
<section class="train-explore" aria-labelledby="explore-title">
    <div class="train-editorial-heading" data-reveal>
        <div><p class="train-kicker">A little inspiration</p><h2 id="explore-title">Six places. <em>Countless stories.</em></h2></div>
        <p>From first light on the ghats to a last cup of hill-station chai. Where will you go next?</p>
    </div>
    <div class="train-destination-grid">
        @foreach($destinations as [$city, $state, $description, $alt, $image])
            <figure class="train-destination" data-reveal>
                <img src="{{ asset('images/railway/' . $image . '.webp') }}" alt="{{ $alt }}" width="720" height="540" loading="lazy" decoding="async" data-rail-image>
                <figcaption><span>{{ $state }}</span><h3>{{ $city }}</h3><p>{{ $description }}</p></figcaption>
            </figure>
        @endforeach
    </div>
    <div class="train-explore-note"><p>Places to inspire your travels. Available journeys depend on the stations and scheduled services in SmartSeat.</p><a href="#train-search">Plan your journey <span aria-hidden="true">↗</span></a></div>
    <details class="train-photo-credits">
        <summary>Photography &amp; credits</summary>
        <p>Photographs from Pexels, used under the <a href="https://www.pexels.com/license/" rel="noopener" target="_blank">Pexels License</a>. Resized and cropped for display. No endorsement implied.</p>
        <ul>
            <li><a href="https://www.pexels.com/photo/darjeeling-toy-train-on-scenic-route-33565206/">Railway hero — Soumalya Halder</a></li>
            <li><a href="https://www.pexels.com/photo/hawa-mahal-palace-facade-in-jaipur-india-30171014/">Jaipur — Aditya Oberai</a></li>
            <li><a href="https://www.pexels.com/photo/vibrant-scene-at-varanasi-ghats-in-india-35868472/">Varanasi — The Capturist</a></li>
            <li><a href="https://www.pexels.com/photo/scenic-view-of-udaipur-s-historic-lakefront-palaces-33658453/">Udaipur — Anshul Kaushik</a></li>
            <li><a href="https://www.pexels.com/photo/gateway-of-india-in-mumbai-in-india-21319632/">Mumbai — Shivam</a></li>
            <li><a href="https://www.pexels.com/photo/a-view-of-a-harbor-with-boats-and-a-dock-27833051/">Kochi — Jinu Safarin</a></li>
            <li><a href="https://www.pexels.com/photo/landscape-photo-of-a-snowy-mountaintop-2408962/">Darjeeling — Vinayak Khithani</a></li>
        </ul>
    </details>
</section>
