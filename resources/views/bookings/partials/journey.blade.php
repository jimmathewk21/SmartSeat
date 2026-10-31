<section class="panel booking-journey">
    <span class="eyebrow">Train {{ $trip->train->number }} · {{ $trip->train->name }}</span>
    <div class="journey-times">
        <div><strong>{{ $journey['origin']->station->code }}</strong><span>{{ $journey['origin']->station->name }}</span><span>{{ $journey['departure']->format('d-M-Y H:i A') }}</span></div>
        <span aria-hidden="true">──── →</span>
        <div><strong>{{ $journey['destination']->station->code }}</strong><span>{{ $journey['destination']->station->name }}</span><span>{{ $journey['arrival']->format('d-M-Y H:i A') }}</span></div>
    </div>
    <small class="muted">All times are in India time. A reserved seat is occupied for the whole train trip.</small>
</section>
