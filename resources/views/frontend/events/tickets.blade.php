<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = $event->title . ' - Tickets';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $step = request()->query('step', 'plans');
        $isDetailsStep = $step === 'details';
        $isReviewStep = $step === 'review';
        $isPlanStep = ! $isDetailsStep && ! $isReviewStep;
        $selectedPlan = $event->ticketPlans->firstWhere('id', $selectedPlanId);
        $minTicketQuantity = isset($minTicketQuantity) ? max(1, (int) $minTicketQuantity) : 1;
        $maxTicketQuantity = isset($maxTicketQuantity) ? max($minTicketQuantity, (int) $maxTicketQuantity) : 10;
        $planCaption = trim((string) ($ticketCheckoutSettings['plan_caption'] ?? ''));
        if ($planCaption === '') {
            $planCaption = 'Conference access with curated benefits and member-ready pricing.';
        }
        $ticketHeroLabel = trim((string) ($event->ticket_hero_label ?? ''));
        if ($ticketHeroLabel === '') {
            $ticketHeroLabel = 'Ticket Booking';
        }
        $ticketHeroSuffix = trim((string) ($event->ticket_hero_suffix ?? ''));
        if ($ticketHeroSuffix === '') {
            $ticketHeroSuffix = '/ reserve your seat';
        }
        $ticketHeroDescription = trim((string) ($event->ticket_hero_description ?? ''));
        if ($ticketHeroDescription === '') {
            $ticketHeroDescription = 'Choose the best registration package for this event, review the included benefits, and continue to a secure checkout experience.';
        }
        $eventDateRangeLabel = 'TBA';
        if ($event->start_at) {
            $startDateLabel = \Carbon\Carbon::parse($event->start_at)->format('d-M-Y');
            $eventDateRangeLabel = $startDateLabel;

            if ($event->end_at) {
                $endDateLabel = \Carbon\Carbon::parse($event->end_at)->format('d-M-Y');
                if ($endDateLabel !== $startDateLabel) {
                    $eventDateRangeLabel .= ' to ' . $endDateLabel;
                }
            }
        }
        $detailsStepBaseUrl = route('frontend.events.tickets', ['event' => $event->slug, 'step' => 'details']);
        $reviewStepBaseUrl = route('frontend.events.tickets', ['event' => $event->slug, 'step' => 'review']);
        $backToDetailsUrl = route('frontend.events.tickets', ['event' => $event->slug, 'plan' => $selectedPlanId, 'step' => 'details']);
        $backToPlansUrl = route('frontend.events.tickets', ['event' => $event->slug, 'plan' => $selectedPlanId]);
        $previousStepUrl = $isReviewStep
            ? $backToDetailsUrl
            : ($isDetailsStep ? $backToPlansUrl : route('frontend.events.index'));
    @endphp
    <title>{{ $frontendSeoTitle ?? $pageTitle }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <link id="colors" href="{{ asset('css/colors/scheme-01.css') }}" rel="stylesheet" type="text/css">
    <style>
        html, body { margin: 0 !important; padding: 0 !important; }
        #wrapper { display: flow-root; margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.event-ticket-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.event-ticket-page > footer { margin-top: 0 !important; }
        html, body.event-ticket-page {
            height: auto !important;
            min-height: 100%;
        }
        body.event-ticket-page {
            background: #f8f9fa;
            color: #111;
            overflow-y: auto !important;
        }
        body.event-ticket-page .event-ticket-world-section {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(111, 70, 245, 0.10), transparent 30%),
                linear-gradient(180deg, #f9fbff 0%, #f6f8fc 100%);
        }
        body.event-ticket-page .event-ticket-world-map {
            position: absolute;
            top: 120px;
            left: 50%;
            width: min(1280px, 96vw);
            aspect-ratio: 2.1 / 1;
            transform: translateX(-50%);
            background-image: url('{{ asset('images/worldmap.png') }}');
            background-repeat: no-repeat;
            background-position: center 28px;
            background-size: contain;
            opacity: 0.34;
            pointer-events: none;
            z-index: 0;
        }
        body.event-ticket-page .event-ticket-world-section > .container {
            position: relative;
            z-index: 1;
        }
        .ticket-hero {
            display: flex;
            justify-content: space-between;
            gap: 28px;
            align-items: flex-start;
            margin-bottom: 26px;
            padding: 10px 0 8px;
        }
        .ticket-hero-copy {
            max-width: 920px;
        }
        .ticket-eyebrow {
            display: inline-block;
            margin-bottom: 16px;
            padding: 7px 14px;
            border-radius: 999px;
            background: rgba(59, 114, 200, 0.10);
            color: #3a72c8;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .ticket-hero-title {
            margin: 0 0 14px;
            color: #000;
            font-size: 60px;
            line-height: 1.02;
            font-weight: 700;
            letter-spacing: -0.03em;
        }
        .ticket-hero-title small {
            font-size: 0.52em;
            font-weight: 400;
            color: #000;
        }
        .ticket-hero-description {
            margin: 0 0 18px;
            max-width: 980px;
            color: #273142;
            font-size: 18px;
            line-height: 1.75;
        }
        .ticket-hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }
        .ticket-hero-meta-item {
            min-width: 220px;
            padding: 14px 18px;
            border: 1px solid rgba(111, 70, 245, 0.14);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(8px);
            box-shadow: 0 16px 44px rgba(24, 39, 75, 0.08);
        }
        .ticket-hero-meta-item .label {
            display: block;
            margin-bottom: 6px;
            color: #6f7d92;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .ticket-hero-meta-item strong {
            display: block;
            color: #121826;
            font-size: 17px;
            line-height: 1.45;
            font-weight: 700;
        }
        .ticket-stepper {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 32px;
        }
        .ticket-step {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 210px;
            padding: 14px 18px;
            border: 1px solid #dbe3f2;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 16px 36px rgba(25, 39, 69, 0.06);
        }
        .ticket-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e8edf9;
            color: #43536e;
            font-weight: 700;
        }
        .ticket-step-label {
            display: block;
            color: #162033;
            font-weight: 700;
            line-height: 1.25;
        }
        .ticket-step-note {
            display: block;
            margin-top: 3px;
            color: #6c7588;
            font-size: 13px;
            line-height: 1.35;
        }
        .ticket-step.is-active {
            border-color: rgba(111, 70, 245, 0.22);
            background: linear-gradient(135deg, rgba(111, 70, 245, 0.14), rgba(58, 114, 200, 0.09));
        }
        .ticket-step.is-active .ticket-step-number {
            background: linear-gradient(135deg, #6f46f5, #3a72c8);
            color: #fff;
            box-shadow: 0 10px 20px rgba(111, 70, 245, 0.28);
        }
        .ticket-plans-shell,
        .ticket-content-shell {
            padding: 26px;
            border: 1px solid rgba(215, 222, 237, 0.95);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(10px);
            box-shadow: 0 24px 70px rgba(18, 29, 55, 0.08);
        }
        .ticket-section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
        }
        .ticket-currency-switch {
            display: inline-flex;
            align-items: center;
            padding: 4px;
            border-radius: 999px;
            background: #eef2ff;
            border: 1px solid #d8e1fb;
            gap: 4px;
        }
        .ticket-currency-btn {
            border: none;
            background: transparent;
            color: #52617d;
            border-radius: 999px;
            min-width: 74px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            transition: all .18s ease;
        }
        .ticket-currency-btn.is-active {
            background: linear-gradient(90deg, #6f46f5, #3a72c8);
            color: #fff;
            box-shadow: 0 8px 18px rgba(62, 84, 168, 0.22);
        }
        .ticket-currency-btn:focus-visible {
            outline: 2px solid rgba(58, 114, 200, 0.55);
            outline-offset: 1px;
        }
        .ticket-section-head h3 {
            margin: 0 0 6px;
            color: #0f1728;
            font-size: 34px;
            line-height: 1.05;
        }
        .ticket-section-head p {
            margin: 0;
            color: #607089;
            font-size: 16px;
        }
        .ticket-section-head.is-review-header {
            display: block;
            margin-bottom: 28px;
        }
        .ticket-section-head.is-review-header h3 {
            margin: 0 0 10px;
            font-size: 42px;
            line-height: 1;
        }
        .ticket-section-head.is-review-header p {
            font-size: 17px;
            line-height: 1.5;
            color: #23314d;
        }
        body.event-ticket-page #wrapper {
            height: auto !important;
            min-height: 100vh;
            overflow: visible !important;
        }
        body.event-ticket-page section,
        body.event-ticket-page .card,
        body.event-ticket-page .card-body,
        body.event-ticket-page .row,
        body.event-ticket-page [class*="col-"] {
            overflow: visible !important;
            max-height: none !important;
        }
        .ticket-plan-card {
            --plan-accent: #6f46f5;
            --plan-price-bg: #5b2bc4;
            border: 1px solid #dce4f1;
            border-radius: 24px;
            padding: 24px 22px 22px;
            height: 100%;
            position: relative;
            cursor: pointer;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 255, 0.98));
            box-shadow: 0 18px 44px rgba(18, 29, 55, 0.08);
            overflow: hidden !important;
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }
        .ticket-plan-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 6px;
            background: var(--plan-accent);
        }
        .ticket-plan-card.plan-tone-1 {
            --plan-accent: #6f46f5;
            --plan-price-bg: #5b2bc4;
        }
        .ticket-plan-card.plan-tone-2 {
            --plan-accent: #0065e8;
            --plan-price-bg: #0065e8;
        }
        .ticket-plan-card.plan-tone-3 {
            --plan-accent: #111827;
            --plan-price-bg: #111827;
        }
        .ticket-plan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 26px 60px rgba(18, 29, 55, 0.12);
        }
        .ticket-plan-card.is-selected {
            border-color: var(--plan-accent);
            box-shadow: 0 0 0 3px rgba(111, 70, 245, 0.12), 0 28px 64px rgba(18, 29, 55, 0.14);
        }
        .ticket-plan-radio {
            position: absolute;
            top: 16px;
            right: 18px;
            width: 26px;
            height: 26px;
            pointer-events: none;
            accent-color: #3a72c8;
        }
        .ticket-plan-card:focus-visible {
            outline: 2px solid #6f42f5;
            outline-offset: 2px;
        }
        .ticket-plan-header {
            display: block;
            margin-bottom: 16px;
            padding-top: 24px;
        }
        .ticket-plan-badge {
            position: absolute;
            top: 21px;
            right: 60px;
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 10px;
            background: rgba(111, 70, 245, 0.12);
            color: #6f46f5;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            line-height: 1;
        }
        .ticket-plan-title {
            margin: 0;
            color: #101828;
            font-size: 34px;
            line-height: 1.02;
            font-weight: 700;
        }
        .ticket-plan-caption {
            margin: 6px 0 0;
            color: #5d6b82;
            font-size: 14px;
        }
        .ticket-plan-price-block {
            margin-bottom: 18px;
            padding: 18px;
            padding-top: 54px;
            border-radius: 18px;
            background: var(--plan-price-bg);
            position: relative;
        }
        .price-overlay-discount {
            position: absolute;
            top: 12px;
            right: 12px;
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.88);
            color: var(--plan-accent);
            font-weight: 800;
            font-size: 13px;
            line-height: 1;
        }
        .event-ticket-page .ticket-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 22px;
            border-radius: 8px;
            text-decoration: none;
            line-height: 1;
            border: none;
            white-space: nowrap;
        }
        .event-ticket-page .ticket-btn span {
            white-space: nowrap;
        }
        .event-ticket-page .ticket-btn-outline {
            background: transparent;
            border: 1px solid rgba(111, 66, 245, 0.5);
            color: #6f42f5;
        }
        .event-ticket-page .ticket-btn-outline:hover {
            background: rgba(111, 66, 245, 0.08);
            color: #6f42f5;
        }
        .event-ticket-page .ticket-btn-block {
            width: 100%;
        }
        .ticket-benefits {
            margin: 0;
            padding-left: 18px;
        }
        .ticket-benefits li {
            margin-bottom: 6px;
        }
        .price-line {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .ticket-plans-shell[data-currency="INR"] .price-line[data-currency="USD"],
        .ticket-plans-shell[data-currency="USD"] .price-line[data-currency="INR"],
        .ticket-plans-shell[data-currency="INR"] .price-overlay-discount[data-currency="USD"],
        .ticket-plans-shell[data-currency="USD"] .price-overlay-discount[data-currency="INR"] {
            display: none;
        }
        .price-currency {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }
        .price-mrp {
            text-decoration: line-through;
            color: rgba(255, 255, 255, 0.72);
        }
        .ticket-plan-validity {
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.86);
            font-size: 14px;
            font-weight: 600;
        }
        .ticket-plans-shell.is-review-step .ticket-plan-card {
            padding: 28px 28px 24px;
            cursor: default;
        }
        .ticket-plans-shell.is-review-step .ticket-plan-card:hover {
            transform: none;
        }
        .ticket-plans-shell.is-review-step .ticket-plan-header {
            margin-bottom: 18px;
            padding-top: 0;
        }
        .ticket-plans-shell.is-review-step .ticket-plan-badge {
            top: 19px;
            right: 48px;
        }
        .ticket-plans-shell.is-review-step .ticket-plan-title {
            font-size: 24px;
        }
        .ticket-plan-review-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 24px;
            align-items: start;
            padding-top: 20px;
        }
        .ticket-plan-review-side {
            border: 1px solid #cfd6e4;
            border-radius: 22px;
            padding: 14px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(18, 29, 55, 0.06);
        }
        .ticket-plan-review-side .ticket-plan-price-block {
            margin-bottom: 14px;
            border-radius: 14px;
            background: var(--plan-price-bg);
            padding: 14px 14px 12px;
        }
        .ticket-plan-review-side .price-line {
            gap: 8px;
        }
        .ticket-plan-review-side .price-currency,
        .ticket-plan-review-side .price-mrp,
        .ticket-plan-review-side .ticket-plan-validity {
            color: #fff;
        }
        .ticket-plan-review-side .price-mrp {
            opacity: 0.82;
        }
        .ticket-plan-review-side .price-overlay-discount {
            top: 2px;
            right: 8px;
            padding: 4px 8px;
            font-size: 10px;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
        }
        .ticket-plan-review-summary-title {
            margin: 0 0 10px;
            color: #162033;
            font-size: 16px;
            font-weight: 800;
        }
        .ticket-plan-review-summary .summary-row {
            margin-bottom: 6px;
            font-size: 14px;
        }
        .ticket-plan-review-summary .summary-row strong {
            color: #162033;
            text-align: right;
            padding-left: 12px;
        }
        .summary-box {
            background: linear-gradient(180deg, #f8faff, #f2f6ff);
            border: 1px solid #d9e2f0;
            border-radius: 20px;
            padding: 18px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .summary-row.total {
            font-weight: 700;
            font-size: 18px;
            border-top: 1px solid #d6dceb;
            padding-top: 10px;
        }
        .package-box {
            background: #fff;
            border: 1px solid #dfe5f0;
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 14px;
            box-shadow: 0 16px 36px rgba(18, 29, 55, 0.05);
        }
        .package-box h6 {
            margin-bottom: 12px;
            font-weight: 700;
            color: #1a1f36;
        }
        .ticket-form-title {
            margin: 0;
            color: #0f1728;
            font-size: 34px;
            line-height: 1.04;
        }
        .ticket-form-subtitle {
            margin: 8px 0 0;
            color: #607089;
            font-size: 15px;
            line-height: 1.6;
        }
        .ticket-form-grid .form-control,
        .ticket-form-grid .form-select,
        .delegate-card .form-control,
        .delegate-card .form-select {
            min-height: 54px;
            border-radius: 14px;
            border-color: #d8e1ef;
            background: #fdfefe;
            box-shadow: none;
            color: #122033;
        }
        .ticket-form-grid label,
        .delegate-card label {
            display: block;
            margin-bottom: 9px;
            color: #1e293b;
            font-size: 14px;
            font-weight: 700;
        }
        .ticket-form-grid .form-control:focus,
        .ticket-form-grid .form-select:focus,
        .delegate-card .form-control:focus,
        .delegate-card .form-select:focus {
            border-color: #6f46f5;
            box-shadow: 0 0 0 4px rgba(111, 70, 245, 0.10);
        }
        .ticket-field-card {
            padding: 20px;
            border: 1px solid #dee6f4;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.98));
            box-shadow: 0 14px 34px rgba(18, 29, 55, 0.05);
        }
        .ticket-field-card + .ticket-field-card {
            margin-top: 16px;
        }
        .ticket-field-card-title {
            margin: 0 0 14px;
            color: #111827;
            font-size: 22px;
            font-weight: 700;
        }
        .package-kv {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #edf1f8;
            font-size: 14px;
        }
        .package-kv:last-child {
            border-bottom: none;
        }
        .ticket-availability-list,
        .ticket-not-available-list {
            margin: 0;
            padding-left: 20px;
        }
        .ticket-availability-list li,
        .ticket-not-available-list li {
            margin-bottom: 6px;
        }
        .ticket-not-available-list li {
            color: #5d667a;
        }
        #delegatesContainer {
            overflow: visible;
            padding-bottom: 12px;
        }
        #delegatesContainer .delegate-card {
            scroll-margin-bottom: 80px;
        }
        #delegatesContainer .delegate-card:last-child {
            margin-bottom: 0;
        }
        .delegate-card {
            border: 1px solid #dde5f3 !important;
            border-radius: 20px !important;
            padding: 20px !important;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 248, 255, 0.95));
            box-shadow: 0 16px 36px rgba(18, 29, 55, 0.06);
        }
        .delegate-card h6 {
            margin-bottom: 16px !important;
            color: #111827;
            font-size: 21px;
            font-weight: 700;
        }
        .ticket-sidebar-note {
            border: 1px solid rgba(58, 114, 200, 0.14);
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(58, 114, 200, 0.10), rgba(111, 70, 245, 0.10));
            color: #23324b;
            padding: 16px 18px;
        }
        .promo-action-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .promo-feedback {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
            min-height: 22px;
        }
        .promo-feedback.is-success {
            color: #0a8f62;
        }
        .promo-feedback.is-error {
            color: #d92d20;
        }
        .promo-feedback .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 800;
            flex: 0 0 18px;
        }
        .promo-feedback.is-success .icon {
            background: rgba(10, 143, 98, 0.14);
            color: #0a8f62;
        }
        .promo-feedback.is-error .icon {
            background: rgba(217, 45, 32, 0.14);
            color: #d92d20;
        }
        .review-card {
            border: 1px solid #d7dfef;
            border-radius: 26px;
            background:
                radial-gradient(circle at right top, rgba(58, 114, 200, 0.10), transparent 36%),
                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 24px;
            box-shadow: 0 24px 56px rgba(13, 23, 45, 0.10);
        }
        .review-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            gap: 18px;
            align-items: start;
        }
        .review-main,
        .review-side {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .review-block {
            padding: 20px;
            border: 1px solid #dbe5f5;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 10px 24px rgba(16, 28, 54, 0.06);
        }
        .review-section-title {
            margin: 0 0 14px;
            color: #0f1728;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 0.01em;
        }
        .review-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 12px;
        }
        .review-row {
            border: 1px solid #e7edf8;
            border-radius: 12px;
            padding: 11px 13px;
            background: #fbfdff;
        }
        .review-row .k {
            font-size: 12px;
            color: #6c7588;
            text-transform: uppercase;
            letter-spacing: .2px;
        }
        .review-row .v {
            font-weight: 700;
            margin-top: 4px;
            word-break: break-word;
        }
        .review-row-total {
            background: linear-gradient(130deg, #0f172a, #1f304f);
            border-color: #1f304f;
        }
        .review-row-total .k,
        .review-row-total .v {
            color: #f8fafc;
        }
        .review-row-total .v {
            font-size: 18px;
        }
        .review-delegate-card {
            padding: 16px;
            border: 1px solid #e1e8f5;
            border-radius: 18px;
            background: #fff;
        }
        .review-delegate-card h6 {
            margin-bottom: 12px;
            color: #111827;
            font-size: 18px;
            font-weight: 700;
        }
        .review-pay-card {
            padding: 20px;
            border: 1px solid #cfdbef;
            border-radius: 20px;
            background:
                radial-gradient(circle at 20% 0, rgba(111, 70, 245, 0.09), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #f3f7ff 100%);
            box-shadow: 0 12px 28px rgba(16, 28, 54, 0.07);
        }
        .review-pay-title {
            margin: 0 0 8px;
            font-size: 20px;
            font-weight: 800;
            color: #111827;
        }
        .review-pay-note {
            margin: 0 0 14px;
            color: #4b5565;
            font-size: 14px;
        }
        #payNowBtn.ticket-btn {
            width: 100%;
            justify-content: center;
        }
        #payNowBtn.ticket-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }
        .review-confirmations {
            display: grid;
            gap: 10px;
            margin: 0 0 16px;
        }
        .review-confirmation {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            color: #263349;
            font-size: 14px;
            line-height: 1.45;
        }
        .review-confirmation input {
            margin-top: 4px;
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
            accent-color: #6f46f5;
        }
        @media (max-width: 767px) {
            body.event-ticket-page .event-ticket-world-map {
                top: 140px;
                width: 115vw;
                opacity: 0.16;
            }
            .ticket-hero {
                flex-direction: column;
                align-items: stretch;
            }
            .ticket-hero-title {
                font-size: 40px;
            }
            .ticket-section-head {
                flex-direction: column;
                align-items: flex-start;
            }
            .ticket-plans-shell,
            .ticket-content-shell {
                padding: 18px;
                border-radius: 22px;
            }
            .ticket-plan-title {
                font-size: 28px;
            }
            .ticket-form-title {
                font-size: 28px;
            }
            .ticket-plan-review-layout {
                grid-template-columns: 1fr;
            }
            .review-grid {
                grid-template-columns: 1fr;
            }
            .review-card {
                padding: 18px;
                border-radius: 20px;
            }
            .review-layout {
                grid-template-columns: 1fr;
            }
            .event-ticket-page .ticket-btn {
                width: 100%;
                white-space: normal;
                text-align: center;
                padding: 12px 16px;
            }
            .event-ticket-page .ticket-btn span {
                white-space: normal;
            }
            .summary-row,
            .package-kv {
                gap: 10px;
                align-items: flex-start;
            }
            .summary-row strong,
            .package-kv strong {
                text-align: right;
            }
        }
    </style>
</head>
<body class="event-ticket-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-120 event-ticket-world-section">
        <div class="event-ticket-world-map" aria-hidden="true"></div>
        <div class="container">
            <div class="ticket-hero">
                <div class="ticket-hero-copy">
                    <span class="ticket-eyebrow">{{ $ticketHeroLabel }}</span>
                    <h1 class="ticket-hero-title">
                        {{ $event->title }}
                        <small>{{ $ticketHeroSuffix }}</small>
                    </h1>
                    <p class="ticket-hero-description">{{ $ticketHeroDescription }}</p>
                    <div class="ticket-hero-meta">
                        <div class="ticket-hero-meta-item">
                            <span class="label">Venue</span>
                            <strong>{{ $event->venue ?: 'Venue TBA' }}</strong>
                        </div>
                        <div class="ticket-hero-meta-item">
                            <span class="label">Location</span>
                            <strong>{{ $event->location ?: 'Location TBA' }}</strong>
                        </div>
                        <div class="ticket-hero-meta-item">
                            <span class="label">Date</span>
                            <strong>{{ $eventDateRangeLabel }}</strong>
                        </div>
                    </div>
                </div>
                <div class="text-lg-end">
                    <a href="{{ $previousStepUrl }}" class="ticket-btn ticket-btn-outline">
                        <span>Back to Previous Step</span>
                    </a>
                </div>
            </div>

            <div class="ticket-stepper">
                <div class="ticket-step {{ $isPlanStep ? 'is-active' : '' }}">
                    <span class="ticket-step-number">1</span>
                    <div>
                        <span class="ticket-step-label">Select Your Package</span>
                        <span class="ticket-step-note">Compare plans and choose the right access level.</span>
                    </div>
                </div>
                <div class="ticket-step {{ $isDetailsStep ? 'is-active' : '' }}">
                    <span class="ticket-step-number">2</span>
                    <div>
                        <span class="ticket-step-label">Enter Delegate Details</span>
                        <span class="ticket-step-note">Add billing information and attendee profiles.</span>
                    </div>
                </div>
                <div class="ticket-step {{ $isReviewStep ? 'is-active' : '' }}">
                    <span class="ticket-step-number">3</span>
                    <div>
                        <span class="ticket-step-label">Review & Pay</span>
                        <span class="ticket-step-note">Confirm the package summary before checkout.</span>
                    </div>
                </div>
            </div>

            <div class="ticket-plans-shell mb-4 {{ $isDetailsStep ? 'd-none' : '' }} {{ $isReviewStep ? 'is-review-step' : '' }}" id="ticketPlansShell" data-currency="INR" @if($isDetailsStep) aria-hidden="true" @endif>
                <div class="ticket-section-head {{ $isReviewStep ? 'is-review-header' : '' }}">
                    <div>
                        @if($isReviewStep)
                            <h3>Review & Pay</h3>
                            <p>Please review your billing details, delegate information, and total amount carefully before proceeding with the payment.</p>
                        @else
                            <h3>Registration Plans</h3>
                            <p>Participants are responsible for their own travel, logistics, and visa arrangements, where applicable.</p>
                        @endif
                    </div>
                    @if($isPlanStep)
                        <div class="ticket-currency-switch" role="tablist" aria-label="Select currency">
                            <button type="button" class="ticket-currency-btn is-active" data-currency-toggle="INR" role="tab" aria-selected="true">INR</button>
                            <button type="button" class="ticket-currency-btn" data-currency-toggle="USD" role="tab" aria-selected="false">USD</button>
                        </div>
                    @endif
                </div>
                <div class="row g-4" id="planCards">
                @forelse($event->ticketPlans as $plan)
                    @if(($isDetailsStep || $isReviewStep) && (int) $plan->id !== (int) $selectedPlanId)
                        @continue
                    @endif
                    @php
                        $isSelected = $selectedPlanId === (int) $plan->id;
                        $planDisplayCaption = trim((string) ($plan->caption ?? '')) ?: $planCaption;
                        $benefits = preg_split('/\r\n|\r|\n/', (string) $plan->benefits, -1, PREG_SPLIT_NO_EMPTY);
                        $discInr = $plan->mrp_inr_paise > 0 ? round((($plan->mrp_inr_paise - $plan->sale_inr_paise) * 100) / $plan->mrp_inr_paise) : 0;
                        $discUsd = $plan->mrp_usd_cents > 0 ? round((($plan->mrp_usd_cents - $plan->sale_usd_cents) * 100) / $plan->mrp_usd_cents) : 0;
                    @endphp
                    <div class="{{ $isReviewStep ? 'col-12' : (($isDetailsStep) ? 'col-lg-6' : 'col-lg-4') }}">
                        <div class="ticket-plan-card plan-tone-{{ (($loop->iteration - 1) % 3) + 1 }} {{ $isSelected ? 'is-selected' : '' }}" data-plan-card data-plan-id="{{ $plan->id }}">
                            <input class="ticket-plan-radio" type="radio" name="selected_plan" value="{{ $plan->id }}" @checked($isSelected) @disabled(! $plan->is_active)>
                            @if($isReviewStep)
                                <div class="ticket-plan-review-layout">
                                    <div class="ticket-plan-review-main">
                                        <div class="ticket-plan-header">
                                            <div>
                                                @if($isSelected)
                                                    <span class="ticket-plan-badge">Selected Plan</span>
                                                @endif
                                                <h4 class="ticket-plan-title">{{ $plan->name }} Plan</h4>
                                            </div>
                                        </div>
                                        <ul class="ticket-benefits">
                                            @foreach($benefits as $benefit)
                                                <li>{{ $benefit }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="ticket-plan-review-side">
                                        <div class="ticket-plan-price-block">
                                            <span class="price-overlay-discount" data-currency="INR">{{ max(0,$discInr) }}% OFF</span>
                                            <span class="price-overlay-discount" data-currency="USD">{{ max(0,$discUsd) }}% OFF</span>
                                            <div class="price-line" data-currency="INR"><span class="price-currency">INR {{ number_format($plan->sale_inr_paise/100,2) }}</span><span class="price-mrp">INR {{ number_format($plan->mrp_inr_paise/100,2) }}</span></div>
                                            <div class="price-line" data-currency="USD"><span class="price-currency">USD {{ number_format($plan->sale_usd_cents/100,2) }}</span><span class="price-mrp">USD {{ number_format($plan->mrp_usd_cents/100,2) }}</span></div>
                                            @if($plan->valid_till_text)
                                                <div class="ticket-plan-validity">{{ $plan->valid_till_text }}</div>
                                            @endif
                                        </div>
                                        <div class="ticket-plan-review-summary">
                                            <h6 class="ticket-plan-review-summary-title">Price Summary</h6>
                                            <div class="summary-row"><span>Ticket Name</span><strong id="rvTicketName">-</strong></div>
                                            <div class="summary-row"><span>Currency</span><strong id="rvCurrency">-</strong></div>
                                            <div class="summary-row"><span>Subtotal</span><strong id="rvSubtotal">-</strong></div>
                                            <div class="summary-row"><span>Promo Discount</span><strong id="rvPromo">-</strong></div>
                                            <div class="summary-row"><span>Gateway Charge</span><strong id="rvGateway">-</strong></div>
                                            <div class="summary-row total"><span>Total</span><strong id="rvTotal">-</strong></div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="ticket-plan-header">
                                    <div>
                                        @if($isSelected)
                                            <span class="ticket-plan-badge">Selected Plan</span>
                                        @endif
                                        <h4 class="ticket-plan-title">{{ $plan->name }}</h4>
                                        <p class="ticket-plan-caption">{{ $planDisplayCaption }}</p>
                                    </div>
                                </div>
                                <div class="ticket-plan-price-block">
                                    <span class="price-overlay-discount" data-currency="INR">{{ max(0,$discInr) }}% OFF</span>
                                    <span class="price-overlay-discount" data-currency="USD">{{ max(0,$discUsd) }}% OFF</span>
                                    <div class="price-line" data-currency="INR"><span class="price-currency">INR {{ number_format($plan->sale_inr_paise/100,2) }}</span><span class="price-mrp">INR {{ number_format($plan->mrp_inr_paise/100,2) }}</span></div>
                                    <div class="price-line" data-currency="USD"><span class="price-currency">USD {{ number_format($plan->sale_usd_cents/100,2) }}</span><span class="price-mrp">USD {{ number_format($plan->mrp_usd_cents/100,2) }}</span></div>
                                @if($plan->valid_till_text)
                                    <div class="ticket-plan-validity">{{ $plan->valid_till_text }}</div>
                                @endif
                                </div>
                                <ul class="ticket-benefits">
                                    @foreach($benefits as $benefit)
                                        <li>{{ $benefit }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12"><div class="alert alert-light border">No ticket plans configured for this event yet.</div></div>
                @endforelse
                </div>
            </div>

            @if($event->ticketPlans->count() && $isPlanStep)
                <div class="d-flex justify-content-end mb-4 flex-column flex-sm-row">
                    <a href="{{ route('frontend.events.tickets', ['event' => $event->slug, 'plan' => $selectedPlanId, 'step' => 'details']) }}"
                       class="btn-main fx-slide ticket-btn"
                       id="nextStepBtn">
                        <span>Next: Enter Details</span>
                    </a>
                </div>
            @endif

            @if($event->ticketPlans->count() && $isDetailsStep)
                @auth
                <div class="card ticket-content-shell">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-column flex-md-row gap-2">
                            <div>
                                <h4 class="ticket-form-title">Ticket Registration Form</h4>
                                <p class="ticket-form-subtitle">Add billing information, delegate details, and promo data before moving to the review step.</p>
                            </div>
                            <a href="{{ route('frontend.events.tickets', ['event' => $event->slug, 'plan' => $selectedPlanId]) }}"
                               class="ticket-btn ticket-btn-outline"><span>Back to Ticket Selection</span></a>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="ticket-field-card">
                                    <h5 class="ticket-field-card-title">Billing Details</h5>
                                    <div class="row g-3 ticket-form-grid">
                                        <div class="col-12">
                                            <label class="d-block mb-2">Currency</label>
                                            <div class="ticket-currency-switch" role="tablist" aria-label="Select currency for billing">
                                                <button type="button" class="ticket-currency-btn" data-currency-toggle="INR" role="tab" aria-selected="false">INR</button>
                                                <button type="button" class="ticket-currency-btn" data-currency-toggle="USD" role="tab" aria-selected="false">USD</button>
                                            </div>
                                            <small class="form-text text-muted d-block mt-2">INR allows India only. USD allows all countries except India.</small>
                                        </div>
                                        <div class="col-md-6"><label>Billing/Company Name <span class="text-danger">*</span></label><input class="form-control" id="billingCompanyName" required></div>
                                        <div class="col-md-6"><label>Billing Address (line 1) <span class="text-danger">*</span></label><input class="form-control" id="billingAddress1" required></div>
                                        <div class="col-md-6"><label>Billing Address (line 2)</label><input class="form-control" id="billingAddress2"></div>
                                        <div class="col-md-6"><label>Billing Country <span class="text-danger">*</span></label>
                                            <select class="form-select" id="billingCountry" required>
                                                @foreach($countries as $country)
                                                    @php $code = strtoupper((string)$country->short_code); @endphp
                                                    <option value="{{ $code }}" @selected($code==='IN')>{{ $country->name }} ({{ $code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6" id="gstinFieldWrap"><label>GSTIN</label><input class="form-control" id="gstin" placeholder="Enter GSTIN"></div>
                                        <div class="col-md-6"><label>Number of Tickets/Registrations <span class="text-danger">*</span></label><input type="number" class="form-control" id="ticketQty" min="{{ $minTicketQuantity }}" max="{{ $maxTicketQuantity }}" value="{{ $minTicketQuantity }}" required></div>
                                        <div class="col-md-6"><label>Promo Code (6 digit)</label><input class="form-control" id="promoCode" maxlength="6"></div>
                                        <div class="col-md-6 d-flex align-items-end">
                                            <div class="promo-action-wrap">
                                                <button class="ticket-btn ticket-btn-outline" id="applyPromoBtn" type="button">
                                                    <span>Apply Promo</span>
                                                </button>
                                                <span id="promoFeedback" class="promo-feedback" aria-live="polite"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ticket-field-card">
                                    <h5 class="ticket-field-card-title">Delegate Details</h5>
                                    <div id="delegatesContainer"></div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" id="nextReviewBtn" class="btn-main fx-slide ticket-btn">
                                        <span>Next: Review & Pay</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <h4 class="ticket-form-title mb-3">Selected Package</h4>
                                <div class="package-box">
                                    <h6>Ticket Details</h6>
                                    <div class="package-kv"><span>Ticket Name</span><strong id="packageTicketName">-</strong></div>
                                    <div class="package-kv"><span id="packageTicketPriceLabel">Ticket Price (INR)</span><strong id="packageTicketPriceValue">-</strong></div>
                                    <div class="package-kv"><span>Total Tickets</span><strong id="packageTotalTickets">1</strong></div>
                                    <div class="package-kv"><span>Valid Till</span><strong id="packageValidTill">-</strong></div>
                                </div>
                                <div class="package-box">
                                    <h6>What is available</h6>
                                    <ul class="ticket-availability-list" id="packageAvailableList">
                                        <li>Select a ticket plan to view details.</li>
                                    </ul>
                                </div>
                                <div class="package-box">
                                    <h6>What is not available</h6>
                                    <ul class="ticket-not-available-list">
                                        <li>Registration transfer is not available.</li>
                                        <li>Evening reception / dinner access is not included.</li>
                                    </ul>
                                </div>
                                <div class="summary-box" id="summaryBox">
                                    <h6>Price Summary</h6>
                                    <div class="summary-row"><span>Ticket Name</span><strong id="summaryPlanName">-</strong></div>
                                    <div class="summary-row"><span>Currency</span><strong id="summaryCurrency">INR</strong></div>
                                    <div class="summary-row"><span>Subtotal</span><strong id="summarySubtotal">INR 0.00</strong></div>
                                    <div class="summary-row"><span>Promo Discount</span><strong id="summaryPromo">- INR 0.00</strong></div>
                                    <div class="summary-row"><span>Gateway Charge</span><strong id="summaryGateway">INR 0.00</strong></div>
                                    <div class="summary-row total"><span>Total</span><strong id="summaryTotal">INR 0.00</strong></div>
                                </div>
                                <div class="mt-3" id="checkoutMessage"></div>
                                <div class="ticket-sidebar-note mt-3 mb-0">Complete the required billing and delegate fields, then continue to <strong>Next: Review & Pay</strong>.</div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                    <a class="btn-main fx-slide ticket-btn" href="{{ route('login') }}"><span>Sign In to Continue</span></a>
                @endauth
            @endif

            @if($event->ticketPlans->count() && $isReviewStep)
                @auth
                <div class="review-card mt-4 ticket-content-shell" id="reviewPageSection">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-column flex-md-row gap-2">
                        <div>
                            <h4 class="ticket-form-title">Review Details</h4>
                            <p class="ticket-form-subtitle">Verify billing data, delegates, and final totals before opening Razorpay.</p>
                        </div>
                        <a href="{{ route('frontend.events.tickets', ['event' => $event->slug, 'plan' => $selectedPlanId, 'step' => 'details']) }}"
                           class="ticket-btn ticket-btn-outline"><span>Back to Details</span></a>
                    </div>
                    <div class="review-layout">
                        <div class="review-main">
                            <div class="review-block">
                                <h6 class="review-section-title">Billing Information</h6>
                                <div class="review-grid" id="reviewBilling"></div>
                            </div>
                            <div class="review-block">
                                <h6 class="review-section-title">Delegate Information</h6>
                                <div id="reviewDelegates"></div>
                            </div>
                        </div>
                        <div class="review-side">
                            <div class="review-pay-card">
                                <h6 class="review-pay-title">Ready To Complete Payment</h6>
                                <p class="review-pay-note">Please verify all details once before proceeding to Razorpay.</p>
                                <div id="reviewMessage" class="mb-3"></div>
                                <div class="review-confirmations">
                                    <label class="review-confirmation">
                                        <input type="checkbox" id="confirmDetails" required>
                                        <span>I confirm that all the information provided is accurate and complete. I have read and agree to the Terms & Conditions, Rules & Regulations, Code of Conduct, and Privacy Policy.</span>
                                    </label>
                                    <label class="review-confirmation">
                                        <input type="checkbox" id="confirmPaymentTerms" required>
                                        <span>I acknowledge that the registration fee is non-refundable and does not guarantee participation until confirmed by the organiser with an invoice. I also accept that the organiser is not liable for any loss due to event changes, postponement, cancellation, or speaker/participant withdrawal.</span>
                                    </label>
                                </div>
                                <button type="button" id="payNowBtn" class="btn-main fx-slide ticket-btn" disabled>
                                    <span>Pay with Razorpay</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                    <a class="btn-main fx-slide ticket-btn" href="{{ route('login') }}"><span>Sign In to Continue</span></a>
                @endauth
            @endif
        </div>
    </section>
</div>

@include('frontend.partials.footer')
<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script>
(function () {
    const cards = Array.from(document.querySelectorAll('[data-plan-card]'));
    if (!cards.length) return;

    const nextStepBtn = document.getElementById('nextStepBtn');
    const detailsBaseUrl = @json($detailsStepBaseUrl);
    const currencyButtons = Array.from(document.querySelectorAll('[data-currency-toggle]'));
    const plansShell = document.getElementById('ticketPlansShell');
    const currencyStorageKey = 'ticket_currency_{{ $event->id }}';

    function setCurrency(currency) {
        if (!plansShell) return;
        const normalized = currency === 'USD' ? 'USD' : 'INR';
        plansShell.setAttribute('data-currency', normalized);
        currencyButtons.forEach((button) => {
            const active = button.getAttribute('data-currency-toggle') === normalized;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        try {
            sessionStorage.setItem(currencyStorageKey, normalized);
        } catch (e) {
            // Storage is optional; ignore failures in private browsing contexts.
        }
    }

    function getCheckedCard() {
        const checked = document.querySelector('input[name="selected_plan"]:checked');
        return checked ? checked.closest('[data-plan-card]') : null;
    }

    function setSelection(card) {
        if (!card) return;
        const radio = card.querySelector('input[name="selected_plan"]');
        if (!radio || radio.disabled) return;

        cards.forEach((c) => {
            const input = c.querySelector('input[name="selected_plan"]');
            let badge = c.querySelector('.ticket-plan-badge');
            const active = c === card;
            c.classList.toggle('is-selected', active);
            c.setAttribute('aria-checked', active ? 'true' : 'false');
            c.tabIndex = active ? 0 : -1;
            if (input) input.checked = active;
            if (active && !badge) {
                badge = document.createElement('span');
                badge.className = 'ticket-plan-badge';
                badge.textContent = 'Selected Plan';
                const badgeHost = c.querySelector('.ticket-plan-header > div') || c.querySelector('.ticket-plan-header');
                if (badgeHost) {
                    badgeHost.prepend(badge);
                }
            } else if (!active && badge) {
                badge.remove();
            }
        });

        if (nextStepBtn) {
            const url = new URL(detailsBaseUrl, window.location.origin);
            url.searchParams.set('plan', String(radio.value));
            nextStepBtn.setAttribute('href', url.pathname + url.search);
        }

        window.dispatchEvent(new CustomEvent('ticket-plan-selected'));
    }

    cards.forEach((card) => {
        card.setAttribute('role', 'radio');
        card.setAttribute('aria-checked', card.classList.contains('is-selected') ? 'true' : 'false');
        card.tabIndex = card.classList.contains('is-selected') ? 0 : -1;

        card.addEventListener('click', function () {
            setSelection(card);
        });

        card.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                setSelection(card);
            }
        });
    });

    const initial = getCheckedCard() || cards[0];
    if (initial) setSelection(initial);

    if (currencyButtons.length && plansShell) {
        currencyButtons.forEach((button) => {
            button.addEventListener('click', function () {
                setCurrency(button.getAttribute('data-currency-toggle') || 'INR');
            });
        });

        let initialCurrency = 'INR';
        try {
            initialCurrency = sessionStorage.getItem(currencyStorageKey) || 'INR';
        } catch (e) {
            initialCurrency = 'INR';
        }
        setCurrency(initialCurrency);
    }
})();
</script>
@auth
@if($isDetailsStep)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    const cards = Array.from(document.querySelectorAll('[data-plan-card]'));
    if (!cards.length) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const minTicketQuantity = {{ $minTicketQuantity }};
    const maxTicketQuantity = {{ $maxTicketQuantity }};
    const qtyInput = document.getElementById('ticketQty');
    const billingCountry = document.getElementById('billingCountry');
    const promoInput = document.getElementById('promoCode');
    const promoFeedback = document.getElementById('promoFeedback');
    const delegatesContainer = document.getElementById('delegatesContainer');
    const messageBox = document.getElementById('checkoutMessage');
    const nextReviewBtn = document.getElementById('nextReviewBtn');
    const reviewUrlBase = @json($reviewStepBaseUrl);
    const reviewStorageKey = 'ticket_review_{{ $event->id }}';
    const delegateCountries = @json(
        $countries->map(function ($country) {
            return [
                'name' => $country->name,
                'code' => strtoupper((string) $country->short_code),
            ];
        })->values()
    );

    function selectedPlanCard() {
        const checked = document.querySelector('input[name="selected_plan"]:checked');
        return checked ? checked.closest('[data-plan-card]') : null;
    }

    function activeCurrency() {
        const plansShell = document.getElementById('ticketPlansShell');
        const selected = plansShell ? String(plansShell.getAttribute('data-currency') || 'INR').toUpperCase() : 'INR';
        return selected === 'USD' ? 'USD' : 'INR';
    }

    function resetSummaryByCurrency() {
        const currency = activeCurrency();
        document.getElementById('summaryCurrency').textContent = currency;
        document.getElementById('summarySubtotal').textContent = formatMinor(0, currency);
        document.getElementById('summaryPromo').textContent = '- ' + formatMinor(0, currency);
        document.getElementById('summaryGateway').textContent = formatMinor(0, currency);
        document.getElementById('summaryTotal').textContent = formatMinor(0, currency);
    }

    function syncBillingCountryAvailability() {
        if (!billingCountry) return;

        const currency = activeCurrency();
        const isInr = currency === 'INR';
        const options = Array.from(billingCountry.options || []);

        options.forEach(function (option) {
            const code = String(option.value || '').toUpperCase();
            option.disabled = isInr ? code !== 'IN' : code === 'IN';
        });

        if (isInr) {
            billingCountry.value = 'IN';
            clearFieldError(billingCountry);
            return;
        }

        const selectedCode = String(billingCountry.value || '').toUpperCase();
        if (selectedCode === 'IN' || !selectedCode) {
            const firstAllowed = options.find(function (option) {
                return !option.disabled;
            });
            if (firstAllowed) {
                billingCountry.value = firstAllowed.value;
            }
        }
        clearFieldError(billingCountry);
    }

    function syncGstinVisibility() {
        const gstinFieldWrap = document.getElementById('gstinFieldWrap');
        const gstinInput = document.getElementById('gstin');
        if (!gstinFieldWrap || !gstinInput) return;

        const isInr = activeCurrency() === 'INR';
        gstinFieldWrap.classList.toggle('opacity-50', !isInr);
        gstinInput.disabled = !isInr;
        gstinInput.readOnly = !isInr;

        if (!isInr) {
            gstinInput.value = 'GST not applicable for USD billing';
            clearFieldError(gstinInput);
        } else if (gstinInput.value === 'GST not applicable for USD billing') {
            gstinInput.value = '';
        }
    }

    function selectedPlanId() {
        const checked = document.querySelector('input[name="selected_plan"]:checked');
        return checked ? Number(checked.value) : 0;
    }

    function setMessage(type, text) {
        if (!messageBox) return;
        messageBox.innerHTML = '<div class="alert alert-' + type + ' mb-0">' + text + '</div>';
    }

    function formatMinor(minor, currency) {
        const value = Number(minor || 0) / 100;
        return (currency === 'USD' ? 'USD ' : 'INR ') + value.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function normalizeQuantity() {
        const rawValue = Number(qtyInput.value || minTicketQuantity);
        const normalized = Math.max(minTicketQuantity, Math.min(maxTicketQuantity, Number.isFinite(rawValue) ? rawValue : minTicketQuantity));
        qtyInput.value = normalized;
        return normalized;
    }

    function delegateCountryOptionsHtml() {
        const options = ['<option value="">Select Country</option>'];
        delegateCountries.forEach(function (country) {
            options.push('<option value="' + country.name + '">' + country.name + ' (' + country.code + ')</option>');
        });
        return options.join('');
    }

    function clearFieldError(field) {
        if (!field) return;
        field.classList.remove('is-invalid');
        const parent = field.parentElement;
        if (!parent) return;
        const existing = parent.querySelector('.ticket-field-error');
        if (existing) existing.remove();
    }

    function setFieldError(field, message) {
        if (!field || !message) return;
        clearFieldError(field);
        field.classList.add('is-invalid');
        const parent = field.parentElement;
        if (!parent) return;
        const error = document.createElement('div');
        error.className = 'invalid-feedback d-block ticket-field-error';
        error.textContent = message;
        parent.appendChild(error);
    }

    function clearAllFieldErrors() {
        document.querySelectorAll('.ticket-field-error').forEach(function (el) { el.remove(); });
        document.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
    }

    function firstValidationMessage(errors) {
        if (!errors) return '';
        if (errors._global) return errors._global;
        const fieldKeys = Object.keys(errors.fields || {});
        if (fieldKeys.length) return errors.fields[fieldKeys[0]];
        const delegateErrors = errors.delegates || [];
        for (let i = 0; i < delegateErrors.length; i++) {
            const row = delegateErrors[i];
            if (!row) continue;
            const keys = Object.keys(row);
            if (keys.length) return row[keys[0]];
        }
        return '';
    }

    function applyValidationErrors(errors) {
        clearAllFieldErrors();
        if (!errors) return;

        const fields = errors.fields || {};
        if (fields.billingCompanyName) setFieldError(document.getElementById('billingCompanyName'), fields.billingCompanyName);
        if (fields.billingAddress1) setFieldError(document.getElementById('billingAddress1'), fields.billingAddress1);
        if (fields.billingCountry) setFieldError(document.getElementById('billingCountry'), fields.billingCountry);
        if (fields.ticketQty) setFieldError(document.getElementById('ticketQty'), fields.ticketQty);

        const rows = Array.from(document.querySelectorAll('.delegate-card'));
        const delegateErrors = errors.delegates || [];
        rows.forEach(function (row, index) {
            const rowErrors = delegateErrors[index] || {};
            if (rowErrors.delegate_name) setFieldError(row.querySelector('.delegate-name'), rowErrors.delegate_name);
            if (rowErrors.company_name) setFieldError(row.querySelector('.delegate-company'), rowErrors.company_name);
            if (rowErrors.country) setFieldError(row.querySelector('.delegate-country'), rowErrors.country);
            if (rowErrors.email) setFieldError(row.querySelector('.delegate-email'), rowErrors.email);
            if (rowErrors.mobile) setFieldError(row.querySelector('.delegate-mobile'), rowErrors.mobile);
        });
    }

    function setPromoFeedback(state, text) {
        if (!promoFeedback) return;
        promoFeedback.className = 'promo-feedback' + (state ? (' is-' + state) : '');
        if (!text) {
            promoFeedback.innerHTML = '';
            return;
        }

        if (state === 'success') {
            promoFeedback.innerHTML = '<span class="icon">&#10003;</span><span>' + text + '</span>';
            return;
        }

        if (state === 'error') {
            promoFeedback.innerHTML = '<span class="icon">&#10005;</span><span>' + text + '</span>';
            return;
        }

        promoFeedback.textContent = text;
    }

    function refreshPackageDetailsPanel() {
        const card = selectedPlanCard();
        const qty = normalizeQuantity();
        document.getElementById('packageTotalTickets').textContent = String(qty);

        if (!card) {
            document.getElementById('packageTicketName').textContent = '-';
            document.getElementById('packageTicketPriceLabel').textContent = 'Ticket Price (' + activeCurrency() + ')';
            document.getElementById('packageTicketPriceValue').textContent = '-';
            document.getElementById('packageValidTill').textContent = '-';
            document.getElementById('packageAvailableList').innerHTML = '<li>Select a ticket plan to view details.</li>';
            return;
        }

        const ticketName = card.querySelector('h4') ? card.querySelector('h4').textContent.trim() : '-';
        const currency = activeCurrency();
        const currencyPriceNode = card.querySelector('.price-line[data-currency="' + currency + '"] .price-currency');
        const validTill = card.querySelector('.text-muted') ? card.querySelector('.text-muted').textContent.trim() : '-';

        document.getElementById('packageTicketName').textContent = ticketName;
        let totalTicketPrice = '-';
        if (currencyPriceNode) {
            const unitPriceText = currencyPriceNode.textContent.trim();
            const unitPrice = Number(unitPriceText.replace(/^[A-Z]{3}\s*/i, '').replace(/,/g, ''));
            if (Number.isFinite(unitPrice)) {
                totalTicketPrice = currency + ' ' + (unitPrice * qty).toFixed(2);
            } else {
                totalTicketPrice = unitPriceText;
            }
        }

        document.getElementById('packageTicketPriceLabel').textContent = 'Total Ticket Price (' + currency + ')';
        document.getElementById('packageTicketPriceValue').textContent = totalTicketPrice;
        document.getElementById('packageValidTill').textContent = validTill;

        const benefitItems = Array.from(card.querySelectorAll('.ticket-benefits li'))
            .map((li) => li.textContent.trim())
            .filter(Boolean);

        document.getElementById('packageAvailableList').innerHTML = benefitItems.length
            ? benefitItems.map((item) => '<li>' + item + '</li>').join('')
            : '<li>No additional benefits listed.</li>';
    }

        function generateDelegates() {
            const qty = Math.max(minTicketQuantity, Math.min(maxTicketQuantity, Number(qtyInput.value || minTicketQuantity)));
            qtyInput.value = qty;
            delegatesContainer.innerHTML = '';
            for (let i = 0; i < qty; i++) {
                const html = `
                <div class="border rounded p-3 mb-3 delegate-card" data-index="${i}">
                    <h6 class="mb-3">Delegate ${i + 1}</h6>
                    <div class="row g-2">
                        <div class="col-md-6"><label>Delegate Name <span class="text-danger">*</span></label><input class="form-control delegate-name" placeholder="Delegate Name" required></div>
                        <div class="col-md-6"><label>Designation/Job Title</label><input class="form-control delegate-designation" placeholder="Designation/Job Title"></div>
                        <div class="col-md-6"><label>Company Name <span class="text-danger">*</span></label><input class="form-control delegate-company" placeholder="Company Name" required></div>
                        <div class="col-md-6"><label>Country <span class="text-danger">*</span></label><select class="form-select delegate-country" required>${delegateCountryOptionsHtml()}</select></div>
                        <div class="col-md-6"><label>Email Address <span class="text-danger">*</span></label><input type="email" class="form-control delegate-email" placeholder="Email Address" required></div>
                        <div class="col-md-6"><label>Mobile Number <span class="text-danger">*</span></label><input class="form-control delegate-mobile" placeholder="Mobile Number" required></div>
                    </div>
                </div>`;
                delegatesContainer.insertAdjacentHTML('beforeend', html);
            }
        }

    function collectDelegates() {
        const rows = Array.from(document.querySelectorAll('.delegate-card'));
        return rows.map((row) => ({
            delegate_name: row.querySelector('.delegate-name').value.trim(),
            designation: row.querySelector('.delegate-designation').value.trim(),
            company_name: row.querySelector('.delegate-company').value.trim(),
            country: row.querySelector('.delegate-country').value.trim(),
            email: row.querySelector('.delegate-email').value.trim(),
            mobile: row.querySelector('.delegate-mobile').value.trim(),
        }));
    }

    function restoreReviewDraft() {
        const storedRaw = sessionStorage.getItem(reviewStorageKey);
        if (!storedRaw) {
            generateDelegates();
            return false;
        }

        try {
            const stored = JSON.parse(storedRaw);
            const payload = stored && stored.payload ? stored.payload : null;
            if (!payload || typeof payload !== 'object') {
                generateDelegates();
                return false;
            }

            const preferredCurrency = String(payload.preferred_currency || '').toUpperCase();
            if (preferredCurrency === 'USD' || preferredCurrency === 'INR') {
                setCurrency(preferredCurrency);
            }

            if (document.getElementById('billingCompanyName')) document.getElementById('billingCompanyName').value = payload.billing_company_name || '';
            if (document.getElementById('billingAddress1')) document.getElementById('billingAddress1').value = payload.billing_address_line_1 || '';
            if (document.getElementById('billingAddress2')) document.getElementById('billingAddress2').value = payload.billing_address_line_2 || '';
            if (document.getElementById('gstin')) document.getElementById('gstin').value = payload.gstin || '';
            if (document.getElementById('promoCode')) document.getElementById('promoCode').value = payload.promo_code || '';
            if (billingCountry) billingCountry.value = payload.billing_country_code || billingCountry.value || 'IN';

            const quantity = Math.max(
                minTicketQuantity,
                Math.min(maxTicketQuantity, Number(payload.quantity || minTicketQuantity))
            );
            qtyInput.value = quantity;
            generateDelegates();

            const delegates = Array.isArray(payload.delegates) ? payload.delegates : [];
            Array.from(document.querySelectorAll('.delegate-card')).forEach(function (row, index) {
                const delegate = delegates[index] || {};
                const nameField = row.querySelector('.delegate-name');
                const designationField = row.querySelector('.delegate-designation');
                const companyField = row.querySelector('.delegate-company');
                const countryField = row.querySelector('.delegate-country');
                const emailField = row.querySelector('.delegate-email');
                const mobileField = row.querySelector('.delegate-mobile');

                if (nameField) nameField.value = delegate.delegate_name || '';
                if (designationField) designationField.value = delegate.designation || '';
                if (companyField) companyField.value = delegate.company_name || '';
                if (countryField) countryField.value = delegate.country || '';
                if (emailField) emailField.value = delegate.email || '';
                if (mobileField) mobileField.value = delegate.mobile || '';
            });

            return true;
        } catch (error) {
            generateDelegates();
            return false;
        }
    }

    function getPayload() {
        const billingCountryCode = String((billingCountry.value || 'IN')).toUpperCase();
        return {
            event_ticket_plan_id: selectedPlanId(),
            quantity: normalizeQuantity(),
            billing_country_code: billingCountryCode,
            preferred_currency: activeCurrency(),
            billing_company_name: document.getElementById('billingCompanyName').value.trim(),
            billing_address_line_1: document.getElementById('billingAddress1').value.trim(),
            billing_address_line_2: document.getElementById('billingAddress2').value.trim(),
            gstin: activeCurrency() === 'INR' ? (document.getElementById('gstin')?.value || '').trim() : '',
            promo_code: document.getElementById('promoCode').value.trim().toUpperCase(),
            delegates: collectDelegates(),
        };
    }

    function validateReview(payload) {
        const errors = { _global: '', fields: {}, delegates: [] };

        if (!payload.event_ticket_plan_id) {
            errors._global = 'Please select a ticket plan.';
        }

        if (payload.quantity < minTicketQuantity || payload.quantity > maxTicketQuantity) {
            errors.fields.ticketQty = 'Quantity must be between ' + minTicketQuantity + ' and ' + maxTicketQuantity + '.';
        }

        if (!payload.billing_company_name) {
            errors.fields.billingCompanyName = 'Billing/Company Name is required.';
        }
        if (!payload.billing_address_line_1) {
            errors.fields.billingAddress1 = 'Billing Address (line 1) is required.';
        }
        if (!payload.billing_country_code) {
            errors.fields.billingCountry = 'Billing Country is required.';
        } else if (payload.preferred_currency === 'INR' && payload.billing_country_code !== 'IN') {
            errors.fields.billingCountry = 'For INR, billing country must be India.';
        } else if (payload.preferred_currency === 'USD' && payload.billing_country_code === 'IN') {
            errors.fields.billingCountry = 'For USD, billing country cannot be India.';
        }

        if (!payload.delegates.length || payload.delegates.length !== payload.quantity) {
            errors.fields.ticketQty = 'Delegate count must match quantity.';
        }

        for (let i = 0; i < payload.delegates.length; i++) {
            const d = payload.delegates[i];
            const rowErrors = {};
            if (!d.delegate_name) rowErrors.delegate_name = 'Delegate Name is required.';
            if (!d.company_name) rowErrors.company_name = 'Company Name is required.';
            if (!d.country) rowErrors.country = 'Country is required.';
            if (!d.email) rowErrors.email = 'Email Address is required.';
            if (!d.mobile) rowErrors.mobile = 'Mobile Number is required.';
            errors.delegates[i] = rowErrors;
        }

        const hasErrors = Boolean(errors._global)
            || Object.keys(errors.fields).length > 0
            || errors.delegates.some(function (row) { return row && Object.keys(row).length > 0; });

        return {
            valid: !hasErrors,
            errors: hasErrors ? errors : null,
        };
    }

    function validateSummary(payload) {
        if (!payload.event_ticket_plan_id) return 'Please select a ticket plan.';
        if (payload.quantity < minTicketQuantity || payload.quantity > maxTicketQuantity) return 'Quantity must be between ' + minTicketQuantity + ' and ' + maxTicketQuantity + '.';
        return '';
    }

    async function fetchSummary(options) {
        const opts = Object.assign({
            showMessage: false,
            showPromoFeedback: false,
        }, options || {});

        const payload = getPayload();
        const error = validateSummary(payload);
        if (error) {
            if (opts.showMessage) {
                setMessage('warning', error);
            }
            if (opts.showPromoFeedback) {
                setPromoFeedback('', '');
            }
            return null;
        }

        const resp = await fetch('{{ route('frontend.events.summary', $event->slug) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        });
        const data = await resp.json();
        if (!resp.ok) throw new Error(data.message || 'Unable to calculate summary.');

        const planCard = selectedPlanCard();
        document.getElementById('summaryPlanName').textContent = planCard ? planCard.querySelector('h4').textContent.trim() : '-';
        document.getElementById('summaryCurrency').textContent = data.display_currency;
        document.getElementById('summarySubtotal').textContent = formatMinor(data.subtotal_minor, data.display_currency);
        document.getElementById('summaryPromo').textContent = '- ' + formatMinor(data.promo_discount_minor, data.display_currency);
        document.getElementById('summaryGateway').textContent = formatMinor(data.gateway_charge_minor, data.display_currency);
        document.getElementById('summaryTotal').textContent = formatMinor(data.grand_total_minor, data.display_currency);
        if (opts.showMessage) {
            setMessage('success', 'Summary updated successfully.');
        }

        if (opts.showPromoFeedback) {
            const promoCode = String(payload.promo_code || '').trim();
            if (promoCode !== '') {
                const savingsText = formatMinor(data.promo_discount_minor, data.display_currency);
                if (String(data.promo_code || '').trim() !== '') {
                    setPromoFeedback('success', 'You Saved ' + savingsText);
                } else {
                    setPromoFeedback('error', 'Invalid Promo Code');
                }
            } else {
                setPromoFeedback('', '');
            }
        }
        return data;
    }

    let autoSummaryTimer = null;
    function requestAutoSummary() {
        if (autoSummaryTimer) {
            clearTimeout(autoSummaryTimer);
        }
        autoSummaryTimer = setTimeout(async function () {
            try {
                await fetchSummary();
            } catch (e) {
                // Ignore while user is still filling form. Explicit actions show messages.
            }
        }, 280);
    }

    document.getElementById('applyPromoBtn').addEventListener('click', async function () {
        try {
            await fetchSummary({ showMessage: true, showPromoFeedback: true });
        } catch (e) {
            setMessage('danger', e.message);
            if (String((promoInput && promoInput.value) || '').trim() !== '') {
                setPromoFeedback('error', 'Invalid Promo Code');
            } else {
                setPromoFeedback('', '');
            }
        }
    });

    if (promoInput) {
        promoInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                document.getElementById('applyPromoBtn').click();
            }
        });
        promoInput.addEventListener('input', function () {
            setPromoFeedback('', '');
            requestAutoSummary();
        });
    }

    nextReviewBtn.addEventListener('click', async function () {
        try {
            const payload = getPayload();
            const reviewValidation = validateReview(payload);
            if (!reviewValidation.valid) {
                applyValidationErrors(reviewValidation.errors);
                setMessage('warning', firstValidationMessage(reviewValidation.errors) || 'Please complete all required fields.');
                return;
            }

            clearAllFieldErrors();
            const summary = await fetchSummary({ showMessage: true, showPromoFeedback: true });
            if (!summary) return;
            const saved = {
                payload: payload,
                summary: summary,
                selected_plan_id: selectedPlanId(),
                saved_at: Date.now()
            };
            sessionStorage.setItem(reviewStorageKey, JSON.stringify(saved));

            const reviewUrl = new URL(reviewUrlBase, window.location.origin);
            reviewUrl.searchParams.set('plan', String(saved.selected_plan_id));
            window.location.href = reviewUrl.pathname + reviewUrl.search;
        } catch (e) {
            setMessage('danger', e.message || 'Unable to prepare review.');
        }
    });

    window.addEventListener('ticket-plan-selected', refreshPackageDetailsPanel);
    window.addEventListener('ticket-plan-selected', requestAutoSummary);
    document.querySelectorAll('[data-currency-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            setTimeout(refreshPackageDetailsPanel, 0);
            setTimeout(syncBillingCountryAvailability, 0);
            setTimeout(syncGstinVisibility, 0);
            setTimeout(resetSummaryByCurrency, 0);
            setTimeout(requestAutoSummary, 0);
        });
    });

    qtyInput.addEventListener('change', function () {
        generateDelegates();
        refreshPackageDetailsPanel();
        requestAutoSummary();
    });
    qtyInput.addEventListener('input', function () {
        refreshPackageDetailsPanel();
        requestAutoSummary();
    });
    ['billingCompanyName', 'billingAddress1', 'billingCountry', 'ticketQty'].forEach(function (fieldId) {
        const el = document.getElementById(fieldId);
        if (!el) return;
        el.addEventListener('input', function () { clearFieldError(el); });
        el.addEventListener('change', function () { clearFieldError(el); });
    });
    delegatesContainer.addEventListener('input', function (event) {
        const target = event.target;
        if (target && target.classList) {
            if (target.classList.contains('delegate-name')
                || target.classList.contains('delegate-company')
                || target.classList.contains('delegate-country')
                || target.classList.contains('delegate-email')
                || target.classList.contains('delegate-mobile')) {
                clearFieldError(target);
            }
        }
    });
    billingCountry.addEventListener('change', requestAutoSummary);
    restoreReviewDraft();
    refreshPackageDetailsPanel();
    syncBillingCountryAvailability();
    syncGstinVisibility();
    resetSummaryByCurrency();
    requestAutoSummary();
})();
</script>
@endif
@if($isReviewStep)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const reviewMessage = document.getElementById('reviewMessage');
    const payNowBtn = document.getElementById('payNowBtn');
    const confirmDetails = document.getElementById('confirmDetails');
    const confirmPaymentTerms = document.getElementById('confirmPaymentTerms');
    const reviewStorageKey = 'ticket_review_{{ $event->id }}';
    const backDetailsUrl = @json($backToDetailsUrl);
    const storedRaw = sessionStorage.getItem(reviewStorageKey);

    function setReviewMessage(type, text) {
        if (!reviewMessage) return;
        reviewMessage.innerHTML = '<div class="alert alert-' + type + ' mb-0">' + text + '</div>';
    }

    function formatMinor(minor, currency) {
        const value = Number(minor || 0) / 100;
        return (currency === 'USD' ? 'USD ' : 'INR ') + value.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function confirmationsChecked() {
        return Boolean(confirmDetails && confirmDetails.checked && confirmPaymentTerms && confirmPaymentTerms.checked);
    }

    function syncPayButton() {
        if (payNowBtn) {
            payNowBtn.disabled = !confirmationsChecked();
        }
    }

    if (!storedRaw) {
        setReviewMessage('warning', 'No review data found. Please complete the details form first.');
        payNowBtn.disabled = true;
        return;
    }

    let stored;
    try {
        stored = JSON.parse(storedRaw);
    } catch (e) {
        setReviewMessage('danger', 'Invalid review data. Please fill details again.');
        payNowBtn.disabled = true;
        return;
    }

    const payload = stored.payload || {};
    const summary = stored.summary || {};

    const mkRow = (k, v) => '<div class="review-row"><div class="k">' + k + '</div><div class="v">' + (v || '-') + '</div></div>';
    const billingRows = [
        mkRow('Billing/Company Name', payload.billing_company_name),
        mkRow('Billing Country', payload.billing_country_code),
        mkRow('Number of Tickets', payload.quantity),
        mkRow('Billing Address 1', payload.billing_address_line_1),
        mkRow('Billing Address 2', payload.billing_address_line_2 || '-'),
        mkRow('Promo Code', payload.promo_code || '-'),
    ];

    if (String(payload.preferred_currency || 'INR').toUpperCase() === 'INR') {
        billingRows.splice(2, 0, mkRow('GSTIN', payload.gstin || '-'));
    }

    document.getElementById('reviewBilling').innerHTML = billingRows.join('');

    const delegates = Array.isArray(payload.delegates) ? payload.delegates : [];
    document.getElementById('reviewDelegates').innerHTML = delegates.map((d, i) =>
        '<div class="review-delegate-card mb-2">' +
        '<h6 class="mb-2">Delegate ' + (i + 1) + '</h6>' +
        '<div class="review-grid">' +
        mkRow('Delegate Name', d.delegate_name) +
        mkRow('Designation/Job Title', d.designation || '-') +
        mkRow('Company Name', d.company_name) +
        mkRow('Country', d.country) +
        mkRow('Email Address', d.email) +
        mkRow('Mobile Number', d.mobile) +
        '</div></div>'
    ).join('') || '<div class="text-muted">No delegates.</div>';

    document.getElementById('rvTicketName').textContent = document.querySelector('[data-plan-card] h4')?.textContent.trim() || '-';
    document.getElementById('rvCurrency').textContent = summary.display_currency || '-';
    document.getElementById('rvSubtotal').textContent = formatMinor(summary.subtotal_minor, summary.display_currency);
    document.getElementById('rvPromo').textContent = '- ' + formatMinor(summary.promo_discount_minor, summary.display_currency);
    document.getElementById('rvGateway').textContent = formatMinor(summary.gateway_charge_minor, summary.display_currency);
    document.getElementById('rvTotal').textContent = formatMinor(summary.grand_total_minor, summary.display_currency);

    if (confirmDetails) confirmDetails.addEventListener('change', syncPayButton);
    if (confirmPaymentTerms) confirmPaymentTerms.addEventListener('change', syncPayButton);
    syncPayButton();

    payNowBtn.addEventListener('click', async function () {
        if (!confirmationsChecked()) {
            syncPayButton();
            setReviewMessage('warning', 'Please accept both confirmations before payment.');
            return;
        }
        payNowBtn.disabled = true;
        try {
            setReviewMessage('info', 'Creating payment order...');
            const orderResp = await fetch('{{ route('frontend.events.orders', $event->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(payload)
            });
            const orderData = await orderResp.json();
            if (!orderResp.ok) throw new Error(orderData.message || 'Unable to create order.');

            const options = {
                key: orderData.key,
                amount: orderData.amount,
                currency: orderData.currency,
                name: orderData.name,
                description: orderData.description,
                order_id: orderData.razorpay_order_id,
                prefill: orderData.prefill || {},
                theme: orderData.theme || { color: '#6f42f5' },
                handler: async function (response) {
                    const verifyResp = await fetch('{{ route('frontend.payments.verify') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(response)
                    });
                    const verifyData = await verifyResp.json();
                    if (!verifyResp.ok) throw new Error(verifyData.message || 'Payment verification failed.');
                    sessionStorage.removeItem(reviewStorageKey);
                    window.location.href = verifyData.redirect_url || '{{ route('my-tickets.index') }}';
                }
            };
            const rz = new Razorpay(options);
            rz.on('payment.failed', function (resp) {
                const error = (resp && resp.error) || {};
                const metadata = error.metadata || {};
                const reason = error.description || error.reason || 'Payment failed. Please try again.';

                fetch('{{ route('frontend.payments.failed') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        razorpay_order_id: metadata.order_id || orderData.razorpay_order_id,
                        razorpay_payment_id: metadata.payment_id || '',
                        reason: reason,
                        payload: resp || {}
                    })
                }).catch(function () {});

                setReviewMessage('danger', reason);
            });
            rz.open();
        } catch (e) {
            setReviewMessage('danger', e.message || 'Something went wrong.');
        } finally {
            syncPayButton();
        }
    });
})();
</script>
@endif
@endauth
</body>
</html>
