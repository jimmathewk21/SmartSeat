<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
    @endphp
    <title>{{ $frontendSeoTitle ?? 'Forgot Password - ' . trans('panel.site_title') }}</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <link id="colors" href="{{ asset('css/colors/scheme-01.css') }}" rel="stylesheet" type="text/css">
    <style>
        body.auth-frontend-page { background:#eef1f8; color:#111; }
        .auth-section { padding:120px 0 100px; }
        .auth-card { background:#fff; border:1px solid #dde3f1; border-radius:14px; box-shadow:0 18px 45px rgba(17, 24, 39, 0.08); overflow:hidden; }
        .auth-card-header { background:linear-gradient(90deg, #151a56 0%, #2f2a80 100%); color:#fff; padding:22px 26px; }
        .auth-card-header h2 { margin:0; font-size:30px; font-weight:700; color:#fff !important; }
        .auth-card-header p { margin:8px 0 0; opacity:.9; color:#e9eaff !important; }
        .auth-card-body { padding:26px; }
        .auth-card .form-group label { font-weight:600; margin-bottom:7px; }
        .auth-card .form-control { border-radius:8px; height:48px; border:1px solid #d8deea; }
        .auth-link { font-weight:600; color:#6f42f5; text-decoration:none; }
    </style>
</head>
<body class="auth-frontend-page">
<div id="wrapper">
    @include('frontend.partials.header')
    <section class="auth-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="auth-card">
                        <div class="auth-card-header">
                            <h2>Forgot Password</h2>
                            <p>Enter your email address and we will send you a password reset link.</p>
                        </div>
                        <div class="auth-card-body">
                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="email">{{ trans('global.login_email') }}</label>
                                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" required autocomplete="email" autofocus value="{{ old('email') }}">
                                    @if($errors->has('email'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
                                    @endif
                                </div>
                                <button type="submit" class="btn-main fx-slide"><span>{{ trans('global.send_password') }}</span></button>
                                <div class="mt-3">
                                    <a class="auth-link" href="{{ route('login') }}">Back to Login</a>
                                </div>
                            </form>
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
</body>
</html>
