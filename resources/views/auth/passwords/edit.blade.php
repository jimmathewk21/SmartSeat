<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
    @endphp
    <title>My Profile | SmartSeat</title>
    <link rel="icon" href="{{ $siteIconHref }}" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="{{ asset('css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <link id="colors" href="{{ asset('css/colors/scheme-01.css') }}" rel="stylesheet" type="text/css">
    <style>
        body.profile-frontend-page { background:#eef1f8; color:#111; }
        .profile-section { padding:72px 0 48px; }
        .profile-card { background:#fff; border:1px solid #dde3f1; border-radius:14px; box-shadow:0 18px 45px rgba(17, 24, 39, 0.08); overflow:hidden; height:100%; }
        .profile-card-header { background:linear-gradient(90deg, #151a56 0%, #2f2a80 100%); color:#fff; padding:20px 24px; font-size:24px; font-weight:700; }
        .profile-card-body { padding:24px; }
        .profile-card .form-control { border-radius:8px; min-height:48px; border:1px solid #d8deea; }
        .profile-card label { font-weight:600; margin-bottom:7px; }
        @media (max-width:767px) { .profile-section { padding:42px 0 32px; } .profile-card-header { padding:16px 18px; font-size:20px; } .profile-card-body { padding:18px; } }
    </style>
</head>
<body class="profile-frontend-page">
<div id="wrapper">
    @include('frontend.partials.header')
    <section class="profile-section">
        <div class="container">
            @if(session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="profile-card">
                        <div class="profile-card-header">{{ trans('global.my_profile') }}</div>
                        <div class="profile-card-body">
                            <form method="POST" action="{{ route('profile.password.updateProfile') }}">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="required" for="name">{{ trans('cruds.user.fields.name') }}</label>
                                    <input class="form-control" type="text" name="name" id="name" value="{{ auth()->user()->name }}" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="required" for="email">{{ trans('cruds.user.fields.email') }}</label>
                                    <input class="form-control" type="email" name="email" id="email" value="{{ auth()->user()->email }}" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="company_name">Company Name</label>
                                    <input class="form-control {{ $errors->has('company_name') ? 'is-invalid' : '' }}" type="text" name="company_name" id="company_name" value="{{ old('company_name', auth()->user()->company_name) }}">
                                    @if($errors->has('company_name'))<div class="invalid-feedback d-block">{{ $errors->first('company_name') }}</div>@endif
                                </div>
                                <div class="form-group mb-4">
                                    <label for="mobile">{{ trans('cruds.user.fields.mobile') }}</label>
                                    <input class="form-control" type="text" name="mobile" id="mobile" value="{{ auth()->user()->mobile }}" readonly>
                                </div>
                                <button class="btn-main fx-slide" type="submit"><span>{{ trans('global.save') }}</span></button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="profile-card">
                        <div class="profile-card-header">{{ trans('global.change_password') }}</div>
                        <div class="profile-card-body">
                            <form method="POST" action="{{ route('profile.password.update') }}">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="required" for="password">New {{ trans('cruds.user.fields.password') }}</label>
                                    <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" id="password" required>
                                    @if($errors->has('password'))<div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>@endif
                                </div>
                                <div class="form-group mb-4">
                                    <label class="required" for="password_confirmation">Repeat New {{ trans('cruds.user.fields.password') }}</label>
                                    <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" required>
                                </div>
                                <button class="btn-main fx-slide" type="submit"><span>{{ trans('global.save') }}</span></button>
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
