<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $authPageSettings = \App\Support\AuthPageSettings::get();
        $resolvedSiteMeta = $siteMeta ?? \App\Support\SiteMetaSettings::resolveForPath(request()->path() === '/' ? '/' : '/' . ltrim(request()->path(), '/'));
        $siteIcon = $resolvedSiteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $brandImage = $authPageSettings['brand_image_url'] ?? 'images/logo.webp';
        $isAbsoluteBrandImage = str_starts_with($brandImage, 'http://') || str_starts_with($brandImage, 'https://') || str_starts_with($brandImage, '//') || str_starts_with($brandImage, '/');
        $brandImageSrc = $isAbsoluteBrandImage ? $brandImage : asset($brandImage);
        $currentIpAddress = request()->getClientIp() ?: request()->ip() ?: '127.0.0.1';
    @endphp
    <title>{{ $frontendSeoTitle ?? 'Sign In - ' . trans('panel.site_title') }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <link id="colors" href="{{ asset('css/colors/scheme-01.css') }}" rel="stylesheet" type="text/css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
        }
        body.login-frontend-page {
            background: #eef1f8;
            color: #0d0d16;
        }
        body.login-frontend-page #wrapper {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        .login-section {
            position: relative;
            overflow: hidden;
            padding: 126px 0 84px;
            background: #eef1f8;
        }
        .login-map {
            position: absolute;
            top: 110px;
            left: 50%;
            width: min(1280px, 96vw);
            aspect-ratio: 2.1 / 1;
            height: auto;
            transform: translateX(-50%);
            background-image: url('{{ asset('images/worldmap.png') }}');
            background-repeat: no-repeat;
            background-position: center 24px;
            background-size: contain;
            opacity: 0.5;
            filter: grayscale(1) brightness(0.55);
            pointer-events: none;
            z-index: 0;
        }
        .login-shell {
            position: relative;
            z-index: 1;
        }
        .login-layout {
            align-items: stretch;
        }
        .login-info {
            position: relative;
            padding: 28px 20px 24px 0;
            z-index: 1;
        }
        .login-brand {
            max-width: 390px;
            width: 100%;
            display: block;
            margin: 0 0 2px;
        }
        .login-copy {
            max-width: 640px;
            color: #101116;
        }
        .login-copy p {
            margin: 0 0 28px;
            font-size: 17px;
            line-height: 1.22;
            font-weight: 400;
            letter-spacing: -0.02em;
        }
        .login-copy p strong {
            font-weight: 700;
            margin-right: 8px;
        }
        .login-copy p:last-of-type {
            margin-bottom: 0;
        }
        .login-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            max-width: 640px;
        }
        .login-meta.login-meta--single {
            grid-template-columns: minmax(0, 1fr);
            max-width: 340px;
        }
        .login-meta-below {
            max-width: 680px;
        }
        .login-meta-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.2;
            font-weight: 700;
            color: #1f2937;
            text-align: center;
        }
        .login-meta-box {
            min-height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #b8c4de;
            background: rgba(255, 255, 255, 0.82);
            color: #111827;
            font-size: 16px;
            line-height: 1.15;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 10px 20px rgba(19, 28, 61, 0.04);
        }
        .login-card-wrap {
            display: flex;
            justify-content: flex-end;
        }
        .login-card-stack {
            width: 100%;
            max-width: 680px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .login-card {
            width: 100%;
            max-width: 680px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 22px 44px rgba(19, 28, 61, 0.10);
            overflow: hidden;
            border: 1px solid #dbe0ec;
        }
        .login-card-header {
            background: linear-gradient(90deg, #1d2169 0%, #312a88 100%);
            padding: 28px 34px;
            color: #fff;
        }
        .login-card-header h1 {
            margin: 0 0 14px;
            color: #fff !important;
            font-size: 26px;
            line-height: 1;
            font-weight: 700;
            letter-spacing: -0.04em;
        }
        .login-card-header-badge {
            display: block;
            padding: 10px 12px;
            background: rgba(71, 64, 182, 0.92);
            color: #fff;
            font-size: 12px;
            line-height: 1.2;
            font-weight: 500;
            border-radius: 8px;
            margin: 0;
        }
        .login-card-body {
            padding: 18px 16px 16px;
        }
        .login-card .form-group {
            margin-bottom: 14px;
        }
        .login-card .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #131313;
            font-size: 13px;
            line-height: 1.2;
            font-weight: 700;
        }
        .login-card .form-control {
            min-height: 38px;
            padding: 7px 12px;
            border-radius: 8px;
            border: 1px solid #d5dced;
            background: #fff;
            color: #111;
            font-size: 14px;
            line-height: 1.2;
            box-shadow: none;
        }
        .login-card .form-control:focus {
            border-color: #7a56ff;
            box-shadow: 0 0 0 0.15rem rgba(111, 66, 245, 0.18);
            background: #f1f5ff;
        }
        .login-password-wrap {
            position: relative;
        }
        .login-password-wrap .form-control {
            padding-right: 44px;
        }
        .login-password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #6a7285;
            padding: 0;
            line-height: 1;
            cursor: pointer;
        }
        .login-password-toggle:hover,
        .login-password-toggle:focus {
            color: #383f52;
            outline: none;
        }
        .login-card .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 4px 0 18px;
            padding-left: 0;
        }
        .login-card .form-check-input {
            width: 22px;
            height: 22px;
            margin: 0;
            border-radius: 5px;
            border-color: #cfd6ea;
        }
        .login-card .form-check-label {
            margin: 0;
            color: #151515;
            font-size: 14px;
            line-height: 1.2;
            font-weight: 500;
        }
        .login-form-footer {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .login-submit {
            min-width: 178px;
            justify-content: center;
        }
        .login-links {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
        }
        .login-link {
            color: #7047f6;
            font-size: 13px;
            line-height: 1.2;
            font-weight: 600;
            text-decoration: none;
        }
        .login-link:hover {
            color: #5d36db;
        }
        .login-card .invalid-feedback {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .login-frontend-page .success-modal .modal-content {
            border: 0;
            border-radius: 14px;
            overflow: hidden;
        }
        .login-frontend-page .success-modal .modal-header {
            background: #141a3a;
            border-bottom: 0;
            padding: 16px 20px;
        }
        .login-frontend-page .success-modal .modal-title {
            color: #fff;
            font-weight: 700;
        }
        .login-frontend-page .success-modal .btn-close {
            filter: invert(1);
            opacity: .85;
        }
        .login-frontend-page .success-modal .modal-body {
            padding: 24px 20px;
            background: #f7f8fc;
            color: #111;
            font-size: 16px;
            line-height: 1.75;
        }
        @media (max-width: 1399px) {
            .login-copy p {
                font-size: 17px;
            }
        }
        @media (max-width: 1199px) {
            .login-section {
                padding: 118px 0 86px;
            }
            .login-layout {
                gap: 28px 0;
            }
            .login-info {
                padding: 12px 0 6px;
            }
            .login-card-wrap {
                justify-content: flex-start;
            }
            .login-card {
                max-width: none;
            }
            .login-card-stack {
                max-width: none;
            }
        }
        @media (max-width: 991px) {
            .login-section {
                padding: 108px 0 72px;
            }
            .login-info {
                padding: 0;
            }
            .login-brand {
                max-width: 320px;
                margin: 0 auto 26px;
            }
            .login-copy {
                max-width: none;
                text-align: center;
            }
            .login-copy p {
                font-size: 16px;
                margin-bottom: 22px;
            }
            .login-meta {
                max-width: none;
                gap: 14px;
            }
            .login-card-header {
                padding: 20px 18px 0;
            }
            .login-card-header h1 {
                font-size: 26px;
            }
            .login-card-header-badge {
                font-size: 12px;
            }
            .login-card-body {
                padding: 18px;
            }
        }
        @media (max-width: 767px) {
            .login-section {
                padding: 98px 0 60px;
            }
            .login-map {
                background-position: center 30%;
                background-size: 172vw auto;
                opacity: 0.16;
            }
            .login-copy {
                text-align: left;
            }
            .login-copy p {
                font-size: 15px;
                line-height: 1.28;
                margin-bottom: 18px;
            }
            .login-meta {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .login-meta-label {
                margin-bottom: 6px;
                text-align: center;
                font-size: 13px;
            }
            .login-meta-box {
                min-height: 52px;
                font-size: 15px;
                border-radius: 10px;
            }
            .login-card-header h1 {
                font-size: 24px;
            }
            .login-card-header-badge {
                font-size: 13px;
            }
            .login-card .form-group label,
            .login-card .form-check-label,
            .login-link {
                font-size: 14px;
            }
            .login-card .form-control {
                min-height: 44px;
                font-size: 14px;
                border-radius: 12px;
            }
            .login-form-footer {
                align-items: stretch;
            }
            .login-submit {
                width: 100%;
            }
            .login-links {
                width: 100%;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body class="login-frontend-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="login-section">
        <div class="login-map"></div>
        <div class="container login-shell">
            <div class="row login-layout">
                <div class="col-xl-6 col-lg-6">
                    <div class="login-info">
                        <img src="{{ $brandImageSrc }}" alt="{{ trans('panel.site_title') }}" class="login-brand">

                        <div class="login-copy">
                            @foreach(preg_split("/\\r\\n\\r\\n|\\n\\n|\\r\\r/", trim((string) ($authPageSettings['login_left_content'] ?? ''))) as $paragraph)
                                @if(trim($paragraph) !== '')
                                    <p>{{ trim($paragraph) }}</p>
                                @endif
                            @endforeach
                        </div>

                        <div class="login-meta login-meta-below mt-4 login-meta--single">
                            <div class="login-meta-item">
                                <span class="login-meta-label">{{ $authPageSettings['ip_label'] }}</span>
                                <div class="login-meta-box">{{ $currentIpAddress }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-lg-6">
                    <div class="login-card-wrap">
                        <div class="login-card-stack">
                        <div class="login-card">
                            <div class="login-card-header">
                                <h1>{{ $authPageSettings['login_form_title'] }}</h1>
                                <span class="login-card-header-badge">{{ $authPageSettings['login_form_subtitle'] }}</span>
                            </div>
                            <div class="login-card-body">
                                @if(session('message'))
                                    <div class="alert alert-info" role="alert">
                                        {{ session('message') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <div class="form-group">
                                        <label for="email">{{ trans('global.login_email') }}</label>
                                        <input id="email" name="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required autocomplete="email" autofocus value="{{ old('email', session('email_prefill')) }}">
                                        @if($errors->has('email'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('email') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label for="password">{{ trans('global.login_password') }}</label>
                                        <div class="login-password-wrap">
                                            <input id="password" name="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required>
                                            <button type="button" class="login-password-toggle js-password-toggle" data-target="password" aria-label="Show password">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        @if($errors->has('password'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('password') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input" name="remember" type="checkbox" id="remember">
                                        <label class="form-check-label" for="remember">
                                            {{ trans('global.remember_me') }}
                                        </label>
                                    </div>

                                    <div class="login-form-footer">
                                        <button type="submit" class="btn-main fx-slide login-submit">
                                            <span>{{ $authPageSettings['login_button_text'] }}</span>
                                        </button>

                                        <div class="login-links">
                                            @if(Route::has('password.request'))
                                                <a class="login-link" href="{{ route('password.request') }}">
                                                    {{ trans('global.forgot_password') }}
                                                </a>
                                            @endif
                                            @if(Route::has('register'))
                                                <a class="login-link" href="{{ route('register') }}">
                                                    Sign Up
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@include('frontend.partials.footer')
<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
@if(session('pending_review_message'))
    <div class="modal fade success-modal" id="pendingReviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ session('auth_notice_title', 'Pending Approval') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ session('pending_review_message') }}
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.bootstrap && document.getElementById('pendingReviewModal')) {
                new bootstrap.Modal(document.getElementById('pendingReviewModal')).show();
            }
        });
    </script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggleButtons = document.querySelectorAll('.js-password-toggle');

        toggleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);
                var icon = this.querySelector('i');

                if (!input || !icon) {
                    return;
                }

                var isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
                this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        });
    });
</script>
</body>
</html>
