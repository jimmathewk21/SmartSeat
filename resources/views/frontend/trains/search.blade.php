<!DOCTYPE html>
<html lang="en">
<head>
    <title>Find a train | SmartSeat</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Search scheduled train journeys and find available seats with SmartSeat.">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/train-search.css') }}" rel="stylesheet">
</head>
<body class="train-search-page">
<a class="train-skip-link" href="#train-search">Skip to train search</a>
<div id="wrapper">
    @include('frontend.trains.partials.header')

    <main>
        <section class="train-hero" aria-labelledby="train-page-title">
            <div class="train-container train-hero-grid">
                <div class="train-hero-copy">
                    <p class="train-eyebrow">THE JOURNEY IS PART OF THE STORY</p>
                    <h1 id="train-page-title">Find your way.<br><em>See India anew.</em></h1>
                    <p class="train-hero-intro">Early trains. Window-seat daydreams. A different India at every stop. Find a journey worth looking forward to.</p>
                    <a class="train-hero-link" href="#train-search">Let's get you there <span aria-hidden="true">↘</span></a>
                    <div class="train-hero-footnote"><span aria-hidden="true">01 —</span> YOUR NEXT CHAPTER, BY RAIL</div>
                </div>
                <figure class="train-hero-photo">
                    <img src="{{ asset('images/railway/railway-hero.webp') }}" alt="A Darjeeling Himalayan Railway train winding through lush greenery" width="1600" height="2240" fetchpriority="high" data-rail-image>
                    <figcaption><span aria-hidden="true">◉</span> Darjeeling, West Bengal <small>A view worth slowing down for.</small></figcaption>
                </figure>
            </div>
            <div class="train-container train-hero-search">
                @include('frontend.trains.partials.search-panel')
            </div>
        </section>
        <div class="train-container train-content">
            @if(session('newsletter_message'))
                <div class="train-alert train-alert-success" role="status">
                    <p>{{ session('newsletter_message') }}</p>
                </div>
            @endif

            @if(session('message'))
                <div class="train-alert train-alert-success" role="status">
                    <span class="train-alert-icon" aria-hidden="true">✓</span>
                    <div>
                        <p>{{ session('message') }}</p>
                        @if(session('waitlist_entry_id'))
                            <p class="train-alert-detail">Waitlist request #{{ session('waitlist_entry_id') }} · Status: Waiting.</p>
                            @auth<a class="train-alert-link" href="{{ route('frontend.trains.bookings') }}">View waitlist status</a>@endauth
                        @endif
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="train-alert train-alert-error" role="alert" aria-labelledby="train-error-title">
                    <span class="train-alert-icon" aria-hidden="true">!</span>
                    <div>
                        <h2 id="train-error-title">Please check your request</h2>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($searched)
                @php
                    $selectedOrigin = $stations->firstWhere('id', (int) request('from_station_id'));
                    $selectedDestination = $stations->firstWhere('id', (int) request('to_station_id'));
                @endphp
                <section class="train-results" id="train-results" aria-labelledby="train-results-title" aria-live="polite">
                    <div class="train-results-heading">
                        <div>
                            <p class="train-kicker">Your search</p>
                            <h2 id="train-results-title">
                                {{ $selectedOrigin?->name ?? 'Origin' }}
                                <span class="train-route-arrow" aria-hidden="true">→</span>
                                {{ $selectedDestination?->name ?? 'Destination' }}
                            </h2>
                            <p class="train-results-subtitle">
                                {{ request('date') ? \Illuminate\Support\Carbon::parse(request('date'))->format('l, j F Y') : 'Select a journey date' }}
                                <span class="train-result-separator" aria-hidden="true">·</span>
                                {{ $trips->count() }} {{ \Illuminate\Support\Str::plural('journey', $trips->count()) }} found
                            </p>
                        </div>
                        <a class="train-edit-search" href="#train-search">Edit search <span aria-hidden="true">↗</span></a>
                    </div>

                    @if($trips->isEmpty())
                        <div class="train-empty-state" role="status">
                            <div class="train-empty-icon" aria-hidden="true">
                                <svg viewBox="0 0 48 48"><path d="M10 31h28M14 31V18a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4v13M17 36l-3 4m17-4 3 4M18 21h12m-8-7v-4m4 4v-4"></path><circle cx="17" cy="31" r="2"></circle><circle cx="31" cy="31" r="2"></circle></svg>
                            </div>
                            <h3>No journeys found for this search</h3>
                            <p>Try another date or check the stations you selected.</p>
                            <a href="#train-search" class="train-secondary-button">Change search</a>
                        </div>
                    @else
                        <div class="train-journey-list">
                            @foreach($trips as $trip)
                                @php
                                    $departure = $trip->search_departure_at;
                                    $arrival = $trip->search_arrival_at;
                                    $durationMinutes = max(0, (int) $departure->diffInMinutes($arrival));
                                    $durationLabel = intdiv($durationMinutes, 60) . 'h ' . str_pad((string) ($durationMinutes % 60), 2, '0', STR_PAD_LEFT) . 'm';
                                @endphp
                                <article class="train-journey-card" aria-labelledby="train-trip-{{ $trip->id }}">
                                    <div class="journey-main">
                                        <div class="journey-train-info">
                                            <div class="journey-train-mark" aria-hidden="true">
                                                <svg viewBox="0 0 28 28"><path d="M8 4h12a4 4 0 0 1 4 4v9H4V8a4 4 0 0 1 4-4Zm-4 16h20l-3 4H7l-3-4Zm5-12h4v4H9zm7 0h4v4h-4z"></path><circle cx="9" cy="20" r="1.5"></circle><circle cx="19" cy="20" r="1.5"></circle></svg>
                                            </div>
                                            <div>
                                                <h3 id="train-trip-{{ $trip->id }}">{{ $trip->train->name }}</h3>
                                                <p>{{ $trip->train->code }} <span aria-hidden="true">·</span> Scheduled service</p>
                                            </div>
                                        </div>

                                        <div class="journey-timeline" aria-label="Departs {{ $departure->format('g:i A') }} from {{ $trip->origin_station?->name }}; arrives {{ $arrival->format('g:i A') }} at {{ $trip->destination_station?->name }}">
                                            <div class="journey-time-block">
                                                <strong>{{ $departure->format('g:i A') }}</strong>
                                                <span>{{ $trip->origin_station?->name }}</span>
                                                <small>{{ $departure->format('D, j M') }}</small>
                                            </div>
                                            <div class="journey-duration">
                                                <span>{{ $durationLabel }}</span>
                                                <div class="journey-line" aria-hidden="true"><i></i></div>
                                                <small>Direct route</small>
                                            </div>
                                            <div class="journey-time-block journey-arrival">
                                                <strong>{{ $arrival->format('g:i A') }}</strong>
                                                <span>{{ $trip->destination_station?->name }}</span>
                                                <small>{{ $arrival->format('D, j M') }}</small>
                                            </div>
                                        </div>

                                        <div class="journey-availability {{ $trip->inventories->isNotEmpty() ? 'is-available' : 'is-full' }}">
                                            <span class="availability-dot" aria-hidden="true"></span>
                                            @if($trip->inventories->isNotEmpty())
                                                {{ $trip->inventories->count() }} {{ \Illuminate\Support\Str::plural('seat', $trip->inventories->count()) }} available
                                            @else
                                                No seats currently available
                                            @endif
                                        </div>
                                    </div>

                                    <div class="journey-actions">
                                        @auth
                                            @if($trip->inventories->isNotEmpty())
                                                <details class="journey-disclosure">
                                                    <summary class="train-primary-button">Choose seats <span aria-hidden="true">＋</span></summary>
                                                    <div class="journey-form-panel">
                                                        <form method="POST" action="{{ route('frontend.trains.book', $trip) }}" class="train-passenger-form" data-passenger-form>
                                                            @csrf
                                                            <input type="hidden" name="boarding_station_id" value="{{ $trip->origin_station?->id }}">
                                                            <input type="hidden" name="destination_station_id" value="{{ $trip->destination_station?->id }}">
                                                            <h4>Available seats</h4>
                                                            <p class="journey-form-hint">Select one seat for each passenger. Submitting confirms the booking immediately if the seats remain available.</p>
                                                            <div class="train-seat-grid">
                                                                @foreach($trip->inventories as $inventory)
                                                                    <label class="train-seat-option">
                                                                        <input type="checkbox" name="inventory_ids[]" value="{{ $inventory->id }}">
                                                                        <span class="train-seat-check" aria-hidden="true"></span>
                                                                        <span class="train-seat-copy">
                                                                            <strong>{{ $inventory->seat->coach->code }}-{{ $inventory->seat->number }}</strong>
                                                                            <small>{{ $inventory->seat->coach->class_code }}@if($inventory->seat->berth_type) · {{ ucfirst($inventory->seat->berth_type) }}@endif</small>
                                                                            @if($inventory->seat->is_accessible)<em>Accessible</em>@endif
                                                                        </span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                            <p class="train-form-feedback" data-seat-feedback aria-live="polite"></p>

                                                            <div class="train-form-divider"></div>
                                                            <div class="train-passenger-heading">
                                                                <div><h4>Passenger details</h4><p>Enter details for everyone travelling.</p></div>
                                                                <span data-passenger-count>1 of 6</span>
                                                            </div>
                                                            <div class="train-passenger-rows" data-passenger-rows>
                                                                <div class="train-passenger-row">
                                                                    <label class="train-field"><span>Passenger name <em aria-hidden="true">*</em></span>
                                                                        <input type="text" name="passengers[0][name]" maxlength="255" autocomplete="name" placeholder="Full name" required>
                                                                    </label>
                                                                    <label class="train-field"><span>Age <small>Optional</small></span>
                                                                        <input type="number" name="passengers[0][age]" min="0" max="120" inputmode="numeric" placeholder="Age">
                                                                    </label>
                                                                    <label class="train-field"><span>Gender <small>Optional</small></span>
                                                                        <select name="passengers[0][gender]">
                                                                            <option value="">Prefer not to say</option>
                                                                            <option value="female">Female</option>
                                                                            <option value="male">Male</option>
                                                                            <option value="other">Other</option>
                                                                        </select>
                                                                    </label>
                                                                    <label class="train-accessible-check"><input type="checkbox" name="passengers[0][requires_accessible_seat]" value="1"> <span>Needs an accessible seat</span></label>
                                                                    <button class="train-remove-passenger" type="button" data-remove-passenger hidden>Remove passenger</button>
                                                                </div>
                                                            </div>
                                                            <div class="train-passenger-controls">
                                                                <button class="train-add-passenger" type="button" data-add-passenger>＋ Add passenger</button>
                                                                <span class="train-passenger-limit" data-passenger-limit aria-live="polite"></span>
                                                            </div>
                                                            <button class="train-confirm-button" type="submit">Confirm booking <span aria-hidden="true">→</span></button>
                                                        </form>
                                                    </div>
                                                </details>
                                            @endif

                                            <details class="journey-disclosure journey-waitlist">
                                                <summary class="train-secondary-button">{{ $trip->inventories->isNotEmpty() ? 'Waitlist options' : 'Join waitlist' }} <span aria-hidden="true">＋</span></summary>
                                                <div class="journey-form-panel">
                                                    <p class="journey-form-hint">Waiting requests are considered by request time, then entry number. Eligible seats may be offered if a booking is cancelled.</p>
                                                    <form method="POST" action="{{ route('frontend.trains.waitlist', $trip) }}" class="train-passenger-form" data-passenger-form>
                                                        @csrf
                                                        <input type="hidden" name="boarding_station_id" value="{{ $trip->origin_station?->id }}">
                                                        <input type="hidden" name="destination_station_id" value="{{ $trip->destination_station?->id }}">
                                                        <input type="hidden" name="from_station_id" value="{{ request('from_station_id') }}">
                                                        <input type="hidden" name="to_station_id" value="{{ request('to_station_id') }}">
                                                        <input type="hidden" name="date" value="{{ request('date') }}">
                                                        <div class="train-passenger-rows" data-passenger-rows>
                                                            <div class="train-passenger-row">
                                                                <label class="train-field"><span>Passenger name <em aria-hidden="true">*</em></span>
                                                                    <input type="text" name="passengers[0][name]" maxlength="255" autocomplete="name" placeholder="Full name" required>
                                                                </label>
                                                                <label class="train-field"><span>Age <small>Optional</small></span>
                                                                    <input type="number" name="passengers[0][age]" min="0" max="120" inputmode="numeric" placeholder="Age">
                                                                </label>
                                                                <label class="train-field"><span>Gender <small>Optional</small></span>
                                                                    <select name="passengers[0][gender]">
                                                                        <option value="">Prefer not to say</option>
                                                                        <option value="female">Female</option>
                                                                        <option value="male">Male</option>
                                                                        <option value="other">Other</option>
                                                                    </select>
                                                                </label>
                                                                <label class="train-accessible-check"><input type="checkbox" name="passengers[0][requires_accessible_seat]" value="1"> <span>Needs an accessible seat</span></label>
                                                                <button class="train-remove-passenger" type="button" data-remove-passenger hidden>Remove passenger</button>
                                                            </div>
                                                        </div>
                                                        <div class="train-passenger-controls">
                                                            <button class="train-add-passenger" type="button" data-add-passenger>＋ Add passenger</button>
                                                            <span class="train-passenger-limit" data-passenger-limit aria-live="polite"></span>
                                                        </div>
                                                        <button class="train-confirm-button train-confirm-secondary" type="submit">Join waitlist <span aria-hidden="true">→</span></button>
                                                    </form>
                                                </div>
                                            </details>
                                        @else
                                            <a class="train-primary-button" href="{{ route('frontend.trains.login', request()->only(['from_station_id', 'to_station_id', 'date'])) }}">Sign in to book <span aria-hidden="true">→</span></a>
                                        @endauth
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            @else
                @include('frontend.trains.partials.explore')
            @endif
        </div>
    </main>
</div>

@include('frontend.trains.partials.footer')
<script src="{{ asset('js/train-search.js') }}" defer></script>
</body>
</html>
