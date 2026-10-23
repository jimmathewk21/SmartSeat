<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'My Contacts';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $pageSettings = $pageSettings ?? [];
        $countryFlagUrl = function (?string $countryName) use ($countryCodeMap) {
            $countryName = mb_strtolower(trim((string) $countryName));
            $countryCode = trim((string) ($countryCodeMap[$countryName] ?? ''));

            if ($countryCode === '' || strlen($countryCode) !== 2) {
                return null;
            }

            return 'https://flagcdn.com/24x18/' . strtolower($countryCode) . '.png';
        };
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
        body.my-contacts-page {
            background: #f8f9fa;
            color: #111;
        }
        #wrapper { display: flow-root; margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.my-contacts-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.my-contacts-page > footer {
            margin-top: 0 !important;
            position: relative;
            z-index: 2;
        }
        .my-contacts-page-shell {
            position: relative;
            overflow: hidden;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        .my-contacts-map {
            position: absolute;
            top: 140px;
            left: 50%;
            width: min(1411px, 100vw);
            aspect-ratio: 1411 / 702;
            transform: translateX(-50%);
            background-image: url('{{ asset('images/worldmap.png') }}');
            background-repeat: no-repeat;
            background-position: center 28px;
            background-size: contain;
            opacity: 0.40;
            pointer-events: none;
            z-index: 0;
        }
        .my-contacts-page-shell > .container {
            position: relative;
            z-index: 1;
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }
        body.my-contacts-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.my-contacts-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.my-contacts-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.my-contacts-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        body.my-contacts-page .inside-page-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        body.my-contacts-page .spk-btn {
            border: 0;
            cursor: pointer;
        }
        body.my-contacts-page .spk-btn.w-100 {
            width: 100%;
        }
        body.my-contacts-page .speaker-search-wrap {
            background: #f4f6f9;
            border: 1px solid #dce2ed;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            z-index: 1;
            margin-bottom: 28px;
        }
        body.my-contacts-page .guidelines-body {
            color: #111;
            font-size: 16px;
            line-height: 1.7;
        }
        body.my-contacts-page .modal-content {
            border: 0;
            border-radius: 12px;
            overflow: hidden;
        }
        body.my-contacts-page .modal-header {
            background: #141a3a;
            color: #fff;
            border-bottom: 0;
            padding: 14px 18px;
        }
        body.my-contacts-page .modal-header .modal-title {
            color: #fff !important;
        }
        body.my-contacts-page .modal-header .btn-close {
            filter: invert(1);
            opacity: .8;
        }
        body.my-contacts-page .modal-body.guidelines-body {
            background: #f7f8fc;
            padding: 20px;
        }
        body.my-contacts-page .modal-body.guidelines-body ul,
        body.my-contacts-page .modal-body.guidelines-body ol {
            margin-bottom: 0;
            padding-left: 1.2rem;
        }
        body.my-contacts-page .modal-body.guidelines-body h1,
        body.my-contacts-page .modal-body.guidelines-body h2,
        body.my-contacts-page .modal-body.guidelines-body h3,
        body.my-contacts-page .modal-body.guidelines-body h4,
        body.my-contacts-page .modal-body.guidelines-body h5,
        body.my-contacts-page .modal-body.guidelines-body h6 {
            color: #111;
            font-weight: 700;
            line-height: 1.25;
            margin: 0 0 12px;
        }
        body.my-contacts-page .modal-body.guidelines-body p {
            margin: 0 0 12px;
            color: #111;
        }
        body.my-contacts-page .speaker-search-button {
            width: 100%;
            min-height: 52px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #7d52ff, #5c42ef);
            color: #fff;
            font-size: 18px;
            font-weight: 800;
        }
        body.my-contacts-page .speaker-search-button:hover,
        body.my-contacts-page .speaker-search-button:focus {
            background: linear-gradient(135deg, #7d52ff, #5c42ef);
            color: #fff;
            text-decoration: none;
            opacity: 0.95;
        }
        .my-contact-card-grid {
            row-gap: 24px;
        }
        .my-contact-card {
            background: #f1f2f4;
            border: 1px solid #e2e6ee;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.06);
            height: 100%;
        }
        .my-contact-card-image {
            aspect-ratio: 1 / 1;
            background: #f1f2f4;
            padding: 14px 14px 0;
        }
        .my-contact-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            background: #d6d6d6;
        }
        .my-contact-card-body {
            padding: 12px 16px 10px;
            text-align: center;
            background: #f1f2f4;
        }
        .my-contact-card-name {
            margin: 0 0 6px;
            font-size: 16px;
            line-height: 1.2;
            font-weight: 700;
            color: #111;
        }
        .my-contact-card-name-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            margin-left: 6px;
            color: #c08a00;
            vertical-align: baseline;
        }
        .my-contact-card-name-badge svg {
            display: block;
            width: 100%;
            height: 100%;
            fill: currentColor;
        }
        .my-contact-card-line {
            margin: 0 0 4px;
            color: #222;
            font-size: 13px;
            line-height: 1.35;
        }
        .my-contact-card-line--company {
            font-weight: 700;
        }
        .my-contact-card-line--country {
            margin-top: 6px;
        }
        .my-contact-gender-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            margin-right: 6px;
            color: #6f42f5;
            font-size: 13px;
            vertical-align: baseline;
        }
        .my-contact-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0;
            border-top: 1px solid #e2e6ee;
            border-bottom: 1px solid #e2e6ee;
            background: #f1f2f4;
        }
        .my-contact-meta-item {
            padding: 10px 8px;
            text-align: center;
        }
        .my-contact-meta-item + .my-contact-meta-item {
            border-left: 1px solid #e2e6ee;
        }
        .my-contact-meta-label {
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 700;
            color: #333;
            text-transform: uppercase;
        }
        .my-contact-meta-value {
            display: block;
            font-size: 12px;
            color: #222;
            line-height: 1.35;
            word-break: break-word;
        }
        .my-contact-card-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 14px 14px;
            background: #f1f2f4;
        }
        .my-contact-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            font-size: 18px;
            color: #111;
            text-decoration: none;
        }
        .my-contact-link:hover,
        .my-contact-link:focus {
            color: #6f42f5;
            text-decoration: none;
        }
        .my-contact-key-figure {
            background: transparent;
            border: 0;
            padding: 0;
            cursor: pointer;
        }
        .my-contact-key-figure.is-active {
            color: #6f42f5;
        }
        .my-contact-flag {
            width: 28px;
            height: 20px;
            border-radius: 3px;
            object-fit: cover;
            border: 1px solid rgba(17, 24, 39, 0.08);
        }
        .my-contact-empty {
            border: 1px solid #dce2ed;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.88);
            padding: 22px 24px;
            color: #4a5568;
            font-size: 16px;
        }
        .my-contact-pagination {
            margin-top: 26px;
        }
        .my-contact-pagination .pagination {
            justify-content: center;
        }
        @media (max-width: 767.98px) {
            body.my-contacts-page .my-contacts-page-shell {
                padding-top: 90px !important;
            }
            .my-contacts-map {
                top: 160px;
                width: 118vw;
                opacity: 0.18;
            }
            body.my-contacts-page .inside-page-hero-main {
                font-size: 32px;
            }
            body.my-contacts-page .inside-page-hero-description {
                font-size: 15px;
            }
            .my-contact-meta-grid {
                grid-template-columns: 1fr;
            }
            .my-contact-meta-item + .my-contact-meta-item {
                border-left: 0;
                border-top: 1px solid #e1e6ef;
            }
            body.my-contacts-page .inside-page-hero-actions {
                gap: 10px;
            }
            body.my-contacts-page .inside-page-hero-actions .btn-main {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body class="my-contacts-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-0 bg-light my-contacts-page-shell">
        <div class="my-contacts-map"></div>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    @php
                        $formButtonLink = trim((string) ($pageSettings['form_button_link'] ?? route('frontend.my-contacts.create')));
                        $formButtonHref = str_starts_with($formButtonLink, 'http://') || str_starts_with($formButtonLink, 'https://') || str_starts_with($formButtonLink, '//')
                            ? $formButtonLink
                            : url($formButtonLink);
                        $heroButtons = [
                            [
                                'type' => 'button',
                                'text' => $pageSettings['disclaimer_button_text'] ?? 'Disclaimer',
                                'class' => 'btn-main btn-line text-uppercase spk-btn',
                                'toggle' => 'modal',
                                'target' => '#myContactsDisclaimerModal',
                            ],
                            [
                                'type' => 'link',
                                'text' => $pageSettings['form_button_text'] ?? 'List Yourself',
                                'class' => 'btn-main text-uppercase spk-btn',
                                'href' => $formButtonHref,
                            ],
                        ];
                    @endphp
                    <div id="my-contacts-disclaimer">
                        @include('frontend.partials.inner-page-hero', [
                            'heroLabel' => $pageSettings['page_title'] ?? '[World Leaders]',
                            'heroTitle' => $pageSettings['subtitle'] ?? 'IP Professionals',
                            'heroSuffix' => $pageSettings['subtitle_suffix'] ?? 'across world',
                            'heroDescription' => $pageSettings['description'] ?? '',
                            'heroButtons' => $heroButtons,
                        ])
                    </div>

                    <div class="speaker-search-wrap wow fadeInUp" data-wow-delay=".35s">
                        <form method="GET" action="{{ route('frontend.my-contacts.index') }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Select Country</label>
                                    <select class="form-control" name="country">
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country }}" @selected(request('country') === $country)>{{ $country }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Select Category</label>
                                    <select class="form-control" name="category">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Select Area of Expertise</label>
                                    <select class="form-control" name="specialization">
                                        <option value="">Select Area of Expertise</option>
                                        @foreach($specializations as $specialization)
                                            <option value="{{ $specialization }}" @selected(request('specialization') === $specialization)>{{ $specialization }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label">Select Experience</label>
                                    <select class="form-control" name="experience">
                                        <option value="">Select Experience</option>
                                        @foreach($experiences as $experience)
                                            <option value="{{ $experience }}" @selected(request('experience') === $experience)>{{ $experience }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-9 col-md-8">
                                    <label class="form-label">Search</label>
                                    <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="Name, company, country, expertise">
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <button class="speaker-search-button text-uppercase spk-btn" type="submit">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="row my-contact-card-grid">
                        @forelse($contacts as $contact)
                            @php
                                $picture = $contact->picture_url
                                    ? asset(ltrim($contact->picture_url, '/'))
                                    : asset(($contact->gender ?? '') === 'Female' ? 'images/placeholders/contact-female.svg' : 'images/placeholders/contact-male.svg');
                                $gender = strtolower(trim((string) $contact->gender));
                                $genderIcon = $gender === 'female' ? 'fa-venus' : ($gender === 'male' ? 'fa-mars' : 'fa-user');
                                $flagUrl = $countryFlagUrl($contact->country);
                                $keyFigureFilterUrl = request('is_key_figure') === '1'
                                    ? route('frontend.my-contacts.index', array_merge(request()->except(['page', 'is_key_figure'])))
                                    : route('frontend.my-contacts.index', array_merge(request()->except('page'), ['is_key_figure' => '1']));
                                $metaItems = [];

                                if ($contact->show_category) {
                                    $metaItems[] = [
                                        'label' => 'Category',
                                        'value' => $contact->category ?: '-',
                                    ];
                                }

                                if ($contact->show_expertise) {
                                    $metaItems[] = [
                                        'label' => 'Expertise',
                                        'value' => $contact->specialization ?: '-',
                                    ];
                                }

                                $metaItems[] = [
                                    'label' => 'Experience',
                                    'value' => $contact->experience ?: '-',
                                ];
                            @endphp
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="my-contact-card">
                                    <div class="my-contact-card-image">
                                        <img src="{{ $picture }}" alt="{{ $contact->name }}">
                                    </div>
                                    <div class="my-contact-card-body">
                                        <h3 class="my-contact-card-name">{{ $contact->name }}</h3>
                                        <p class="my-contact-card-line">{{ $contact->designation ?: '-' }}</p>
                                        <p class="my-contact-card-line my-contact-card-line--company">{{ $contact->company_name ?: '-' }}</p>
                                        <p class="my-contact-card-line my-contact-card-line--country">
                                            {{ $contact->country ?: '-' }}
                                        </p>
                                    </div>
                                    <div class="my-contact-meta-grid">
                                        @foreach($metaItems as $metaItem)
                                            <div class="my-contact-meta-item">
                                                <span class="my-contact-meta-label">{{ $metaItem['label'] }}</span>
                                                <span class="my-contact-meta-value">{{ $metaItem['value'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="my-contact-card-links">
                                        @if($flagUrl)
                                            <img src="{{ $flagUrl }}" alt="{{ $contact->country }}" class="my-contact-flag">
                                        @endif
                                        @if($contact->show_email && $contact->official_email)
                                            <a href="mailto:{{ $contact->official_email }}" class="my-contact-link" title="Email"><i class="fa fa-envelope"></i></a>
                                        @endif
                                        @if($contact->show_whatsapp && $contact->whatsapp_number)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp_number) }}" target="_blank" rel="noopener" class="my-contact-link" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                        @endif
                                        @if($contact->show_linkedin && $contact->linkedin_profile)
                                            @php
                                                $linkedinHref = $contact->linkedin_profile;
                                                if (!str_starts_with($linkedinHref, 'http://') && !str_starts_with($linkedinHref, 'https://')) {
                                                    $linkedinHref = 'https://www.linkedin.com/in/' . ltrim($linkedinHref, '/');
                                                }
                                            @endphp
                                            <a href="{{ $linkedinHref }}" target="_blank" rel="noopener" class="my-contact-link" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                                        @endif
                                        @if($contact->gender)
                                            <span class="my-contact-link" title="{{ $contact->gender }}"><i class="fa {{ $genderIcon }}"></i></span>
                                        @endif
                                        @if($contact->is_whitelisted)
                                            <button type="button" class="my-contact-link my-contact-key-figure {{ request('is_key_figure') === '1' ? 'is-active' : '' }}" title="Key Figure (Click to filter)" aria-label="Key Figure (Click to filter)" onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ $keyFigureFilterUrl }}';">
                                                <i class="fa fa-crown"></i>
                                            </button>
                                        @endif
                                        @if($contact->show_website && $contact->web_url)
                                            <a href="{{ $contact->web_url }}" target="_blank" rel="noopener" class="my-contact-link" title="Website"><i class="fa fa-globe"></i></a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="my-contact-empty">No contacts available right now.</div>
                            </div>
                        @endforelse
                    </div>

                    @if($contacts->hasPages())
                        <div class="my-contact-pagination">
                            {{ $contacts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="myContactsDisclaimerModal" tabindex="-1" aria-labelledby="myContactsDisclaimerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myContactsDisclaimerModalLabel">{{ $pageSettings['disclaimer_button_text'] ?? 'Disclaimer' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body guidelines-body">
                    {!! $pageSettings['guidelines_content'] ?? '' !!}
                </div>
            </div>
        </div>
    </div>

    @include('frontend.partials.footer')
</div>

<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
</body>
</html>
