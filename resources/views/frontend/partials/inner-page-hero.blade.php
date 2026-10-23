@php
    $heroLabel = trim((string) ($heroLabel ?? ''));
    $heroTitle = trim((string) ($heroTitle ?? ''));
    $heroSuffix = trim((string) ($heroSuffix ?? ''));
    $heroDescription = trim((string) ($heroDescription ?? ''));
    $heroButtons = collect($heroButtons ?? [])->filter(function ($button) {
        return trim((string) ($button['text'] ?? '')) !== '';
    })->values();
@endphp

<div class="inside-page-hero mb-4">
    @if($heroLabel !== '')
        <div class="subtitle wow fadeInUp" data-wow-delay=".0s">{{ $heroLabel }}</div>
    @endif
    <h2 class="inside-page-hero-headline wow fadeInUp" data-wow-delay=".1s">
        <span class="inside-page-hero-main">{{ $heroTitle }}</span>
        @if($heroSuffix !== '')
            <small class="ms-2">/ {{ $heroSuffix }}</small>
        @endif
    </h2>
    @if($heroDescription !== '')
        <p class="inside-page-hero-description wow fadeInUp" data-wow-delay=".2s">{{ $heroDescription }}</p>
    @endif
    @if($heroButtons->isNotEmpty())
        <div class="inside-page-hero-actions wow fadeInUp" data-wow-delay=".3s">
            @foreach($heroButtons as $button)
                @php
                    $buttonType = ($button['type'] ?? 'link') === 'button' ? 'button' : 'link';
                    $buttonClass = trim((string) ($button['class'] ?? 'btn-main text-uppercase'));
                @endphp
                @if($buttonType === 'button')
                    <button
                        type="button"
                        class="{{ $buttonClass }}"
                        @if(!empty($button['target'])) data-bs-target="{{ $button['target'] }}" @endif
                        @if(!empty($button['toggle'])) data-bs-toggle="{{ $button['toggle'] }}" @endif
                    >{{ $button['text'] }}</button>
                @else
                    <a
                        href="{{ $button['href'] ?? '#' }}"
                        class="{{ $buttonClass }}"
                        @if(!empty($button['target'])) target="{{ $button['target'] }}" @endif
                        @if(!empty($button['rel'])) rel="{{ $button['rel'] }}" @endif
                    >{{ $button['text'] }}</a>
                @endif
            @endforeach
        </div>
    @endif
</div>
