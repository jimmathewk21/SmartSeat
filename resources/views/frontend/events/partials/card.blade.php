@php
    $startAt = $event->start_at ? \Carbon\Carbon::parse($event->start_at) : null;
    $endAt = $event->end_at ? \Carbon\Carbon::parse($event->end_at) : null;

    $dateLabel = 'TBA';
    if ($startAt && $endAt) {
        $dateLabel = $startAt->isSameDay($endAt)
            ? $startAt->format('j M')
            : $startAt->format('j M') . ' - ' . $endAt->format('j M');
    } elseif ($startAt) {
        $dateLabel = $startAt->format('j M');
    }

    $yearLabel = $startAt ? $startAt->format('Y') : 'TBA';
    $venueText = trim((string) ($event->venue ?? ''));
    $venueLabel = $venueText !== '' ? $venueText : 'Venue TBA';

    $locationLabel = trim((string) ($event->location ?? '')) ?: 'Location TBA';
@endphp
<div class="event-card-visual">
    <img src="{{ $imageSrc }}" alt="{{ $event->title }}" class="event-card-image">
    <div class="event-card-body">
        <h4 class="event-card-title">{{ $event->title }}</h4>
        <div class="event-card-text">{{ $description !== '' ? $description : 'Event description can be managed from the admin panel.' }}</div>
        <div class="event-card-facts">
            <div class="event-card-fact"><span class="event-card-fact-label">Date:</span><span class="event-card-fact-value">{{ $dateLabel }}</span></div>
            <div class="event-card-fact"><span class="event-card-fact-label">Year:</span><span class="event-card-fact-value">{{ $yearLabel }}</span></div>
            <div class="event-card-fact"><span class="event-card-fact-label">Venue:</span><span class="event-card-fact-value">{{ $venueLabel }}</span></div>
            <div class="event-card-fact"><span class="event-card-fact-label">Location:</span><span class="event-card-fact-value">{{ $locationLabel }}</span></div>
        </div>
        @php
            $galleryLink = $galleryHref !== '' ? $galleryHref : route('frontend.events.show', $event->slug);
            $brochureLink = $brochureHref !== '' ? $brochureHref : route('frontend.events.show', $event->slug);
            $galleryExternal = str_starts_with($galleryLink, 'http://') || str_starts_with($galleryLink, 'https://') || str_starts_with($galleryLink, '//');
            $brochureExternal = str_starts_with($brochureLink, 'http://') || str_starts_with($brochureLink, 'https://') || str_starts_with($brochureLink, '//');
        @endphp
        <div class="event-card-actions">
            <a
                href="{{ $galleryLink }}"
                class="event-card-btn"
                @if($galleryExternal) target="_blank" rel="noopener" @endif
            >View Gallery</a>
            <a
                href="{{ $brochureLink }}"
                class="event-card-btn"
                @if($brochureExternal) target="_blank" rel="noopener" @endif
            >View Brochure</a>
        </div>
    </div>
</div>
