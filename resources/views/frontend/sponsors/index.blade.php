<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'Event Sponsors';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $sponsorSettings = $sponsorPageSettings ?? [];
        $rawButtonLink = trim((string) ($sponsorSettings['button_link'] ?? ''));
        $buttonHref = ($rawButtonLink === '' || $rawButtonLink === '#') ? '/become-sponsor' : $rawButtonLink;
        if (!str_starts_with($buttonHref, 'http://') && !str_starts_with($buttonHref, 'https://') && !str_starts_with($buttonHref, '//') && !str_starts_with($buttonHref, '/')) {
            $buttonHref = '/' . ltrim($buttonHref, '/');
        }
    @endphp
    <title>{{ $frontendSeoTitle ?? $pageTitle }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <link id="colors" href="{{ asset('css/colors/scheme-01.css') }}" rel="stylesheet" type="text/css">
    <style>
        html, body { margin: 0 !important; padding: 0 !important; }
        #wrapper { display: flow-root; margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.sponsors-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.sponsors-page > footer { margin-top: 0 !important; }
        body.sponsors-page {
            background: #f8f9fa;
            color: #111;
        }
        body.sponsors-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.sponsors-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.sponsors-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.sponsors-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        body.sponsors-page .inside-page-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        body.sponsors-page .btn-main.btn-line {
            border-width: 1px;
        }
        body.sponsors-page .sponsors-world-section {
            position: relative;
            overflow: hidden;
        }
        body.sponsors-page .sponsors-world-map {
            position: absolute;
            top: 140px;
            left: 50%;
            width: min(1280px, 96vw);
            aspect-ratio: 2.1 / 1;
            height: auto;
            transform: translateX(-50%);
            background-image: url('{{ asset('images/worldmap.png') }}');
            background-repeat: no-repeat;
            background-position: center 28px;
            background-size: contain;
            opacity: 0.40;
            pointer-events: none;
            z-index: 0;
        }
        body.sponsors-page .sponsors-world-section > .container {
            position: relative;
            z-index: 1;
        }
        body.sponsors-page .category-headline-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
        }
        body.sponsors-page .category-headline-wrap::before,
        body.sponsors-page .category-headline-wrap::after {
            content: '';
            flex: 1 1 auto;
            height: 1px;
            background: rgba(0, 0, 0, 0.35);
        }
        body.sponsors-page .category-headline {
            margin: 0;
            text-transform: uppercase;
            font-size: 30px;
            color: #000;
            white-space: nowrap;
        }
        body.sponsors-page .category-subline {
            text-align: center;
            margin-top: -8px;
            margin-bottom: 16px;
            color: #3a72c8;
            font-weight: 700;
            font-size: 16px;
        }
        body.sponsors-page .sponsor-logo-btn {
            width: 100%;
            border: 1px solid #d3d5de;
            border-radius: 16px;
            background: #ffffff;
            padding: 16px 14px 14px;
            text-align: center;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        body.sponsors-page .sponsor-logo-btn:hover {
            border-color: #6f46f5;
            box-shadow: 0 0 0 2px rgba(111, 70, 245, 0.12);
        }
        body.sponsors-page .sponsor-logo {
            width: 100%;
            max-width: 260px;
            height: 140px;
            object-fit: contain;
        }
        body.sponsors-page .sponsor-logo.sponsor-logo-sm {
            max-width: 180px;
            height: 95px;
        }
        body.sponsors-page .sponsor-logo.sponsor-logo-md {
            max-width: 230px;
            height: 125px;
        }
        body.sponsors-page .sponsor-logo.sponsor-logo-lg {
            max-width: 290px;
            height: 170px;
        }
        body.sponsors-page .sponsor-name {
            margin-top: 12px;
            font-size: 20px;
            font-weight: 600;
            color: #111;
            line-height: 1.25;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            width: 100%;
        }
        body.sponsors-page .sponsor-subcategory {
            margin-top: 6px;
            color: #555;
            font-size: 16px;
            line-height: 1.3;
            font-weight: 500;
        }
        body.sponsors-page .sponsor-category-block + .sponsor-category-block {
            margin-top: 44px;
        }
        body.sponsors-page .guidelines-body {
            color: #111;
            font-size: 16px;
            line-height: 1.7;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        body.sponsors-page .modal-content {
            border: 0;
            border-radius: 12px;
            overflow: hidden;
            max-height: calc(100vh - 56px);
        }
        body.sponsors-page .modal-header {
            background: #141a3a;
            color: #fff;
            border-bottom: 0;
            padding: 14px 18px;
        }
        body.sponsors-page .modal-header .modal-title {
            color: #fff !important;
        }
        body.sponsors-page .modal-header .btn-close {
            filter: invert(1);
            opacity: .8;
        }
        body.sponsors-page .modal-body.guidelines-body {
            flex: 1 1 auto;
            background: #f7f8fc;
            padding: 20px;
            max-height: calc(100vh - 136px);
            height: auto;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgba(71, 85, 105, 0.75) rgba(203, 213, 225, 0.55);
        }
        body.sponsors-page .modal-body.guidelines-body::-webkit-scrollbar {
            width: 10px;
        }
        body.sponsors-page .modal-body.guidelines-body::-webkit-scrollbar-track {
            background: rgba(203, 213, 225, 0.55);
            border-radius: 999px;
        }
        body.sponsors-page .modal-body.guidelines-body::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.75);
            border-radius: 999px;
            border: 2px solid rgba(247, 248, 252, 0.95);
        }
        body.sponsors-page .modal-body.guidelines-body::-webkit-scrollbar-thumb:hover {
            background: rgba(51, 65, 85, 0.9);
        }
        body.sponsors-page .modal-body.guidelines-body ul,
        body.sponsors-page .modal-body.guidelines-body ol {
            margin: 0 0 12px;
            padding-left: 1.3rem;
        }
        body.sponsors-page .modal-body.guidelines-body li {
            margin-bottom: 6px;
        }
        body.sponsors-page .modal-body.guidelines-body h1,
        body.sponsors-page .modal-body.guidelines-body h2,
        body.sponsors-page .modal-body.guidelines-body h3,
        body.sponsors-page .modal-body.guidelines-body h4,
        body.sponsors-page .modal-body.guidelines-body h5,
        body.sponsors-page .modal-body.guidelines-body h6 {
            color: #111;
            font-weight: 700;
            line-height: 1.25;
            margin: 0 0 12px;
        }
        body.sponsors-page .modal-body.guidelines-body p {
            margin: 0 0 12px;
            color: #111;
        }
        body.sponsors-page .modal-body.guidelines-body p:last-child,
        body.sponsors-page .modal-body.guidelines-body ul:last-child,
        body.sponsors-page .modal-body.guidelines-body ol:last-child {
            margin-bottom: 0;
        }
        @media (max-width: 768px) {
            body.sponsors-page .sponsors-world-map {
                top: 160px;
                width: 112vw;
                aspect-ratio: 1.9 / 1;
                height: auto;
                opacity: 0.18;
            }
            body.sponsors-page .inside-page-hero-main { font-size: 34px; }
            body.sponsors-page .inside-page-hero-description {
                font-size: 16px;
            }
            body.sponsors-page .modal-dialog {
                margin: 12px;
            }
            body.sponsors-page .modal-content {
                max-height: calc(100vh - 24px);
            }
            body.sponsors-page .modal-body.guidelines-body {
                max-height: calc(100vh - 104px);
            }
            body.sponsors-page .category-headline {
                font-size: 20px;
                white-space: normal;
                text-align: center;
            }
            body.sponsors-page .sponsor-name {
                font-size: 18px;
            }
        }
    </style>
</head>
<body class="sponsors-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80 bg-light sponsors-world-section">
        <div class="sponsors-world-map" aria-hidden="true"></div>
        <div class="container">
            @php
                $sponsorHeroButtons = [
                    [
                        'type' => 'link',
                        'text' => $sponsorSettings['button_text'] ?? 'BECOME A SPONSOR',
                        'class' => 'btn-main text-uppercase',
                        'href' => $buttonHref,
                    ],
                ];
                if (!empty($sponsorSettings['guidelines_button_text'])) {
                    $sponsorHeroButtons[] = [
                        'type' => 'button',
                        'text' => $sponsorSettings['guidelines_button_text'],
                        'class' => 'btn-main btn-line text-uppercase',
                        'toggle' => 'modal',
                        'target' => '#sponsorGuidelinesModal',
                    ];
                }
            @endphp
            @include('frontend.partials.inner-page-hero', [
                'heroLabel' => $sponsorSettings['page_title'] ?? 'SPONSORS + PARTNERS',
                'heroTitle' => $sponsorSettings['subtitle'] ?? 'EVENT SPONSORS',
                'heroSuffix' => $sponsorSettings['subtitle_suffix'] ?? '',
                'heroDescription' => $sponsorSettings['description'] ?? '',
                'heroButtons' => $sponsorHeroButtons,
            ])

            @forelse($categories as $category)
                @php
                    $categoryName = trim((string) $category->name);
                    $isTitleCategory = str_contains(strtolower($categoryName), 'title');
                    $isPrincipalCategory = str_contains(strtolower($categoryName), 'principal');
                    $categoryHeading = $isTitleCategory
                        ? 'TITLE SPONSOR'
                        : ($isPrincipalCategory ? 'PRINCIPAL SPONSORS (MAIN CAT.)' : $categoryName);
                    $categoryHeading = trim((string) preg_replace('/\(\s*exclusive\s*\)/i', '', $categoryHeading));
                @endphp
                <div class="sponsor-category-block">
                    <div class="category-headline-wrap">
                        <h3 class="category-headline">{{ $categoryHeading }}</h3>
                    </div>
                    @if($isPrincipalCategory)
                        <div class="category-subline">Subcategories managed in sponsor detail text (e.g. Lanyard, Trophy, Bag, Pen, Gift).</div>
                    @endif

                    @if($category->sponsors->isEmpty())
                        <div class="text-center text-muted py-3">Awaiting the confirmation/files from the sponsor.</div>
                    @else
                        <div class="row g-3">
                            @foreach($category->sponsors as $sponsor)
                                @php
                                    $logo = trim((string) ($sponsor->logo_url ?? ''));
                                    $isAbsoluteLogo = str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, '//') || str_starts_with($logo, '/');
                                    $logoSrc = $logo ? ($isAbsoluteLogo ? $logo : asset(ltrim($logo, '/'))) : asset('images/logo.webp');
                                    $logoSize = trim((string) ($category->logo_size ?? 'md'));
                                    $shortName = trim((string) ($sponsor->sub_category ?? ''));
                                    $subCategoryText = trim((string) ($sponsor->sub_category_text ?? ''));
                                    $cardLabel = ($sponsor->use_sub_category_text ?? false) && $subCategoryText !== ''
                                        ? $subCategoryText
                                        : ($shortName !== '' ? $shortName : $sponsor->name);
                                    if (!in_array($logoSize, ['sm', 'md', 'lg'], true)) {
                                        $logoSize = 'md';
                                    }
                                @endphp
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <a href="{{ route('frontend.sponsors.show', $sponsor) }}" class="sponsor-logo-btn d-block">
                                        <img src="{{ $logoSrc }}" alt="{{ $sponsor->name }}" class="sponsor-logo sponsor-logo-{{ $logoSize }}">
                                        <div class="sponsor-name" title="{{ $cardLabel }}">{{ $cardLabel }}</div>
                                        @if($isPrincipalCategory && !empty($sponsor->detail))
                                            <div class="sponsor-subcategory">{{ $sponsor->detail }}</div>
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="alert alert-light border">No sponsor categories found.</div>
            @endforelse
        </div>
    </section>
</div>

@include('frontend.partials.footer')

<div class="modal fade" id="sponsorGuidelinesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sponsor Guidelines</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body guidelines-body">
                {!! $sponsorSettings['guidelines_content'] ?? '' !!}
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('sponsorGuidelinesModal');
        const modalBody = modal ? modal.querySelector('.modal-body.guidelines-body') : null;
        let lockedScrollY = 0;

        if (!modal || !modalBody) {
            return;
        }

        modal.addEventListener('show.bs.modal', function () {
            lockedScrollY = window.scrollY || document.documentElement.scrollTop || 0;
            document.body.style.position = 'fixed';
            document.body.style.top = '-' + lockedScrollY + 'px';
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
        });

        modal.addEventListener('hidden.bs.modal', function () {
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';
            document.body.style.overflow = '';
            window.scrollTo(0, lockedScrollY);
        });

        modal.addEventListener('wheel', function (event) {
            const canScroll = modalBody.scrollHeight > modalBody.clientHeight;

            if (!canScroll) {
                return;
            }

            event.preventDefault();
            modalBody.scrollTop += event.deltaY;
        }, { passive: false });
    });
</script>
</body>
</html>
