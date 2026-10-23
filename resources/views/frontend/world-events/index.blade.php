<!DOCTYPE html>
<html lang="en">
<head>
@php
    use Carbon\Carbon;

    $pageTitle = $settings['overview_title'];
    $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
    $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
    $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
    $palette = ['#764df0', '#8b5cf6', '#f97316', '#4f46e5', '#e11d48', '#a78bfa', '#2563eb', '#f59e0b'];
    $selectedMonthName = $monthOptions[$selectedMonth] ?? Carbon::create()->month($selectedMonth)->format('F');
    $formatWorldEventDateLabel = function (mixed $startDate, mixed $endDate): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return [
            'range' => $start->format('j F') . ' - ' . $end->format('j F'),
            'day' => $start->isSameDay($end)
                ? $start->format('l')
                : $start->format('l') . ' - ' . $end->format('l'),
        ];
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
    :root {
        --world-events-accent: #764df0;
        --world-events-accent-strong: #5f38d8;
        --world-events-accent-soft: #a78bfa;
    }

    html, body { margin: 0 !important; padding: 0 !important; }
    #wrapper { display: flow-root; margin-bottom: 0 !important; padding-bottom: 0 !important; }
    body.world-events-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
    body.world-events-page > footer { margin-top: 28px !important; }
    body.world-events-page {
        background: #f8f9fa;
        color: #111;
    }

    .world-events-shell {
        position: relative;
        overflow: hidden;
        padding: 120px 0 72px;
    }

    .world-events-world-map {
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

    .world-events-shell > .container {
        position: relative;
        z-index: 1;
    }

    body.world-events-page .inside-page-hero {
        margin-bottom: 30px;
    }

    body.world-events-page .inside-page-hero-headline {
        color: #000 !important;
        -webkit-text-fill-color: #000 !important;
        background: none !important;
        text-shadow: none !important;
        opacity: 1 !important;
        margin-bottom: 10px;
        line-height: 1.1;
    }

    body.world-events-page .inside-page-hero-main {
        color: #000;
        font-size: 42px;
        font-weight: 700;
        line-height: 1;
    }

    body.world-events-page .inside-page-hero-headline small {
        color: #000;
        font-size: 0.6em;
        font-weight: 400;
    }

    body.world-events-page .inside-page-hero-description {
        font-size: 18px;
        line-height: 1.8;
        color: #111;
        margin-bottom: 12px;
        max-width: 1100px;
    }

    .world-events-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
    }

    .world-events-title-row h3 {
        margin: 0;
        font-size: 42px;
        font-weight: 800;
        letter-spacing: -0.03em;
        text-transform: uppercase;
        color: #153b78;
    }

    .world-events-title-row h3 span {
        color: #202939;
    }

    .world-events-tabs {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px;
        background: rgba(17, 37, 74, 0.06);
        border-radius: 999px;
    }

    .world-events-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 138px;
        padding: 12px 18px;
        border-radius: 999px;
        font-size: 15px;
        font-weight: 700;
        color: #334155;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .world-events-tab.active {
        color: #fff;
        background: linear-gradient(135deg, var(--world-events-accent), var(--world-events-accent-strong));
        box-shadow: 0 14px 30px rgba(118, 77, 240, 0.28);
    }

    .world-events-filters {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
        padding: 18px;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(83, 107, 164, 0.14);
        border-radius: 22px;
        box-shadow: 0 18px 45px rgba(31, 71, 148, 0.08);
        backdrop-filter: blur(12px);
    }

    .world-events-field {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 58px;
        padding: 0 16px;
        background: #fff;
        border: 1px solid rgba(56, 88, 150, 0.14);
        border-radius: 16px;
    }

    .world-events-field label {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6b7a99;
        white-space: nowrap;
    }

    .world-events-field select,
    .world-events-field input {
        width: 100%;
        border: 0;
        background: transparent;
        font-size: 15px;
        font-weight: 600;
        color: #19253a;
        outline: none;
        box-shadow: none;
    }

    .world-events-filter-actions {
        display: flex;
        gap: 10px;
    }

    .world-events-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 58px;
        padding: 0 22px;
        border: 0;
        border-radius: 16px;
        font-size: 15px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .world-events-btn-primary {
        color: #fff;
        background: linear-gradient(135deg, var(--world-events-accent), var(--world-events-accent-strong));
        box-shadow: 0 16px 32px rgba(118, 77, 240, 0.24);
    }

    #worldEventModalDone {
        background: #764df0;
        box-shadow: 0 16px 32px rgba(118, 77, 240, 0.24);
    }

    .world-events-btn-light {
        color: #27406f;
        background: #fff;
        border: 1px solid rgba(56, 88, 150, 0.14);
    }

    .world-events-btn-website {
        gap: 10px;
        color: #fff;
        background: #153b78;
        border: 1px solid #153b78;
        box-shadow: 0 16px 32px rgba(21, 59, 120, 0.22);
    }

    .world-events-btn-website:hover {
        color: #fff;
        background: #1d4f9b;
        border-color: #1d4f9b;
    }

    .world-events-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.95fr);
        gap: 20px;
    }

    .world-events-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(83, 107, 164, 0.14);
        border-radius: 24px;
        box-shadow: 0 22px 48px rgba(31, 71, 148, 0.08);
        overflow: hidden;
    }

    .world-events-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid rgba(82, 105, 154, 0.12);
    }

    .world-events-card-header h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #1d2b46;
    }

    .world-events-month-nav {
        display: inline-flex;
        gap: 8px;
    }

    .world-events-month-nav a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #f4f7fb;
        color: #1d2b46;
        text-decoration: none;
        font-size: 18px;
        font-weight: 800;
    }

    .world-events-calendar-head,
    .world-events-calendar-row {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
    }

    .world-events-calendar-head {
        padding: 0 16px 10px;
    }

    .world-events-calendar-head div {
        text-align: center;
        font-size: 12px;
        font-weight: 800;
        color: #7b8aa7;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .world-events-day {
        min-height: 118px;
        padding: 10px 8px;
        border-top: 1px solid rgba(82, 105, 154, 0.12);
        border-right: 1px solid rgba(82, 105, 154, 0.08);
        background: #fff;
    }

    .world-events-calendar-row .world-events-day:last-child {
        border-right: 0;
    }

    .world-events-day.outside {
        background: rgba(245, 247, 251, 0.9);
        color: #b0b9ca;
    }

    .world-events-day-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        font-size: 14px;
        font-weight: 800;
        color: #27344d;
    }

    .world-events-day.today .world-events-day-number {
        color: #fff;
        background: linear-gradient(135deg, var(--world-events-accent), var(--world-events-accent-strong));
    }

    .world-events-pill {
        display: block;
        margin-top: 8px;
        padding: 8px 10px;
        border-radius: 12px;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.3;
        text-decoration: none;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    button.world-events-pill {
        width: 100%;
        border: 0;
        text-align: left;
        cursor: pointer;
    }

    .world-events-pill small {
        display: block;
        margin-top: 3px;
        font-size: 11px;
        font-weight: 600;
        opacity: 0.92;
    }

    .world-events-side-layout {
        display: grid;
        grid-template-rows: auto auto;
        gap: 20px;
        height: 100%;
    }

    .world-events-timeline-list {
        padding: 18px 20px 22px;
    }

    .world-events-timeline-item {
        display: grid;
        grid-template-columns: 6px minmax(0, 1fr);
        gap: 14px;
        padding: 12px 0;
        width: 100%;
        border: 0;
        background: transparent;
        text-align: left;
    }

    .world-events-timeline-item + .world-events-timeline-item {
        border-top: 1px solid rgba(82, 105, 154, 0.12);
    }

    .world-events-list-trigger {
        flex: 1 1 auto;
        min-width: 0;
        border: 0;
        background: transparent;
        padding: 0;
        text-align: left;
        cursor: pointer;
        font: inherit;
        color: inherit;
    }

    .world-events-dot {
        border-radius: 999px;
    }

    .world-events-list-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        min-width: 0;
    }

    .world-events-list-text {
        min-width: 0;
    }

    .world-events-date-label {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        line-height: 1.1;
    }

    .world-events-date-label__range {
        font-size: 12px;
    }

    .world-events-date-label__day {
        font-size: 11px;
        letter-spacing: 0.1em;
        opacity: 0.9;
    }

    .world-events-list-logo {
        flex: 0 0 112px;
        width: 112px;
        height: 60px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e4eaf4;
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
    }

    .world-events-list-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .world-events-timeline-date {
        color: inherit;
    }

    .world-events-timeline-name {
        margin-top: 3px;
        font-size: 24px;
        font-weight: 800;
        line-height: 1.15;
        color: #202939;
    }

    .world-events-timeline-meta {
        margin-top: 6px;
        font-size: 15px;
        font-weight: 600;
        color: #5d6d87;
    }

    .world-events-insights {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 220px;
        gap: 20px;
    }

    .world-events-heatmap {
        padding: 18px 20px 22px;
    }

    .world-events-heatmap-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 8px;
        margin-top: 14px;
    }

    .world-events-heatmap-month {
        text-align: center;
    }

    .world-events-heatmap-month label {
        display: block;
        margin-bottom: 8px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #7b8aa7;
    }

    .world-events-heatmap-cell {
        height: 54px;
        border-radius: 12px;
        border: 1px solid rgba(82, 105, 154, 0.08);
    }

    .world-events-stat-card {
        padding: 20px;
    }

    .world-events-stat-total {
        font-size: 52px;
        font-weight: 800;
        line-height: 1;
        color: var(--world-events-accent);
        font-variant-numeric: tabular-nums;
    }

    .world-events-stat-title {
        font-size: 14px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #7b8aa7;
    }

    .world-events-stat-list {
        margin: 16px 0 0;
        padding: 0;
        list-style: none;
    }

    .world-events-stat-list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        font-size: 15px;
        font-weight: 700;
        color: #22314f;
        border-top: 1px solid rgba(82, 105, 154, 0.12);
    }

    .world-events-year-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(310px, 0.8fr);
        gap: 20px;
    }

    .world-events-mini-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        padding: 20px;
    }

    .world-events-mini-card {
        padding: 16px;
        border: 1px solid rgba(82, 105, 154, 0.12);
        border-radius: 18px;
        background: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .world-events-mini-card.active {
        border-color: rgba(118, 77, 240, 0.48);
        box-shadow: 0 16px 28px rgba(118, 77, 240, 0.14);
        transform: translateY(-2px);
    }

    .world-events-mini-title {
        margin-bottom: 10px;
        font-size: 20px;
        font-weight: 800;
        color: #22314f;
    }

    .world-events-mini-weekdays,
    .world-events-mini-days {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 5px;
    }

    .world-events-mini-weekdays div,
    .world-events-mini-day {
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        color: #7b8aa7;
    }

    .world-events-mini-day {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        margin: 0 auto;
        color: #22314f;
        border-radius: 999px;
        border: 0;
        background: transparent;
        padding: 0;
    }

    .world-events-mini-day.has-events {
        color: #fff;
        background: var(--dot-color, var(--world-events-accent));
        font-weight: 800;
        cursor: pointer;
    }

    .world-events-mini-day.outside {
        opacity: 0.25;
    }

    .world-events-side-summary {
        padding: 16px 18px 20px;
    }

    .world-events-summary-title {
        margin: 0 0 12px;
        font-size: 26px;
        font-weight: 800;
        color: #1d2b46;
    }

    .world-events-summary-list {
        display: grid;
        gap: 12px;
    }

    .world-events-summary-item {
        padding: 14px 16px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid rgba(82, 105, 154, 0.12);
    }

    .world-events-summary-item strong {
        display: block;
        font-size: 14px;
        font-weight: 800;
        color: #22314f;
    }

    .world-events-summary-item span {
        display: block;
        margin-top: 4px;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.1;
    }

    .world-events-empty {
        padding: 26px 24px;
        text-align: center;
        font-size: 16px;
        font-weight: 700;
        color: #70809c;
    }

    .world-events-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, 0.58);
        z-index: 9999;
    }

    .world-events-modal.is-open {
        display: flex;
    }

    .world-events-modal-dialog {
        width: min(560px, 100%);
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        overflow: hidden;
    }

    .world-events-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 24px 24px 18px;
        border-bottom: 1px solid rgba(82, 105, 154, 0.12);
    }

    .world-events-modal-title {
        margin: 0;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 900;
        color: #1d2b46;
    }

    .world-events-modal-close {
        border: 0;
        background: #f3f6fb;
        color: #41506a;
        width: 40px;
        height: 40px;
        border-radius: 999px;
        font-size: 26px;
        line-height: 1;
        cursor: pointer;
        flex: 0 0 auto;
    }

    .world-events-modal-body {
        padding: 22px 24px 24px;
        display: grid;
        gap: 14px;
    }

    .world-events-modal-meta {
        display: grid;
        gap: 12px;
    }

    .world-events-modal-row {
        display: grid;
        grid-template-columns: 120px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
    }

    .world-events-modal-row strong {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7b8aa7;
    }

    .world-events-modal-row span {
        font-size: 16px;
        font-weight: 700;
        color: #22314f;
        line-height: 1.45;
    }

    .world-events-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 8px;
    }

    .world-events-sponsor-strip {
        margin-top: 0;
    }

    .world-events-sponsor-header {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 20px;
    }

    .world-events-sponsor-title {
        margin: 0;
        font-size: 34px;
        line-height: 1.05;
        font-weight: 900;
        color: #111;
        white-space: nowrap;
    }

    .world-events-sponsor-line {
        flex: 1 1 auto;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #0f766e, rgba(15, 118, 110, 0.18));
    }

    .world-events-sponsor-scroll {
        overflow: hidden;
        padding-bottom: 8px;
    }

    .world-events-sponsor-track {
        display: inline-flex;
        align-items: center;
        gap: 20px;
        min-width: max-content;
        animation: world-events-sponsor-marquee 30s linear infinite;
        will-change: transform;
    }

    .world-events-sponsor-scroll:hover .world-events-sponsor-track {
        animation-play-state: paused;
    }

    .world-events-sponsor-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 190px;
        min-width: 190px;
        height: 122px;
        padding: 18px;
        background: #fff;
        border: 1px solid #e2e8f3;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(22, 52, 105, 0.08);
    }

    .world-events-sponsor-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
    }

    @keyframes world-events-sponsor-marquee {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }

    .world-events-overview-block + .world-events-overview-block {
        margin-top: 34px;
    }

    .world-events-heading-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .world-events-heading {
        margin: 0;
        font-size: 24px;
        line-height: 1;
        font-weight: 900;
        text-transform: uppercase;
        color: #0a5ea8;
        letter-spacing: 0.02em;
    }

    .world-events-toolbar {
        display: grid;
        grid-template-columns: 190px 170px 190px 190px minmax(220px, 1fr) 130px;
        gap: 12px;
        margin-bottom: 14px;
    }

    .world-events-yearly-toolbar {
        display: grid;
        grid-template-columns: 180px 180px 180px minmax(260px, 1fr) 130px;
        gap: 12px;
        margin-bottom: 14px;
        align-items: stretch;
    }

    .world-events-control,
    .world-events-search,
    .world-events-year-box {
        display: flex;
        align-items: center;
        min-height: 50px;
        padding: 0 14px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid #dce5f1;
        border-radius: 14px;
        box-shadow: 0 8px 18px rgba(22, 52, 105, 0.06);
    }

    .world-events-control select,
    .world-events-search input {
        width: 100%;
        border: 0;
        background: transparent;
        color: #24334d;
        font-size: 15px;
        font-weight: 700;
        outline: none;
        box-shadow: none;
    }

    .world-events-search i {
        margin-right: 10px;
        color: #7b8aa7;
    }

    .world-events-toolbar .world-events-btn,
    .world-events-yearly-toolbar .world-events-btn {
        width: 100%;
        min-height: 50px;
        border-radius: 14px;
    }

    .world-events-yearly-toolbar .world-events-search {
        min-width: 0;
    }

    .world-events-yearly-toolbar > .world-events-btn {
        align-self: stretch;
    }

    .world-events-year-box {
        justify-content: space-between;
        gap: 12px;
        font-size: 16px;
        font-weight: 900;
        color: #23314d;
    }

    .world-events-year-nav {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .world-events-year-nav a {
        color: #23314d;
        text-decoration: none;
        font-size: 18px;
        line-height: 1;
    }

    .world-events-stack-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.34fr) minmax(320px, 0.96fr);
        gap: 16px;
    }

    .world-events-panel {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(182, 197, 222, 0.7);
        border-radius: 20px;
        box-shadow: 0 16px 34px rgba(28, 64, 132, 0.08);
        overflow: hidden;
    }

    .world-events-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid #e6edf7;
    }

    .world-events-panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
        color: #1f2f4b;
    }

    .world-events-month-strip {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 6px;
        padding: 14px 18px 8px;
        border-bottom: 1px solid #edf2f8;
    }

    .world-events-month-strip a {
        display: block;
        text-decoration: none;
        text-align: center;
        font-size: 12px;
        font-weight: 800;
        color: #65748f;
        padding: 8px 0;
        border-radius: 10px;
    }

    .world-events-month-strip .active-month {
        border-radius: 10px;
        background: linear-gradient(135deg, var(--world-events-accent), var(--world-events-accent-strong));
        color: #fff;
    }

    .world-events-grid-two {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 180px;
        gap: 16px;
    }

    .world-events-heatmap-card {
        padding: 14px 16px 16px;
    }

    .world-events-heatmap-grid-compact {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 5px;
    }

    .world-events-heatmap-col {
        text-align: center;
    }

    .world-events-heatmap-col span {
        display: block;
        margin-bottom: 7px;
        font-size: 11px;
        font-weight: 800;
        color: #6d7890;
    }

    .world-events-heatmap-cells {
        display: grid;
        gap: 5px;
    }

    .world-events-heatmap-cells div {
        height: 16px;
        border-radius: 4px;
        border: 1px solid rgba(219, 229, 241, 0.85);
    }

    .world-events-heatmap-legend {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px solid #edf2f8;
        color: #5f6f88;
        font-size: 13px;
        font-weight: 700;
    }

    .world-events-heatmap-legend-swatches {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .world-events-heatmap-legend-swatches span {
        width: 14px;
        height: 14px;
        border-radius: 3px;
        display: inline-block;
    }

    .world-events-summary-card {
        padding: 14px 16px 16px;
    }

    .world-events-summary-card .world-events-stat-title {
        margin-bottom: 6px;
        font-size: 15px;
        letter-spacing: 0;
        text-transform: none;
        color: #22314f;
    }

    .world-events-summary-card .world-events-stat-total {
        font-size: 24px;
        margin-bottom: 8px;
    }

    .world-events-summary-metrics {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .world-events-summary-metrics li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 7px 0;
        font-size: 14px;
        font-weight: 700;
        color: #31415f;
        border-top: 1px solid #edf2f8;
    }

    .world-events-summary-metrics li:first-child {
        border-top: 0;
    }

    .world-events-summary-metrics span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .world-events-summary-metrics strong {
        font-variant-numeric: tabular-nums;
        min-width: 24px;
        text-align: right;
    }

    .world-events-summary-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex: 0 0 8px;
    }

    .world-events-year-events {
        padding: 16px 18px 18px;
    }

    .world-events-year-events h4 {
        margin: 0 0 12px;
        font-size: 16px;
        font-weight: 900;
        color: #22314f;
    }

    .world-events-year-event {
        padding: 12px 0 12px 12px;
        width: 100%;
        border-top: 0;
        border-right: 0;
        border-bottom: 0;
        background: transparent;
        text-align: left;
    }

    .world-events-year-event .world-events-list-logo {
        flex-basis: 96px;
        width: 96px;
        height: 51px;
    }

    .world-events-year-event + .world-events-year-event {
        border-top: 1px solid #edf2f8;
    }

    .world-events-year-event strong {
        display: block;
        font-size: 14px;
        font-weight: 900;
    }

    .world-events-year-event span {
        display: block;
        margin-top: 2px;
        font-size: 15px;
        font-weight: 900;
        color: #24334d;
    }

    .world-events-year-event small {
        display: block;
        margin-top: 4px;
        font-size: 14px;
        font-weight: 600;
        color: #5f6f88;
    }

    .world-events-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        align-items: start;
        align-self: start;
    }

    .world-events-stat-tile {
        padding: 16px 12px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid #e2e8f3;
        text-align: center;
        min-height: 176px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }

    .world-events-stat-tile i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin-bottom: 8px;
        border-radius: 12px;
        font-size: 18px;
        color: #fff;
    }

    .world-events-stat-tile strong {
        display: block;
        font-size: 13px;
        line-height: 1.35;
        font-weight: 900;
        color: #22314f;
        min-height: 36px;
    }

    .world-events-stat-tile span {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 8px;
        min-height: 48px;
        font-size: 30px;
        line-height: 1;
        font-weight: 900;
        color: var(--world-events-accent);
        width: 100%;
        font-variant-numeric: tabular-nums;
    }

    @media (max-width: 1399px) {
        .world-events-filters {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .world-events-mini-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .world-events-toolbar {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .world-events-yearly-toolbar {
            grid-template-columns: 170px 170px 170px minmax(220px, 1fr) 130px;
        }
    }

    @media (max-width: 1199px) {
        .world-events-grid,
        .world-events-year-layout,
        .world-events-insights,
        .world-events-stack-layout,
        .world-events-grid-two {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991px) {
        .world-events-title-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .world-events-title-row h3 {
            font-size: 32px;
        }

        .world-events-filters,
        .world-events-mini-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .world-events-toolbar,
        .world-events-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .world-events-yearly-toolbar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .world-events-shell {
            padding: 96px 0 56px;
        }

        .world-events-title-row h3 {
            font-size: 26px;
        }

        .world-events-tabs {
            width: 100%;
        }

        .world-events-tab {
            min-width: 0;
            flex: 1 1 0;
        }

        .world-events-filters,
        .world-events-mini-grid {
            grid-template-columns: 1fr;
        }

        .world-events-toolbar,
        .world-events-yearly-toolbar,
        .world-events-stats-grid {
            grid-template-columns: 1fr;
        }

        .world-events-calendar-head,
        .world-events-calendar-row {
            min-width: 760px;
        }

        .world-events-calendar-wrap {
            overflow-x: auto;
        }

        .world-events-world-map {
            top: 160px;
            width: 118vw;
            aspect-ratio: 1411 / 702;
            opacity: .18;
        }
    }
</style>
</head>
<body class="world-events-page">
<div id="wrapper">
    @include('frontend.partials.header')

<section class="world-events-shell">
    <div class="world-events-world-map" aria-hidden="true"></div>
    <div class="container">
        @include('frontend.partials.inner-page-hero', [
            'heroLabel' => $settings['hero_label'],
            'heroTitle' => $settings['hero_title'],
            'heroSuffix' => $settings['hero_title_suffix'],
            'heroDescription' => $settings['hero_description'],
        ])

        @php
            $monthlyTabUrl = route('frontend.world-events.index', array_merge(request()->except('view'), ['view' => 'monthly']));
            $yearlyTabUrl = route('frontend.world-events.index', array_merge(request()->except('view'), ['view' => 'yearly']));
            $activeMonthsCount = $yearlyMonths->filter(fn ($month) => $month['events']->isNotEmpty())->count();
            $previousYearUrl = route('frontend.world-events.index', array_merge(request()->except('year'), ['view' => 'yearly', 'year' => $selectedYear - 1]));
            $nextYearUrl = route('frontend.world-events.index', array_merge(request()->except('year'), ['view' => 'yearly', 'year' => $selectedYear + 1]));
        @endphp
        <div class="world-events-overview-block">
            <div class="world-events-heading-row">
                <h3 class="world-events-heading">{{ strtoupper($settings['overview_title']) }}</h3>
                <div class="world-events-tabs">
                    <a class="world-events-tab {{ $selectedView === 'monthly' ? 'active' : '' }}" href="{{ $monthlyTabUrl }}">{{ $settings['monthly_tab_text'] }}</a>
                    <a class="world-events-tab {{ $selectedView === 'yearly' ? 'active' : '' }}" href="{{ $yearlyTabUrl }}">{{ $settings['yearly_tab_text'] }}</a>
                </div>
            </div>

            @if($selectedView === 'monthly')
            <form method="GET" action="{{ route('frontend.world-events.index') }}" id="worldEventsMonthlyFilterForm" data-world-events-filter-form>
                <input type="hidden" name="view" value="monthly">
                <div class="world-events-toolbar">
                    <div class="world-events-control">
                        <select id="month" name="month">
                            @foreach($monthOptions as $monthNumber => $monthLabel)
                                <option value="{{ $monthNumber }}" @selected((int) $selectedMonth === (int) $monthNumber)>{{ $monthLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="world-events-control">
                        <select id="year" name="year">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="world-events-control">
                        <select id="event_name" name="event_name">
                            <option value="">{{ $settings['event_filter_label'] }}</option>
                            @foreach($eventNames as $eventName)
                                <option value="{{ $eventName }}" @selected($selectedEventName === $eventName)>{{ $eventName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="world-events-control">
                        <select id="country" name="country">
                            <option value="">{{ $settings['country_filter_label'] }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="world-events-search">
                        <i class="fa fa-search"></i>
                        <input id="search" type="text" name="search" value="{{ $search }}" placeholder="{{ $settings['search_placeholder'] }}">
                    </div>
                    <button type="submit" class="world-events-btn world-events-btn-primary">Search</button>
                </div>
            </form>

        @php
            $previousMonth = Carbon::create($selectedYear, $selectedMonth, 1)->subMonth();
            $nextMonth = Carbon::create($selectedYear, $selectedMonth, 1)->addMonth();
        @endphp

            <div class="world-events-stack-layout">
                <div class="world-events-card">
                    <div class="world-events-card-header">
                        <h4>{{ $selectedMonthName }} {{ $selectedYear }}</h4>
                        <div class="world-events-month-nav">
                            <a href="{{ route('frontend.world-events.index', array_merge(request()->except('month', 'year'), ['view' => 'monthly', 'month' => $previousMonth->month, 'year' => $previousMonth->year])) }}">&lsaquo;</a>
                            <a href="{{ route('frontend.world-events.index', array_merge(request()->except('month', 'year'), ['view' => 'monthly', 'month' => $nextMonth->month, 'year' => $nextMonth->year])) }}">&rsaquo;</a>
                        </div>
                    </div>

                    <div class="world-events-calendar-wrap">
                        <div class="world-events-calendar-head">
                            <div>Sun</div>
                            <div>Mon</div>
                            <div>Tue</div>
                            <div>Wed</div>
                            <div>Thu</div>
                            <div>Fri</div>
                            <div>Sat</div>
                        </div>

                        @foreach($calendarWeeks as $week)
                            <div class="world-events-calendar-row">
                                @foreach($week as $day)
                                    <div class="world-events-day {{ $day['isCurrentMonth'] ? '' : 'outside' }} {{ $day['date']->isToday() ? 'today' : '' }}">
                                        <div class="world-events-day-number">{{ $day['date']->day }}</div>
                                        @foreach($day['events'] as $index => $event)
                                            @php $color = $palette[($event->id + $index) % count($palette)]; @endphp
                                            <button
                                                type="button"
                                                class="world-events-pill"
                                                data-world-event-trigger
                                                data-event-name="{{ e($event->name) }}"
                                                data-event-date="{{ e(Carbon::parse($event->start_date)->format('d M Y') . ' - ' . Carbon::parse($event->end_date)->format('d M Y')) }}"
                                                data-event-location="{{ e(trim(implode(', ', array_filter([$event->city, $event->country])))) }}"
                                                data-event-theme="{{ e($event->theme ?: '-') }}"
                                                data-event-stars="{{ e((string) $event->stars) }}"
                                                data-event-website="{{ e($event->website ?: '') }}"
                                                style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}cc);"
                                            >
                                                {{ $event->name }}
                                                <small>{{ trim(implode(', ', array_filter([$event->city, $event->country]))) }}</small>
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="world-events-side-layout">
                    <div class="world-events-panel">
                        <div class="world-events-panel-header">
                            <h4 class="world-events-panel-title">{{ $selectedYear }} {{ $settings['monthly_timeline_title'] }}</h4>
                        </div>

                        <div class="world-events-month-strip">
                            @foreach($yearlyMonths as $monthData)
                                <a
                                    href="{{ route('frontend.world-events.index', array_merge(request()->except('month', 'year'), ['view' => 'monthly', 'year' => $selectedYear, 'month' => $monthData['month']])) }}"
                                    class="{{ (int) $monthData['month'] === (int) $selectedMonth ? 'active-month' : '' }}"
                                >
                                    {{ Carbon::create($selectedYear, $monthData['month'], 1)->format('M') }}
                                </a>
                            @endforeach
                        </div>

                        @if($monthlyEvents->isEmpty())
                            <div class="world-events-empty">{{ $settings['empty_state_text'] }}</div>
                        @else
                            <div class="world-events-timeline-list">
                                @foreach($monthlyEvents as $loopIndex => $event)
                                    @php
                                        $color = $palette[$loopIndex % count($palette)];
                                        $logoPath = trim((string) ($event->logo_path ?? ''));
                                        $logoSrc = $logoPath !== ''
                                            ? ((str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://') || str_starts_with($logoPath, '//') || str_starts_with($logoPath, '/')) ? $logoPath : asset(ltrim($logoPath, '/')))
                                            : '';
                                    @endphp
                                    <div class="world-events-timeline-item">
                                        <div class="world-events-dot" style="background: {{ $color }};"></div>
                                        <div class="world-events-list-content">
                                            <button
                                                type="button"
                                                class="world-events-list-trigger"
                                                data-world-event-trigger
                                                data-event-name="{{ e($event->name) }}"
                                                data-event-date="{{ e(Carbon::parse($event->start_date)->format('d M Y') . ' - ' . Carbon::parse($event->end_date)->format('d M Y')) }}"
                                                data-event-location="{{ e(trim(implode(', ', array_filter([$event->city, $event->country])))) }}"
                                                data-event-theme="{{ e($event->theme ?: '-') }}"
                                                data-event-stars="{{ e((string) $event->stars) }}"
                                                data-event-website="{{ e($event->website ?: '') }}"
                                            >
                                                <div class="world-events-list-text">
                                                    <div class="world-events-timeline-date" style="color: {{ $color }};">
                                                        {{ Carbon::parse($event->start_date)->format('j F') }} - {{ Carbon::parse($event->end_date)->format('j F') }}
                                                    </div>
                                                    <div class="world-events-timeline-name">{{ $event->name }}</div>
                                                    <div class="world-events-timeline-meta">{{ trim(implode(', ', array_filter([$event->city, $event->country]))) }}</div>
                                                </div>
                                            </button>
                                            @if($logoSrc !== '')
                                                @if($event->website)
                                                    <a href="{{ $event->website }}" class="world-events-list-logo" target="_blank" rel="noopener" aria-label="Open {{ $event->name }} website">
                                                        <img src="{{ $logoSrc }}" alt="{{ $event->name }} logo">
                                                    </a>
                                                @else
                                                    <span class="world-events-list-logo">
                                                        <img src="{{ $logoSrc }}" alt="{{ $event->name }} logo">
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="world-events-grid-two">
                        <div class="world-events-panel world-events-heatmap-card">
                            <h4 class="world-events-panel-title" style="margin-bottom: 12px;">{{ $settings['yearly_calendar_title'] }}</h4>
                            <div class="world-events-heatmap-grid-compact">
                                @foreach($yearlyMonths as $monthIndex => $monthData)
                                    @php
                                        $eventCount = $monthData['events']->count();
                                        $shades = collect(range(1, 3))->map(function (int $row) use ($eventCount) {
                                            $base = 0.08 + ($eventCount * 0.10);
                                            return max(0.08, min(0.88, $base - (($row - 1) * 0.14)));
                                        });
                                    @endphp
                                    <div class="world-events-heatmap-col">
                                        <span>{{ Carbon::create($selectedYear, $monthData['month'], 1)->format('M') }}</span>
                                        <div class="world-events-heatmap-cells">
                                            @foreach($shades as $shade)
                                                <div style="background: rgba(14, 165, 233, {{ $shade }});"></div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="world-events-heatmap-legend">
                                <span>Low</span>
                                <span class="world-events-heatmap-legend-swatches">
                                    <span style="background: rgba(14, 165, 233, 0.12);"></span>
                                    <span style="background: rgba(14, 165, 233, 0.24);"></span>
                                    <span style="background: rgba(14, 165, 233, 0.38);"></span>
                                    <span style="background: rgba(14, 165, 233, 0.54);"></span>
                                    <span style="background: rgba(14, 165, 233, 0.72);"></span>
                                </span>
                                <span>High</span>
                            </div>
                        </div>

                        <div class="world-events-card world-events-summary-card">
                            <div class="world-events-stat-title">Total Events</div>
                            <div class="world-events-stat-total">{{ $stats['total_events'] }}</div>
                            <ul class="world-events-summary-metrics">
                                <li>
                                    <span><i class="world-events-summary-dot" style="background:#764df0;"></i>Countries</span>
                                    <strong>{{ $stats['countries'] }}</strong>
                                </li>
                                <li>
                                    <span><i class="world-events-summary-dot" style="background:#8b5cf6;"></i>Cities</span>
                                    <strong>{{ $stats['cities'] }}</strong>
                                </li>
                                <li>
                                    <span><i class="world-events-summary-dot" style="background:#ff7f9b;"></i>Themes</span>
                                    <strong>{{ $stats['themes'] }}</strong>
                                </li>
                                <li>
                                    <span><i class="world-events-summary-dot" style="background:#5f38d8;"></i>Months</span>
                                    <strong>{{ $activeMonthsCount }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($selectedView === 'yearly')
            <form method="GET" action="{{ route('frontend.world-events.index') }}" id="worldEventsYearlyFilterForm" data-world-events-filter-form>
                <input type="hidden" name="view" value="yearly">
                <div class="world-events-yearly-toolbar">
                    <div class="world-events-year-box">
                        <span>{{ $selectedYear }}</span>
                        <span class="world-events-year-nav">
                            <a href="{{ $previousYearUrl }}" aria-label="Previous year"><i class="fa fa-angle-left"></i></a>
                            <a href="{{ $nextYearUrl }}" aria-label="Next year"><i class="fa fa-angle-right"></i></a>
                        </span>
                    </div>
                    <div class="world-events-control">
                        <select name="event_name">
                            <option value="">{{ $settings['event_filter_label'] }}</option>
                            @foreach($eventNames as $eventName)
                                <option value="{{ $eventName }}" @selected($selectedEventName === $eventName)>{{ $eventName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="world-events-control">
                        <select name="country">
                            <option value="">{{ $settings['country_filter_label'] }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="world-events-search">
                        <i class="fa fa-search"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ $settings['search_placeholder'] }}">
                    </div>
                    <button type="submit" class="world-events-btn world-events-btn-primary">Search</button>
                </div>
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
            </form>

            <div class="world-events-year-layout">
                <div class="world-events-card">
                    <div class="world-events-mini-grid">
                        @foreach($yearlyMonths as $monthIndex => $monthData)
                            @php $color = $palette[$monthIndex % count($palette)]; @endphp
                            <div class="world-events-mini-card {{ (int) $monthData['month'] === (int) $selectedMonth ? 'active' : '' }}">
                                <div class="world-events-mini-title">{{ $monthData['name'] }}</div>
                                <div class="world-events-mini-weekdays">
                                    <div>S</div>
                                    <div>M</div>
                                    <div>T</div>
                                    <div>W</div>
                                    <div>T</div>
                                    <div>F</div>
                                    <div>S</div>
                                </div>
                                <div class="world-events-mini-days">
                                    @foreach($monthData['weeks']->flatten(1) as $day)
                                        @if($day['events']->isNotEmpty())
                                            @php $event = $day['events']->first(); @endphp
                                            <button
                                                type="button"
                                                class="world-events-mini-day {{ $day['isCurrentMonth'] ? '' : 'outside' }} has-events"
                                                style="--dot-color: {{ $color }}"
                                                data-world-event-trigger
                                                data-event-name="{{ e($event->name) }}"
                                                data-event-date="{{ e(Carbon::parse($event->start_date)->format('d M Y') . ' - ' . Carbon::parse($event->end_date)->format('d M Y')) }}"
                                                data-event-location="{{ e(trim(implode(', ', array_filter([$event->city, $event->country])))) }}"
                                                data-event-theme="{{ e($event->theme ?: '-') }}"
                                                data-event-stars="{{ e((string) $event->stars) }}"
                                                data-event-website="{{ e($event->website ?: '') }}"
                                                aria-label="Open {{ $event->name }} details"
                                            >
                                                {{ $day['date']->day }}
                                            </button>
                                        @else
                                            <div class="world-events-mini-day {{ $day['isCurrentMonth'] ? '' : 'outside' }}">
                                                {{ $day['date']->day }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="world-events-side-layout">
                    <div class="world-events-panel world-events-year-events">
                        <h4>Events in {{ $selectedYear }}</h4>

                        @if($yearlyEvents->isEmpty())
                            <div class="world-events-empty">{{ $settings['empty_state_text'] }}</div>
                        @else
                            @foreach($yearlyEvents as $loopIndex => $event)
                                @php
                                    $color = $palette[$loopIndex % count($palette)];
                                    $logoPath = trim((string) ($event->logo_path ?? ''));
                                    $logoSrc = $logoPath !== ''
                                        ? ((str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://') || str_starts_with($logoPath, '//') || str_starts_with($logoPath, '/')) ? $logoPath : asset(ltrim($logoPath, '/')))
                                        : '';
                                    $dateLabel = $formatWorldEventDateLabel($event->start_date, $event->end_date);
                                @endphp
                                <div
                                    class="world-events-year-event"
                                    style="border-left: 5px solid {{ $color }};"
                                >
                                    <div class="world-events-list-content">
                                        <button
                                            type="button"
                                            class="world-events-list-trigger"
                                            data-world-event-trigger
                                            data-event-name="{{ e($event->name) }}"
                                            data-event-date="{{ e(Carbon::parse($event->start_date)->format('d M Y') . ' - ' . Carbon::parse($event->end_date)->format('d M Y')) }}"
                                            data-event-location="{{ e(trim(implode(', ', array_filter([$event->city, $event->country])))) }}"
                                            data-event-theme="{{ e($event->theme ?: '-') }}"
                                            data-event-stars="{{ e((string) $event->stars) }}"
                                            data-event-website="{{ e($event->website ?: '') }}"
                                        >
                                            <div class="world-events-list-text">
                                                <div class="world-events-date-label" style="color: {{ $color }};">
                                                    <span class="world-events-date-label__range">{{ $dateLabel['range'] }}</span>
                                                    <span class="world-events-date-label__day">{{ $dateLabel['day'] }}</span>
                                                </div>
                                                <span>{{ $event->name }}</span>
                                                <small>{{ trim(implode(', ', array_filter([$event->city, $event->country]))) }}</small>
                                            </div>
                                        </button>
                                        @if($logoSrc !== '')
                                            @if($event->website)
                                                <a href="{{ $event->website }}" class="world-events-list-logo" target="_blank" rel="noopener" aria-label="Open {{ $event->name }} website">
                                                    <img src="{{ $logoSrc }}" alt="{{ $event->name }} logo">
                                                </a>
                                            @else
                                                <span class="world-events-list-logo">
                                                    <img src="{{ $logoSrc }}" alt="{{ $event->name }} logo">
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="world-events-stats-grid">
                        <div class="world-events-stat-tile">
                            <i class="fa fa-calendar" style="background: #764df0;"></i>
                            <strong>Total Events</strong>
                            <span>{{ $stats['total_events'] }}</span>
                        </div>
                        <div class="world-events-stat-tile">
                            <i class="fa fa-globe" style="background: #f59e0b;"></i>
                            <strong>Countries</strong>
                            <span>{{ $stats['countries'] }}</span>
                        </div>
                        <div class="world-events-stat-tile">
                            <i class="fa fa-map-marker-alt" style="background: #8b5cf6;"></i>
                            <strong>Cities</strong>
                            <span>{{ $stats['cities'] }}</span>
                        </div>
                        <div class="world-events-stat-tile">
                            <i class="fa fa-building" style="background: #fb7185;"></i>
                            <strong>Themes</strong>
                            <span>{{ $stats['themes'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

@if(($sponsors ?? collect())->isNotEmpty())
    <section class="pb30" style="padding-top: 18px; padding-bottom: 36px;">
        <div class="container">
            <div class="world-events-sponsor-strip">
                <div class="world-events-sponsor-header">
                    <h4 class="world-events-sponsor-title">Sponsors & Partners</h4>
                    <span class="world-events-sponsor-line" aria-hidden="true"></span>
                </div>
                <div class="world-events-sponsor-scroll">
                    <div class="world-events-sponsor-track">
                        @foreach([$sponsors, $sponsors] as $sponsorLoop)
                            @foreach($sponsorLoop as $sponsor)
                                @php
                                    $sponsorLogo = trim((string) ($sponsor->logo_url ?? ''));
                                    $sponsorLogoSrc = $sponsorLogo === ''
                                        ? asset('images/logo.webp')
                                        : ((str_starts_with($sponsorLogo, 'http://') || str_starts_with($sponsorLogo, 'https://') || str_starts_with($sponsorLogo, '//') || str_starts_with($sponsorLogo, '/'))
                                            ? $sponsorLogo
                                            : asset(ltrim($sponsorLogo, '/')));
                                @endphp
                                <a href="{{ route('frontend.sponsors.show', $sponsor) }}" class="world-events-sponsor-logo" aria-label="{{ $sponsor->name }}">
                                    <img src="{{ $sponsorLogoSrc }}" alt="{{ $sponsor->name }}">
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
</div>

<div class="world-events-modal" id="worldEventModal" aria-hidden="true">
    <div class="world-events-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="worldEventModalTitle">
        <div class="world-events-modal-header">
            <h3 class="world-events-modal-title" id="worldEventModalTitle">Event Details</h3>
            <button type="button" class="world-events-modal-close" id="worldEventModalClose" aria-label="Close">&times;</button>
        </div>
        <div class="world-events-modal-body">
            <div class="world-events-modal-meta">
                <div class="world-events-modal-row">
                    <strong>Date</strong>
                    <span id="worldEventModalDate">-</span>
                </div>
                <div class="world-events-modal-row">
                    <strong>Location</strong>
                    <span id="worldEventModalLocation">-</span>
                </div>
                <div class="world-events-modal-row">
                    <strong>Theme</strong>
                    <span id="worldEventModalTheme">-</span>
                </div>
            </div>
            <div class="world-events-modal-actions">
                <a href="#" class="world-events-btn world-events-btn-website" id="worldEventModalWebsite" target="_blank" rel="noopener" style="display:none;">Open Website <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                <button type="button" class="world-events-btn world-events-btn-primary" id="worldEventModalDone">Close</button>
            </div>
        </div>
    </div>
</div>

@include('frontend.partials.footer')
<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterForms = Array.from(document.querySelectorAll('[data-world-events-filter-form]'));
        const modal = document.getElementById('worldEventModal');
        const closeButton = document.getElementById('worldEventModalClose');
        const doneButton = document.getElementById('worldEventModalDone');
        const title = document.getElementById('worldEventModalTitle');
        const date = document.getElementById('worldEventModalDate');
        const location = document.getElementById('worldEventModalLocation');
        const theme = document.getElementById('worldEventModalTheme');
        const website = document.getElementById('worldEventModalWebsite');
        const triggers = Array.from(document.querySelectorAll('[data-world-event-trigger]'));

        filterForms.forEach(function (form) {
            const controls = Array.from(form.querySelectorAll('select'));

            controls.forEach(function (control) {
                control.addEventListener('change', function () {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                        return;
                    }

                    form.submit();
                });
            });
        });

        if (!modal || !closeButton || !doneButton || !title || !date || !location || !theme || !website) {
            return;
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                title.textContent = trigger.getAttribute('data-event-name') || 'Event Details';
                date.textContent = trigger.getAttribute('data-event-date') || '-';
                location.textContent = trigger.getAttribute('data-event-location') || '-';
                theme.textContent = trigger.getAttribute('data-event-theme') || '-';
                const websiteUrl = trigger.getAttribute('data-event-website') || '';
                if (websiteUrl !== '') {
                    website.href = websiteUrl;
                    website.style.display = 'inline-flex';
                } else {
                    website.href = '#';
                    website.style.display = 'none';
                }

                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            });
        });

        [closeButton, doneButton].forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });
    });
</script>
</body>
</html>
