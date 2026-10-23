<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $authPageSettings = \App\Support\AuthPageSettings::get();
        $resolvedSiteMeta = $siteMeta ?? \App\Support\SiteMetaSettings::resolveForPath(request()->path() === '/' ? '/' : '/' . ltrim(request()->path(), '/'));
        $siteIcon = $resolvedSiteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $currentIpAddress = request()->getClientIp() ?: request()->ip() ?: '127.0.0.1';
    @endphp
    <title>Register | SmartSeat</title>
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
        body.signup-frontend-page {
            background: #eef1f8;
            color: #0d0d16;
        }
        body.signup-frontend-page #wrapper {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
            position: relative;
            z-index: 1;
        }
        .signup-section {
            position: relative;
            overflow: hidden;
            padding: 126px 0 84px;
            background: #eef1f8;
        }
        .signup-map {
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
        .signup-shell {
            position: relative;
            z-index: 1;
        }
        .signup-layout {
            align-items: stretch;
        }
        .signup-info {
            position: relative;
            padding: 28px 20px 24px 0;
            z-index: 1;
        }
        .signup-brand { display:block; margin:0 0 12px; color:#103e38; font-size:42px; font-weight:800; letter-spacing:-.04em; line-height:1.1; }
        .signup-copy {
            max-width: 640px;
            color: #101116;
        }
        .signup-copy-text {
            margin: 0 0 28px;
            font-size: 17px;
            line-height: 1.22;
            font-weight: 400;
            letter-spacing: -0.02em;
        }
        .signup-copy-block {
            margin-bottom: 28px;
        }
        .signup-note-banner {
            display: inline-block;
            max-width: 560px;
            padding: 8px 10px 7px;
            background: #292b86;
            color: #fff;
            font-size: 13px;
            line-height: 1.1;
            font-weight: 500;
            border-radius: 9px;
        }
        .signup-note-banner strong {
            font-weight: 800;
        }
        .signup-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 26px;
            max-width: 640px;
        }
        .signup-meta-label {
            display: block;
            margin-bottom: 12px;
            font-size: 17px;
            line-height: 1.2;
            font-weight: 700;
            color: #151515;
            text-align: center;
        }
        .signup-meta-box {
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 20px;
            border-radius: 18px;
            border: 1px solid #8f98b3;
            background: rgba(255, 255, 255, 0.68);
            color: #111318;
            font-size: 20px;
            line-height: 1.15;
            font-weight: 700;
            text-align: center;
        }
        .signup-card-wrap {
            display: flex;
            justify-content: flex-end;
        }
        .signup-card {
            width: 100%;
            max-width: 680px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 22px 44px rgba(19, 28, 61, 0.10);
            overflow: hidden;
            border: 1px solid #dbe0ec;
        }
        .signup-card-header {
            background: linear-gradient(90deg, #1d2169 0%, #312a88 100%);
            padding: 28px 34px;
            color: #fff;
        }
        .signup-card-header h1 {
            margin: 0 0 14px;
            color: #fff !important;
            font-size: 26px;
            line-height: 1;
            font-weight: 700;
            letter-spacing: -0.04em;
        }
        .signup-card-header-badge {
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
        .signup-card-body {
            padding: 18px 16px 16px;
        }
        .signup-card .form-group {
            margin-bottom: 14px;
        }
        .signup-card .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #131313;
            font-size: 13px;
            line-height: 1.2;
            font-weight: 700;
        }
        .signup-card .form-control {
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
        .signup-card .form-control:focus {
            border-color: #7a56ff;
            box-shadow: 0 0 0 0.15rem rgba(111, 66, 245, 0.18);
            background: #f1f5ff;
        }
        .signup-password-wrap {
            position: relative;
        }
        .signup-password-wrap .form-control {
            padding-right: 44px;
        }
        .signup-password-toggle {
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
        .signup-password-toggle:hover,
        .signup-password-toggle:focus {
            color: #383f52;
            outline: none;
        }
        .signup-card .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 4px 0 18px;
        }
        .signup-card .form-check-input {
            width: 22px;
            height: 22px;
            margin: 0;
            border-radius: 5px;
            border-color: #cfd6ea;
        }
        .signup-card .form-check-label {
            margin: 0;
            color: #151515;
            font-size: 14px;
            line-height: 1.2;
            font-weight: 500;
        }
        .signup-captcha {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            padding: 7px 12px;
            border-radius: 8px;
            border: 1px solid #d5dced;
            background: #edf3ff;
            color: #151a56;
            font-size: 14px;
            line-height: 1.1;
            font-weight: 700;
            width: 100%;
        }
        .signup-form-footer {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .signup-submit {
            min-width: 90px;
            min-height: 34px;
            justify-content: center;
        }
        .signup-links {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
        }
        .signup-link {
            color: #7047f6;
            font-size: 13px;
            line-height: 1.2;
            font-weight: 600;
            text-decoration: none;
        }
        .signup-link:hover {
            color: #5d36db;
        }
        .signup-card .invalid-feedback {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        @media (max-width: 1399px) {
            .signup-copy-text {
                font-size: 17px;
            }
        }
        @media (max-width: 1199px) {
            .signup-section {
                padding: 118px 0 86px;
            }
            .signup-layout {
                gap: 28px 0;
            }
            .signup-info {
                padding: 12px 0 6px;
            }
            .signup-copy {
                max-width: none;
            }
            .signup-copy-text { font-size: 18px; }
            .signup-card-wrap {
                justify-content: flex-start;
            }
            .signup-card {
                max-width: none;
            }
        }
        @media (max-width: 991px) {
            .signup-section {
                padding: 108px 0 72px;
            }
            .signup-info {
                padding: 0;
            }
            .signup-brand {
                max-width: 320px;
                margin: 0 auto 26px;
            }
            .signup-copy {
                max-width: none;
                text-align: center;
            }
            .signup-copy-text { font-size: 16px; margin-bottom: 22px; }
            .signup-card-header {
                padding: 20px 18px 0;
            }
            .signup-card-body {
                padding: 18px;
            }
        }
        @media (max-width: 767px) {
            .signup-section {
                padding: 98px 0 60px;
            }
            .signup-map {
                top: 138px;
                width: 112vw;
                aspect-ratio: 1.9 / 1;
                opacity: 0.10;
            }
            .signup-copy {
                text-align: left;
            }
            .signup-copy-text { font-size: 15px; line-height: 1.28; margin-bottom: 18px; }
            .signup-card-header h1 {
                font-size: 24px;
            }
            .signup-card-header-badge {
                font-size: 13px;
            }
            .signup-card .form-group label,
            .signup-card .form-check-label,
            .signup-link {
                font-size: 14px;
            }
            .signup-card .form-control,
            .signup-captcha {
                min-height: 44px;
                font-size: 14px;
                border-radius: 12px;
            }
            .signup-form-footer {
                align-items: stretch;
            }
            .signup-submit {
                width: 100%;
            }
            .signup-links {
                width: 100%;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body class="signup-frontend-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="signup-section">
        <div class="signup-map"></div>
        <div class="container signup-shell">
            <div class="row signup-layout">
                <div class="col-xl-6 col-lg-6">
                    <div class="signup-info">
                        <div class="signup-brand">SmartSeat</div>

                        <div class="signup-copy">
                            <div class="signup-copy-block">
                                <p class="signup-copy-text">Create a SmartSeat account to manage your railway travel details.</p>
                            </div>

                            <div class="signup-note-banner">
                                <strong>NOTE:</strong> Some account features may require review.
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-xl-6 col-lg-6">
                    <div class="signup-card-wrap">
                        <div class="signup-card">
                            <div class="signup-card-header">
                                <h1>Create your account</h1>
                                <span class="signup-card-header-badge">Join SmartSeat to manage your account.</span>
                            </div>
                            <div class="signup-card-body">
                                <form method="POST" action="{{ route('register') }}">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="name">Full Name</label>
                                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                            @error('name')
                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="company_name">Company Name</label>
                                            <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name') }}" required autocomplete="organization">
                                            @error('company_name')
                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="email">Official Email ID</label>
                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="mobile">Mobile Number</label>
                                            <input id="mobile" type="text" class="form-control @error('mobile') is-invalid @enderror" name="mobile" value="{{ old('mobile') }}" required autocomplete="tel">
                                            @error('mobile')
                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="password">{{ __('Password') }}</label>
                                            <div class="signup-password-wrap">
                                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                                <button type="button" class="signup-password-toggle js-password-toggle" data-target="password" aria-label="Show password">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <small class="form-text text-muted">Password must be at least 8 characters and match the confirmation field.</small>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="password-confirm">{{ __('Confirm Password') }}</label>
                                            <div class="signup-password-wrap">
                                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                                <button type="button" class="signup-password-toggle js-password-toggle" data-target="password-confirm" aria-label="Show password">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="captcha">Captcha</label>
                                            <div class="signup-captcha mb-2">
                                                {{ $captchaQuestion ?? session('register_captcha_question') }}
                                            </div>
                                            <input id="captcha" type="text" class="form-control @error('captcha') is-invalid @enderror" name="captcha" required>
                                            @error('captcha')
                                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="signup-form-footer">
                                        <button type="submit" class="btn-main fx-slide signup-submit">
                                            <span>Register</span>
                                        </button>

                                        <div class="signup-links">
                                            <a class="signup-link" href="{{ route('login') }}">Already have an account? Sign In</a>
                                        </div>
                                    </div>
                                </form>
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
