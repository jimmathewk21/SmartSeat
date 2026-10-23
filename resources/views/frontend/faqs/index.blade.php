<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'FAQs';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $pageSettings = $pageSettings ?? [];
    @endphp
    <title>{{ $frontendSeoTitle ?? $pageTitle }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="AIvent" name="description">
    <meta content="" name="keywords">
    <meta content="" name="author">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <link id="colors" href="{{ asset('css/colors/scheme-01.css') }}" rel="stylesheet" type="text/css">
    <style>
        html, body { margin: 0 !important; padding: 0 !important; }
        #wrapper { display: flow-root; margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.faq-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.faq-page > footer { margin-top: 0 !important; }
        body.faq-page {
            background: #fff;
            color: #111;
        }
        body.faq-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.faq-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.faq-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.faq-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        body.faq-page .inside-page-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .faq-category-block + .faq-category-block {
            margin-top: 56px;
        }
        .faq-category-block:last-child {
            margin-bottom: 0 !important;
        }
        .faq-category-block:last-child .faq-accordion,
        .faq-category-block:last-child .accordion-section {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        .faq-category-title {
            color: #000;
            margin-bottom: 8px;
            font-size: 22px;
            line-height: 1.15;
        }
        .faq-category-divider {
            width: 62px;
            height: 3px;
            background: #6f46f5;
            border-radius: 999px;
            margin-bottom: 24px;
        }
        .faq-accordion .accordion-section-title {
            color: #111;
            border-color: rgba(0, 0, 0, 0.12);
            padding: 16px 12px 16px 44px;
            font-weight: 600;
        }
        .faq-accordion .accordion-section-content {
            color: #222;
            background: #fff;
            border-color: rgba(0, 0, 0, 0.12);
            margin-top: 10px;
            padding: 16px 18px 18px;
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 14px;
        }
        .faq-accordion .accordion-section-title:before {
            left: 12px;
            background: #6f46f5;
            color: #fff !important;
        }
        .faq-accordion .accordion-section-title.active:before {
            color: #fff !important;
        }
        .faq-accordion .accordion-section-title:last-of-type {
            border-bottom: 0 !important;
        }
        .faq-accordion .accordion-section > .accordion-section-title:nth-last-child(2) {
            border-bottom: 0 !important;
        }
        .faq-accordion .accordion-section > .accordion-section-content:last-child {
            margin-bottom: 0 !important;
        }
        .faq-accordion,
        .faq-accordion .accordion-section {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        .faq-category-block:last-child .accordion-section-title:last-of-type,
        .faq-category-block:last-child .accordion-section-content:last-of-type {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        .faq-sidebar-card {
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 22px;
            border-radius: 8px;
            background: #fff;
        }
        .faq-sidebar-title {
            color: #000;
            font-size: 24px;
            line-height: 1.2;
            margin-bottom: 16px;
        }
        .faq-sidebar-form .form-control {
            border-radius: 0;
            border-color: rgba(0, 0, 0, 0.14);
            margin-bottom: 12px;
        }
        .faq-submit-feedback {
            display: inline-flex;
            align-items: center;
            min-height: 58px;
            padding: 0 18px;
            border-radius: 12px;
            background: #ecfdf3;
            border: 1px solid #7dd3a5;
            color: #166534;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .faq-main-section {
            padding-bottom: 0 !important;
        }
        @media (max-width: 991.98px) {
            .faq-sidebar-wrap {
                margin-top: 32px;
            }
            body.faq-page .inside-page-hero-main { font-size: 34px; }
            .faq-category-title {
                font-size: 20px;
            }
            .faq-sidebar-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body class="faq-page">
<div id="wrapper">
    <div class="float-text show-on-scroll">
        <span><a href="#">Scroll to top</a></span>
    </div>
    <div class="scrollbar-v show-on-scroll"></div>
    <div id="de-loader"></div>

    @include('frontend.partials.header')

    <section class="bg-light text-dark pt-120 pb-80 faq-main-section">
        <div class="container">
            @if($faqCategories->isEmpty())
                <div class="row">
                    <div class="col-lg-12">
                        <p class="mb-0">No FAQs available right now.</p>
                    </div>
                </div>
            @else
                <div class="row g-5 align-items-start">
                    <div class="col-lg-8">
                        @include('frontend.partials.inner-page-hero', [
                            'heroLabel' => $pageSettings['page_title'] ?? 'FAQ + SUPPORT',
                            'heroTitle' => $pageSettings['subtitle'] ?? 'FREQUENTLY ASKED QUESTIONS',
                            'heroSuffix' => $pageSettings['subtitle_suffix'] ?? '',
                            'heroDescription' => $pageSettings['description'] ?? 'Find quick answers by category. If your question is not listed, use the form to contact us.',
                            'heroButtons' => [],
                        ])
                        @foreach($faqCategories as $category)
                            <div class="faq-category-block">
                                <h3 class="faq-category-title">{{ $category->name }} FAQ's</h3>
                                <div class="faq-category-divider"></div>
                                <div class="accordion s2 faq-accordion">
                                    <div class="accordion-section">
                                        @foreach($category->faqs as $faq)
                                            <div class="accordion-section-title" data-tab="#faq-{{ $category->id }}-{{ $faq->id }}">
                                                {{ $faq->question }}
                                            </div>
                                            <div class="accordion-section-content" id="faq-{{ $category->id }}-{{ $faq->id }}">
                                                {{ $faq->answer }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-lg-4 faq-sidebar-wrap">
                        <div class="faq-sidebar-card">
                            <h4 class="faq-sidebar-title">Can't Find Answer? Ask Us</h4>
                            <form class="faq-sidebar-form" method="POST" action="{{ route('frontend.faqs.store') }}">
                                @csrf
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Your Name" required>
                                @error('name')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Your Email" required>
                                @error('email')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                                <textarea name="question" class="form-control @error('question') is-invalid @enderror" rows="5" placeholder="Your Question" required>{{ old('question') }}</textarea>
                                @error('question')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                                <div class="mt-2">
                                    @if(session('faq_question_submitted'))
                                        <div class="faq-submit-feedback">{{ session('faq_question_message', 'Your question has been submitted successfully.') }}</div>
                                    @else
                                        <button type="submit" class="btn-main fx-slide"><span>SEND QUESTION</span></button>
                                    @endif
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

@include('frontend.partials.footer')

<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script src="{{ asset('js/countdown-custom.js') }}"></script>
<script src="{{ asset('js/custom-marquee.js') }}"></script>
</body>
</html>
