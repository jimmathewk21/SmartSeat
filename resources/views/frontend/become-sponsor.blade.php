<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'Become A Sponsor';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $sponsorSettings = $sponsorPageSettings ?? [];
        $yesNoOptions = ['Yes' => 'Yes', 'No' => 'No'];
        $sponsorshipCategoryOptions = ['Platinum Sponsor', 'Gold Sponsor', 'Silver Sponsor', 'Exhibit Sponsor', 'Gift Sponsor'];
        $budgetOptions = ['USD 499 ~ USD 999', 'USD 999 ~ USD 1999', 'USD 1999 ~ USD 2999', 'USD 2999 ~ USD 4999', 'USD 4999 ~ USD 9999'];
        $a4AdvertOptions = ['One (1) Page A4 Advert', 'Two (2) Page A4 Advert', 'None'];
        $registrationOptions = ['1 Registration', '2 Registrations', '3 Registrations', '4 Registrations', 'More than 5 Registrations'];
        $linkedinPostOptions = ['1 to 3 posts', '3 to 5 posts', 'None'];
        $rollupStandeeOptions = ['One (1)', 'Two (2)', 'Not required'];
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
        body.become-sponsor-page { background: #f4f5f8; color: #111; }
        body.become-sponsor-page .inside-page-hero-headline { color: #000 !important; -webkit-text-fill-color: #000 !important; background: none !important; text-shadow: none !important; opacity: 1 !important; margin-bottom: 10px; line-height: 1.1; }
        body.become-sponsor-page .inside-page-hero-main { color: #000; font-size: 42px; font-weight: 700; line-height: 1; }
        body.become-sponsor-page .inside-page-hero-headline small { color: #000; font-size: .6em; font-weight: 400; }
        body.become-sponsor-page .inside-page-hero-description { font-size: 18px; line-height: 1.8; color: #111; margin-bottom: 12px; max-width: 1100px; }
        .sponsor-apply-page { position: relative; overflow: hidden; }
        .sponsor-apply-map { position: absolute; top: 140px; left: 50%; width: min(1411px, 100vw); aspect-ratio: 1411 / 702; transform: translateX(-50%); background: url('{{ asset('images/worldmap.png') }}') no-repeat center 28px / contain; opacity: .40; pointer-events: none; z-index: 0; }
        .sponsor-apply-page > .container { position: relative; z-index: 1; }
        .sponsor-apply-shell { max-width: 1080px; margin: 0 auto; }
        .sponsor-apply-layout { display: grid; gap: 18px; }
        .sponsor-form-section { border: 1.5px solid rgba(33, 37, 41, .55); border-radius: 22px; background: rgba(255,255,255,.72); backdrop-filter: blur(2px); padding: 18px 18px 20px; box-shadow: 0 14px 30px rgba(19,25,47,.05); }
        .sponsor-form-section-title { margin: 0 0 14px; font-size: 29px; line-height: 1.05; font-weight: 800; text-transform: uppercase; color: #151515; }
        .sponsor-form-grid, .sponsor-benefits-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 18px; }
        .sponsor-form-grid--details { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .sponsor-benefits-grid { gap: 16px; align-items: stretch; }
        .sponsor-bottom-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 14px; }
        .sponsor-captcha-row { display: inline-flex; align-items: stretch; gap: 12px; flex-wrap: nowrap; max-width: 100%; }
        .sponsor-captcha-answer { width: 180px; max-width: 100%; min-height: 48px; }
        .sponsor-form-field { min-width: 0; }
        .sponsor-form-field.full { grid-column: 1 / -1; }
        .sponsor-form-label { display: block; margin-bottom: 7px; font-size: 13px; font-weight: 700; color: #222; }
        .sponsor-form-label small { font-weight: 500; color: #626b7d; }
        .sponsor-form-input, .sponsor-form-select, .sponsor-form-textarea { width: 100%; border: 1px solid #c9d0dd; border-radius: 11px; background: #fff; color: #111; font-size: 15px; line-height: 1.25; padding: 12px 14px; min-height: 48px; box-shadow: none; }
        .sponsor-form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, #525971 50%), linear-gradient(135deg, #525971 50%, transparent 50%);
            background-position: calc(100% - 20px) calc(50% - 3px), calc(100% - 14px) calc(50% - 3px);
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
            padding-right: 42px;
        }
        .sponsor-form-textarea { min-height: 140px; resize: vertical; }
        .sponsor-form-input:focus, .sponsor-form-select:focus, .sponsor-form-textarea:focus { outline: none; border-color: #7a52f4; box-shadow: 0 0 0 3px rgba(122,82,244,.12); }
        .sponsor-form-input.is-invalid, .sponsor-form-select.is-invalid, .sponsor-form-textarea.is-invalid { border-color: #d91f1f; box-shadow: 0 0 0 3px rgba(217,31,31,.10); background-color: #fff8f8; }
        .sponsor-form-grid--details .sponsor-form-field { padding: 14px 16px 16px; border: 1px solid #d8deef; border-radius: 16px; background: rgba(255,255,255,.94); box-shadow: 0 8px 20px rgba(19,25,47,.05); }
        .sponsor-form-grid--details .sponsor-form-field.full { padding: 0; border: 0; background: transparent; box-shadow: none; }
        .sponsor-form-grid--details .sponsor-form-label { margin-bottom: 9px; font-size: 14px; line-height: 1.45; }
        .sponsor-section-intro { margin: -2px 0 0; font-size: 14px; line-height: 1.55; color: #5d6578; }
        .sponsor-inline-select { margin-top: 12px; }
        .sponsor-benefit-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100%;
            padding: 14px 16px;
            border: 1px solid #d8deef;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 8px 22px rgba(19, 25, 47, 0.05);
        }
        .sponsor-benefit-card--select {
            background: linear-gradient(180deg, rgba(248, 247, 255, 0.98), rgba(255, 255, 255, 0.95));
        }
        .sponsor-benefit-head {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .sponsor-benefit-check {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            flex: 0 0 auto;
        }
        .sponsor-benefit-title {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 700;
            color: #171717;
        }
        .sponsor-benefit-meta {
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.45;
            color: #6a7285;
        }
        .sponsor-checkbox, .sponsor-consent { display: flex; align-items: flex-start; gap: 12px; font-size: 15px; line-height: 1.55; color: #151515; }
        .sponsor-checkbox input, .sponsor-consent input { width: 18px; height: 18px; margin-top: 3px; flex: 0 0 auto; }
        .sponsor-upload-shell { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: center; }
        .sponsor-upload-button { display: inline-flex; align-items: center; justify-content: center; min-width: 124px; height: 44px; border: 0; border-radius: 999px; background: linear-gradient(135deg, #8255ff, #6545f3); color: #fff; font-size: 13px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; cursor: pointer; }
        .sponsor-upload-native { display: none; }
        .sponsor-upload-note, .sponsor-form-hint { margin-top: 6px; font-size: 12px; color: #687082; }
        .sponsor-form-hint { color: #d91f1f; font-size: 12px; line-height: 1.45; }
        .sponsor-required-asterisk { color: #d91f1f; font-weight: 800; }
        .sponsor-required-error {
            margin-top: 6px;
            color: #d91f1f;
            font-size: 12px;
            line-height: 1.4;
            font-weight: 600;
        }
        .sponsor-captcha { display: inline-flex; align-items: center; width: auto; min-width: 180px; max-width: 100%; border: 1px solid #c9d0dd; border-radius: 11px; background: #f4f4ff; color: #2c2f48; font-size: 18px; font-weight: 700; line-height: 1.2; padding: 14px 16px; min-height: 48px; }
        .sponsor-consent-wrap { padding: 0 2px; }
        .sponsor-consent { margin-bottom: 10px; font-weight: 700; color: #1a1a1a; }
        .sponsor-consent.is-invalid-consent { padding: 10px 12px; border: 1px solid #d91f1f; border-radius: 12px; background: #fff8f8; }
        .sponsor-submit-row { display: flex; justify-content: center; margin-top: 6px; }
        .sponsor-submit-btn { min-width: 220px; border: 0; border-radius: 12px; background: linear-gradient(135deg, #7d52ff, #5c42ef); color: #fff; font-size: 18px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; padding: 13px 28px; cursor: pointer; box-shadow: 0 16px 26px rgba(95,66,239,.22); }
        .sponsor-alert { border-radius: 16px; margin-bottom: 22px; }
        .become-sponsor-page .success-modal .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
        .become-sponsor-page .success-modal .modal-header { background: #141a3a; border-bottom: 0; padding: 16px 20px; }
        .become-sponsor-page .success-modal .modal-title { color: #fff; font-weight: 700; }
        .become-sponsor-page .success-modal .btn-close { filter: invert(1); opacity: .85; }
        .become-sponsor-page .success-modal .modal-body { padding: 24px 20px; background: #f7f8fc; color: #111; font-size: 16px; line-height: 1.75; }
        @media (max-width: 991.98px) { .sponsor-apply-map { top: 160px; width: 118vw; opacity: .18; } .sponsor-form-section-title { font-size: 24px; } }
        @media (max-width: 767.98px) { .sponsor-form-section { padding: 16px 14px 18px; border-radius: 18px; } .sponsor-form-grid, .sponsor-benefits-grid { grid-template-columns: minmax(0, 1fr); gap: 12px; } .sponsor-upload-shell { grid-template-columns: minmax(0, 1fr); } .sponsor-upload-button, .sponsor-submit-btn { width: 100%; } .sponsor-benefit-card { padding: 13px 14px; } .sponsor-captcha-row { display: flex; flex-wrap: wrap; width: 100%; } .sponsor-captcha-answer { width: 100%; } }
    </style>
</head>
<body class="become-sponsor-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80 sponsor-apply-page">
        <div class="sponsor-apply-map" aria-hidden="true"></div>
        <div class="container">
            <div class="sponsor-apply-shell">
                @include('frontend.partials.inner-page-hero', [
                    'heroLabel' => $sponsorSettings['apply_page_label'] ?? '[Sponsor Request]',
                    'heroTitle' => $sponsorSettings['apply_page_title'] ?? 'SPONSOR FORM',
                    'heroSuffix' => $sponsorSettings['apply_page_title_suffix'] ?? 'Become A Sponsor',
                    'heroDescription' => $sponsorSettings['apply_page_description'] ?? 'Thank you for your interest in sponsoring our conference. Please complete the form below with accurate details. Our sponsorship team will review your submission and contact you shortly.',
                    'heroButtons' => [],
                ])

                @if($errors->any())
                    <div class="alert alert-danger sponsor-alert">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('frontend.become-sponsor.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="sponsor-apply-layout">
                        <div class="sponsor-form-section">
                            <h2 class="sponsor-form-section-title">Basic Details:</h2>
                            <div class="sponsor-form-grid">
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="name">Enter Your Full Name*</label><input id="name" type="text" name="name" class="sponsor-form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter Your Full Name*" required>@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="job_title">Enter Your Job Title</label><input id="job_title" type="text" name="job_title" class="sponsor-form-input @error('job_title') is-invalid @enderror" value="{{ old('job_title') }}" placeholder="Enter Your Job Title">@error('job_title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="company_name">Enter Your Company Name*</label><input id="company_name" type="text" name="company_name" class="sponsor-form-input @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" placeholder="Enter Your Company Name*" required>@error('company_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="country">Country <small>(Select with search box)</small></label><input list="sponsor-country-list" id="country" type="text" name="country" class="sponsor-form-input @error('country') is-invalid @enderror" value="{{ old('country') }}" placeholder="Country (Select with search box)"><datalist id="sponsor-country-list">@foreach($countries as $country)<option value="{{ $country }}"></option>@endforeach</datalist>@error('country')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="email">Official Email-id*</label><input id="email" type="email" name="email" class="sponsor-form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Official Email-id*" required>@error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="phone">Mobile Number</label><input id="phone" type="text" name="phone" class="sponsor-form-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Mobile Number">@error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="linkedin_url">LinkedIn Url</label><input id="linkedin_url" type="url" name="linkedin_url" class="sponsor-form-input @error('linkedin_url') is-invalid @enderror" value="{{ old('linkedin_url') }}" placeholder="LinkedIn Url">@error('linkedin_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="facebook_url">Facebook Url</label><input id="facebook_url" type="url" name="facebook_url" class="sponsor-form-input @error('facebook_url') is-invalid @enderror" value="{{ old('facebook_url') }}" placeholder="Facebook Url">@error('facebook_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="website_url">Company Website</label><input id="website_url" type="url" name="website_url" class="sponsor-form-input @error('website_url') is-invalid @enderror" value="{{ old('website_url') }}" placeholder="Company Website">@error('website_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field"><label class="sponsor-form-label" for="company_logo_file">Company Logo</label><div class="sponsor-upload-shell"><input id="company_logo_file_name" type="text" class="sponsor-form-input" value="{{ old('company_logo_file_name', 'No file chosen') }}" readonly><label class="sponsor-upload-button" for="company_logo_file">Upload</label><input id="company_logo_file" class="sponsor-upload-native" type="file" name="company_logo_file" accept=".jpg,.jpeg,.png,.webp,.svg"></div><div class="sponsor-upload-note">Accepted formats: JPG, PNG, WEBP, SVG</div>@error('company_logo_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field full"><label class="sponsor-form-label" for="company_profile">Company Profile <small>(within 300 words)</small></label><textarea id="company_profile" name="company_profile" class="sponsor-form-textarea @error('company_profile') is-invalid @enderror" rows="5" placeholder="Company Profile (within 300 words)">{{ old('company_profile') }}</textarea>@error('company_profile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                            </div>
                        </div>

                        <div class="sponsor-form-section">
                            <h2 class="sponsor-form-section-title">Sponsorship Details:</h2>
                            <div class="sponsor-form-grid sponsor-form-grid--details">
                                <div class="sponsor-form-field full">
                                    <label class="sponsor-form-label">Select the benefits you would like to add in your sponsorship pack*</label>
                                    <p class="sponsor-section-intro">Select the benefits to include in your sponsorship package based on your needs and budget. The sponsorship fee will vary with inclusions selected, ranging from USD 499 to USD 9,999.  </p>
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="sponsorship_categories">Select Sponsorship Categories</label>
                                    <select id="sponsorship_categories" name="sponsorship_categories[]" class="sponsor-form-select @error('sponsorship_categories') is-invalid @enderror" required>
                                        <option value="">Select Sponsorship Category</option>
                                        @foreach($sponsorshipCategoryOptions as $categoryOption)
                                            <option value="{{ $categoryOption }}" @selected(in_array($categoryOption, old('sponsorship_categories', []), true))>{{ $categoryOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('sponsorship_categories')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="budget_range">Enter Your Budget</label>
                                    <select id="budget_range" name="budget_range" class="sponsor-form-select @error('budget_range') is-invalid @enderror" required>
                                        <option value="">Select Budget Range</option>
                                        @foreach($budgetOptions as $budgetOption)
                                            <option value="{{ $budgetOption }}" @selected(old('budget_range') === $budgetOption)>{{ $budgetOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('budget_range')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="benefit_logo_placement">Would you like to place your company’s logo on our conference website and brochure as our sponsor?</label>
                                    <select id="benefit_logo_placement" name="benefit_logo_placement" class="sponsor-form-select @error('benefit_logo_placement') is-invalid @enderror" required>
                                        <option value="">Select Yes or No</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('benefit_logo_placement') === $value || old('benefit_prominent_logo') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('benefit_logo_placement')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="benefit_backdrop_logo">Would you like to place your company’s logo in one of the conference backdrops?</label>
                                    <select id="benefit_backdrop_logo" name="benefit_backdrop_logo" class="sponsor-form-select @error('benefit_backdrop_logo') is-invalid @enderror" required>
                                        <option value="">Select Yes or No</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('benefit_backdrop_logo') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('benefit_backdrop_logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="benefit_participant_list">Would you like to receive the list of participants (without contact details) 14 days before the event?</label>
                                    <select id="benefit_participant_list" name="benefit_participant_list" class="sponsor-form-select @error('benefit_participant_list') is-invalid @enderror" required>
                                        <option value="">Select Yes or No</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('benefit_participant_list') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('benefit_participant_list')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="benefit_promotional_materials">Would you like to distribute any promotional materials of your company such as flyers, gifts, souvenir during the conference?</label>
                                    <select id="benefit_promotional_materials" name="benefit_promotional_materials" class="sponsor-form-select @error('benefit_promotional_materials') is-invalid @enderror" required>
                                        <option value="">Select Yes or No</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('benefit_promotional_materials') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('benefit_promotional_materials')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="a4_advert_option">Would you like to publish an A4 size advertisement of your company inside the conference brochure?</label>
                                    <select id="a4_advert_option" name="a4_advert_option" class="sponsor-form-select @error('a4_advert_option') is-invalid @enderror" required>
                                        <option value="">Select Option</option>
                                        @foreach($a4AdvertOptions as $advertOption)
                                            <option value="{{ $advertOption }}" @selected(old('a4_advert_option') === $advertOption)>{{ $advertOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('a4_advert_option')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="total_registrations_required">How many registrations do you require for your company to participate in the conference?</label>
                                    <select id="total_registrations_required" name="total_registrations_required" class="sponsor-form-select @error('total_registrations_required') is-invalid @enderror" required>
                                        <option value="">Select Option</option>
                                        @foreach($registrationOptions as $registrationOption)
                                            <option value="{{ $registrationOption }}" @selected(old('total_registrations_required') === $registrationOption)>{{ $registrationOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('total_registrations_required')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="benefit_social_media_posts">Would you like us to promote your company on LinkedIn by sharing posts?</label>
                                    <select id="benefit_social_media_posts" name="benefit_social_media_posts" class="sponsor-form-select @error('benefit_social_media_posts') is-invalid @enderror" required>
                                        <option value="">Select Option</option>
                                        @foreach($linkedinPostOptions as $linkedinPostOption)
                                            <option value="{{ $linkedinPostOption }}" @selected(old('benefit_social_media_posts') === $linkedinPostOption)>{{ $linkedinPostOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('benefit_social_media_posts')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="benefit_speaking_slot">Would you like to have a speaking slot in one of the available panel discussions?</label>
                                    <select id="benefit_speaking_slot" name="benefit_speaking_slot" class="sponsor-form-select @error('benefit_speaking_slot') is-invalid @enderror" required>
                                        <option value="">Select Yes or No</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('benefit_speaking_slot') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('benefit_speaking_slot')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="benefit_exhibit_space">Would you like to book an exhibition space at the event?</label>
                                    <select id="benefit_exhibit_space" name="benefit_exhibit_space" class="sponsor-form-select @error('benefit_exhibit_space') is-invalid @enderror" required>
                                        <option value="">Select Yes or No</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('benefit_exhibit_space') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('benefit_exhibit_space')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="sponsor-form-field">
                                    <label class="sponsor-form-label" for="rollup_standees_option">Would you like to place one or two roll-up standees during the conference?</label>
                                    <select id="rollup_standees_option" name="rollup_standees_option" class="sponsor-form-select @error('rollup_standees_option') is-invalid @enderror" required>
                                        <option value="">Select Option</option>
                                        @foreach($rollupStandeeOptions as $rollupStandeeOption)
                                            <option value="{{ $rollupStandeeOption }}" @selected(old('rollup_standees_option') === $rollupStandeeOption)>{{ $rollupStandeeOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('rollup_standees_option')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="sponsor-form-section">
                            <div class="sponsor-bottom-grid">
                                <div class="sponsor-form-field full"><label class="sponsor-form-label" for="message">Additional Note</label><textarea id="message" name="message" class="sponsor-form-textarea @error('message') is-invalid @enderror" rows="4" placeholder="Any message for the sponsorship team.">{{ old('message') }}</textarea>@error('message')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                                <div class="sponsor-form-field full"><label class="sponsor-form-label" for="captcha">Captcha</label><div class="sponsor-captcha-row"><div class="sponsor-captcha">{{ $question ?? session('sponsor_captcha_question') }}</div><input id="captcha" type="text" name="captcha" class="sponsor-form-input sponsor-captcha-answer @error('captcha') is-invalid @enderror" value="{{ old('captcha') }}" required></div>@error('captcha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                            </div>
                        </div>

                        <div class="sponsor-consent-wrap">
                            <label class="sponsor-consent"><input type="checkbox" name="consent_accuracy" value="1" @checked(old('consent_accuracy')) required><span>I confirm that all information submitted is true and accurate.</span></label>
                            <label class="sponsor-consent"><input type="checkbox" name="consent_approval_required" value="1" @checked(old('consent_approval_required')) required><span>I understand that sponsorship confirmation is subject to approval.</span></label>
                        </div>

                        <div class="sponsor-submit-row"><button type="submit" class="sponsor-submit-btn">Submit</button></div>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>

@include('frontend.partials.footer')

@if(session('sponsor_success_message'))
    <div class="modal fade success-modal" id="becomeSponsorSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sponsor Request Submitted</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">{{ session('sponsor_success_message') }}</div>
            </div>
        </div>
    </div>
@endif

<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script>
    (function () {
        var form = document.querySelector('form[action="{{ route('frontend.become-sponsor.store') }}"]');
        var logoInput = document.getElementById('company_logo_file');
        var logoName = document.getElementById('company_logo_file_name');
        if (logoInput && logoName) {
            logoInput.addEventListener('change', function () {
                var file = logoInput.files && logoInput.files.length ? logoInput.files[0].name : 'No file chosen';
                logoName.value = file;
            });
        }

        if (!form) {
            return;
        }

        function safeSelectorValue(value) {
            return String(value || '').replace(/"/g, '\\"');
        }

        function fieldLabel(field) {
            if (!field) return null;

            if (field.type === 'checkbox') {
                return field.closest('.sponsor-consent');
            }

            if (!field.id) return null;
            return form.querySelector('label[for="' + safeSelectorValue(field.id) + '"]');
        }

        function markRequiredLabels() {
            var requiredFields = Array.prototype.slice.call(form.querySelectorAll('input[required], select[required], textarea[required]'));
            requiredFields.forEach(function (field) {
                var label = fieldLabel(field);
                if (!label || label.classList.contains('sponsor-consent')) {
                    return;
                }

                if (label.querySelector('.sponsor-required-asterisk')) {
                    return;
                }

                if (label.innerHTML.indexOf('*') !== -1) {
                    label.innerHTML = label.innerHTML.replace('*', '<span class="sponsor-required-asterisk">*</span>');
                } else {
                    label.insertAdjacentHTML('beforeend', ' <span class="sponsor-required-asterisk">*</span>');
                }
            });
        }

        function clearFieldError(field) {
            if (!field) return;
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');

            if (field.type === 'checkbox') {
                var consentLabel = field.closest('.sponsor-consent');
                if (consentLabel) {
                    consentLabel.classList.remove('is-invalid-consent');
                }
            }

            var parent = field.closest('.sponsor-form-field, .sponsor-consent, .sponsor-benefit-card');
            if (!parent) return;
            var error = parent.querySelector('.sponsor-required-error[data-client-error="1"]');
            if (error) {
                error.remove();
            }
        }

        function setFieldError(field, message) {
            if (!field) return;
            clearFieldError(field);

            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');

            if (field.type === 'checkbox') {
                var consentLabel = field.closest('.sponsor-consent');
                if (consentLabel) {
                    consentLabel.classList.add('is-invalid-consent');
                }
            }

            var parent = field.closest('.sponsor-form-field, .sponsor-consent, .sponsor-benefit-card');
            if (!parent) {
                parent = field.parentElement || field;
            }

            var error = document.createElement('div');
            error.className = 'sponsor-required-error';
            error.setAttribute('data-client-error', '1');
            error.textContent = message || 'This field is required.';
            parent.appendChild(error);
        }

        function validateRequiredField(field) {
            if (!field || field.disabled) return true;

            if (field.type === 'checkbox') {
                if (field.name === 'sponsorship_categories[]') {
                    var selected = form.querySelector('#sponsorship_categories');
                    if (selected && selected.selectedOptions.length > 0) {
                        return true;
                    }
                    setFieldError(selected || field, 'Please select at least one option.');
                    return false;
                }

                if (!field.checked && field.required) {
                    setFieldError(field, 'Please tick this required field.');
                    return false;
                }

                return true;
            }

            if (field.tagName === 'SELECT' && field.multiple) {
                if (field.required && field.selectedOptions.length === 0) {
                    setFieldError(field, 'Please select at least one option.');
                    return false;
                }
                return true;
            }

            if (field.required && String(field.value || '').trim() === '') {
                setFieldError(field, 'This field is required.');
                return false;
            }

            return true;
        }

        form.addEventListener('submit', function (event) {
            var requiredFields = Array.prototype.slice.call(form.querySelectorAll('input[required], select[required], textarea[required]'));
            var firstInvalid = null;

            requiredFields.forEach(function (field) {
                clearFieldError(field);
            });

            requiredFields.forEach(function (field) {
                var valid = validateRequiredField(field);
                if (!valid && !firstInvalid) {
                    firstInvalid = field;
                }
            });

            if (firstInvalid) {
                event.preventDefault();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof firstInvalid.focus === 'function') {
                    firstInvalid.focus({ preventScroll: true });
                }
            }
        });

        form.addEventListener('input', function (event) {
            clearFieldError(event.target);
        });

        form.addEventListener('change', function (event) {
            clearFieldError(event.target);
        });

        var firstServerInvalid = form.querySelector('.is-invalid');
        if (firstServerInvalid) {
            setTimeout(function () {
                firstServerInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 80);
        }

        markRequiredLabels();
    })();
</script>
@if(session('sponsor_success_message'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('becomeSponsorSuccessModal');
        if (!modalElement) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
            return;
        }

        window.alert(@json(session('sponsor_success_message')));
    });
</script>
@endif
</body>
</html>
