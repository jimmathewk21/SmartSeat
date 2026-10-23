<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'Become A Speaker';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $speakerSettings = $speakerPageSettings ?? [];
        $genderOptions = [
            'male' => 'Male',
            'female' => 'Female',
            'prefer_not_to_say' => 'Prefer not to say',
            'other' => 'Other',
        ];
        $experienceOptions = [
            '1-3' => '1 to 3 years',
            '4-7' => '4 to 7 years',
            '8-12' => '8 to 12 years',
            '13-20' => '13 to 20 years',
            '20+' => '20+ years',
        ];
        $yesNoOptions = [
            'Yes' => 'Yes',
            'No' => 'No',
        ];
        $presentationModeOptions = [
            'Panel Discussion' => 'Panel Discussion',
            'Presentation' => 'Presentation',
        ];
        $durationOptions = [
            '15' => '15 mins',
            '30' => '30 mins',
            '45' => '45 mins',
            '60' => '60 mins',
        ];
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
        body.become-speaker-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.become-speaker-page > footer { margin-top: 0 !important; }
        body.become-speaker-page {
            background: #f4f5f8;
            color: #111;
        }
        body.become-speaker-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.become-speaker-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.become-speaker-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.become-speaker-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 12px;
            max-width: 1100px;
        }
        body.become-speaker-page .inside-page-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        .speaker-apply-page {
            position: relative;
            overflow: hidden;
        }
        .speaker-apply-map {
            position: absolute;
            top: 140px;
            left: 50%;
            width: min(1411px, 100vw);
            aspect-ratio: 1411 / 702;
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
        .speaker-apply-page > .container {
            position: relative;
            z-index: 1;
        }
        .speaker-apply-shell {
            max-width: 1080px;
            margin: 0 auto;
        }
        .speaker-apply-layout {
            display: grid;
            gap: 18px;
        }
        .speaker-form-section {
            border: 1.5px solid rgba(33, 37, 41, 0.55);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
            padding: 18px 18px 20px;
            box-shadow: 0 14px 30px rgba(19, 25, 47, 0.05);
        }
        .speaker-form-section-title {
            margin: 0 0 14px;
            font-size: 29px;
            line-height: 1.05;
            font-weight: 800;
            text-transform: uppercase;
            color: #151515;
        }
        .speaker-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 18px;
        }
        .speaker-form-field {
            min-width: 0;
        }
        .speaker-form-field.full {
            grid-column: 1 / -1;
        }
        .speaker-form-label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 700;
            color: #222;
        }
        .speaker-form-label-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .speaker-form-label small {
            font-weight: 500;
            color: #626b7d;
        }
        .speaker-form-input,
        .speaker-form-select,
        .speaker-form-textarea {
            width: 100%;
            border: 1px solid #c9d0dd;
            border-radius: 11px;
            background: #fff;
            color: #111;
            font-size: 15px;
            line-height: 1.25;
            padding: 12px 14px;
            min-height: 48px;
            box-shadow: none;
        }
        .speaker-form-textarea {
            min-height: 132px;
            resize: vertical;
        }
        .speaker-form-select {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='9' viewBox='0 0 14 9' fill='none'%3E%3Cpath d='M1 1.5L7 7.5L13 1.5' stroke='%23111' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 14px 9px;
            padding-right: 42px;
            line-height: 1.25;
        }
        .speaker-form-input:focus,
        .speaker-form-select:focus,
        .speaker-form-textarea:focus {
            outline: none;
            border-color: #7a52f4;
            box-shadow: 0 0 0 3px rgba(122, 82, 244, 0.12);
        }
        .speaker-upload-field {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
        }
        .speaker-upload-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 124px;
            height: 44px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, #8255ff, #6545f3);
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
        }
        .speaker-upload-btn:hover,
        .speaker-upload-btn:focus {
            color: #fff;
            text-decoration: none;
        }
        .speaker-upload-native {
            display: none;
        }
        .speaker-upload-note {
            font-size: 12px;
            color: #687082;
            white-space: nowrap;
        }
        .speaker-consent-wrap {
            padding: 6px 2px 0;
        }
        .speaker-consent {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
            font-size: 15px;
            line-height: 1.5;
            font-weight: 700;
            color: #1a1a1a;
        }
        .speaker-consent input {
            width: 18px;
            height: 18px;
            margin-top: 3px;
            flex: 0 0 auto;
        }
        .speaker-submit-row {
            display: flex;
            justify-content: center;
            margin-top: 6px;
        }
        .speaker-submit-btn {
            min-width: 180px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #7d52ff, #5c42ef);
            color: #fff;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 13px 28px;
            cursor: pointer;
            box-shadow: 0 16px 26px rgba(95, 66, 239, 0.22);
        }
        .speaker-submit-btn:hover,
        .speaker-submit-btn:focus {
            color: #fff;
            text-decoration: none;
        }
        .speaker-alert {
            border-radius: 16px;
            margin-bottom: 22px;
        }
        .speaker-form-label.required-label {
            color: #222;
        }
        .speaker-consent.required-label span {
            color: #1a1a1a;
        }
        .speaker-required-asterisk {
            color: #d91f1f;
            font-weight: 800;
        }
        .speaker-form-input.is-invalid,
        .speaker-form-select.is-invalid,
        .speaker-form-textarea.is-invalid {
            border-color: #d91f1f;
            box-shadow: 0 0 0 3px rgba(217, 31, 31, 0.12);
        }
        .speaker-required-error {
            margin-top: 6px;
            color: #d91f1f;
            font-size: 12px;
            line-height: 1.45;
            font-weight: 600;
        }
        @media (max-width: 991.98px) {
            .speaker-apply-map {
                top: 160px;
                width: 118vw;
                aspect-ratio: 1411 / 702;
                height: auto;
                opacity: 0.18;
            }
            .speaker-form-section-title {
                font-size: 24px;
            }
        }
        @media (max-width: 767.98px) {
            .speaker-form-section {
                padding: 16px 14px 18px;
                border-radius: 18px;
            }
            .speaker-form-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }
            .speaker-upload-field {
                grid-template-columns: minmax(0, 1fr);
            }
            .speaker-upload-btn {
                width: 100%;
            }
            .speaker-submit-btn {
                width: 100%;
            }
            .speaker-upload-note {
                white-space: normal;
            }
        }
    </style>
</head>
<body class="become-speaker-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80 speaker-apply-page">
        <div class="speaker-apply-map" aria-hidden="true"></div>
        <div class="container">
            <div class="speaker-apply-shell">
                @include('frontend.partials.inner-page-hero', [
                    'heroLabel' => $speakerSettings['apply_page_label'] ?? '[Speaker Nomination]',
                    'heroTitle' => $speakerSettings['apply_page_title'] ?? 'SPEAKER FORM',
                    'heroSuffix' => $speakerSettings['apply_page_title_suffix'] ?? 'Become A Speaker',
                    'heroDescription' => $speakerSettings['apply_page_description'] ?? 'I will change the text later for time being use a dummy text. I will change the text later for time being use a dummy text',
                    'heroButtons' => [],
                ])

                @if(session('message'))
                    <div class="alert alert-info speaker-alert">{{ session('message') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger speaker-alert">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('frontend.speakers.apply') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="speaker-apply-layout">
                        <div class="speaker-form-section">
                            <h2 class="speaker-form-section-title">Personal Information:</h2>
                            <div class="speaker-form-grid">
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="full_name">Enter Your Full Name*</label>
                                    <input id="full_name" type="text" class="speaker-form-input" name="full_name" value="{{ old('full_name') }}" placeholder="Enter Your Full Name*" required>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="photo_file">Upload Your Picture* <small>(Accepted formats: JPG, PNG, WEBP)</small></label>
                                    <div class="speaker-upload-field">
                                        <input id="photo_file_name" type="text" class="speaker-form-input" value="{{ old('photo_file_name', 'No file chosen') }}" readonly>
                                        <label class="speaker-upload-btn" for="photo_file">Upload</label>
                                        <input id="photo_file" class="speaker-upload-native" type="file" name="photo_file" accept=".jpg,.jpeg,.png,.webp" required>
                                    </div>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="gender">Gender</label>
                                    <select id="gender" class="speaker-form-select" name="gender">
                                        <option value="">Select Gender</option>
                                        @foreach($genderOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('gender') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="nationality">Nationality <small>(Select with search box)</small></label>
                                    <input list="speaker-country-list" id="nationality" type="text" class="speaker-form-input" name="nationality" value="{{ old('nationality') }}" placeholder="Search nationality">
                                    <datalist id="speaker-country-list">
                                        @foreach(($countries ?? collect()) as $country)
                                            <option value="{{ $country }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                        </div>

                        <div class="speaker-form-section">
                            <h2 class="speaker-form-section-title">Professional Details:</h2>
                            <div class="speaker-form-grid">
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="organization">Company Name*</label>
                                    <input id="organization" type="text" class="speaker-form-input" name="organization" value="{{ old('organization') }}" placeholder="Company Name*" required>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="designation">Job Title</label>
                                    <input id="designation" type="text" class="speaker-form-input" name="designation" value="{{ old('designation') }}" placeholder="Job Title">
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="areas_of_expertise">Areas of Expertise</label>
                                    <input id="areas_of_expertise" type="text" class="speaker-form-input" name="areas_of_expertise" value="{{ old('areas_of_expertise') }}" placeholder="Areas of Expertise">
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="years_of_experience">Years of Experience <small>(Select 1 to 20+)</small></label>
                                    <select id="years_of_experience" class="speaker-form-select" name="years_of_experience">
                                        <option value="">Years of Experience (Select 1 to 20+)</option>
                                        @foreach($experienceOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('years_of_experience') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="speaker-form-field full">
                                    <label class="speaker-form-label" for="short_bio">Short Biography <small>(within 200 words)</small></label>
                                    <textarea id="short_bio" class="speaker-form-textarea" name="short_bio" rows="5" placeholder="Short Biography (within 200 words)">{{ old('short_bio') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="speaker-form-section">
                            <h2 class="speaker-form-section-title">Contact Details:</h2>
                            <div class="speaker-form-grid">
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="official_email">Official Email*</label>
                                    <input id="official_email" type="email" class="speaker-form-input" name="official_email" value="{{ old('official_email') }}" placeholder="Official Email*" required>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="personal_email">Alternate Email</label>
                                    <input id="personal_email" type="email" class="speaker-form-input" name="personal_email" value="{{ old('personal_email') }}" placeholder="Alternate Email">
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="mobile">Mobile Number</label>
                                    <input id="mobile" type="text" class="speaker-form-input" name="mobile" value="{{ old('mobile') }}" placeholder="Mobile Number">
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="linkedin_url">LinkedIn URL</label>
                                    <input id="linkedin_url" type="url" class="speaker-form-input" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="LinkedIn Url">
                                </div>
                            </div>
                        </div>

                        <div class="speaker-form-section">
                            <h2 class="speaker-form-section-title">Speaking Engagements:</h2>
                            <div class="speaker-form-grid">
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="proposed_topic_title">Proposed Topic Title*</label>
                                    <input id="proposed_topic_title" type="text" class="speaker-form-input" name="proposed_topic_title" value="{{ old('proposed_topic_title') }}" placeholder="Proposed Topic Title*" required>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="av_requirements">Audio Visual Requirements <small>(Select Yes or No)</small></label>
                                    <select id="av_requirements" class="speaker-form-select" name="av_requirements">
                                        <option value="">Audio Visual Requirements (Select Yes or No)</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('av_requirements') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="presentation_mode">Presentation Mode <small>(Select Panel Discussion / Presentation)</small></label>
                                    <select id="presentation_mode" class="speaker-form-select" name="presentation_mode">
                                        <option value="">Presentation Mode (Select Panel Discussion / Presentation)</option>
                                        @foreach($presentationModeOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('presentation_mode') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="presentation_duration">Speaking Slot Duration <small>(Select 15/30/45/60 mins)</small></label>
                                    <select id="presentation_duration" class="speaker-form-select" name="presentation_duration">
                                        <option value="">Speaking Slot Duration (Select 15/30/45/60 mins)</option>
                                        @foreach($durationOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('presentation_duration') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="speaker-form-field full">
                                    <label class="speaker-form-label" for="abstract_summary">Abstract / Summary of Your Presentation <small>(within 300 words)</small></label>
                                    <textarea id="abstract_summary" class="speaker-form-textarea" name="abstract_summary" rows="5" placeholder="Abstract/Summary of your presentation (within 300 words)">{{ old('abstract_summary') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="speaker-form-section">
                            <h2 class="speaker-form-section-title">Financial / Logistic Support:</h2>
                            <div class="speaker-form-grid">
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="travel_support_required">Would you be able to book your own air tickets?*</label>
                                    <select id="travel_support_required" class="speaker-form-select" name="travel_support_required" required>
                                        <option value="">Select option</option>
                                        <option value="1" @selected(old('travel_support_required') === '1')>Yes</option>
                                        <option value="0" @selected(old('travel_support_required') === '0')>No</option>
                                    </select>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="sub_theme">Would you be able to cover your own hotel stay and logistics?*</label>
                                    <select id="sub_theme" class="speaker-form-select" name="sub_theme" required>
                                        <option value="">Select Option (YES / NO)</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('sub_theme') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="session_type">Would you register and pay online for your registration?*</label>
                                    <select id="session_type" class="speaker-form-select" name="session_type" required>
                                        <option value="">Select Option (YES / NO)</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('session_type') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="speaker-form-field">
                                    <label class="speaker-form-label" for="previous_engagements">Would you like to support the event by becoming a sponsor?*</label>
                                    <select id="previous_engagements" class="speaker-form-select" name="previous_engagements" required>
                                        <option value="">Select Option (YES / NO)</option>
                                        @foreach($yesNoOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('previous_engagements') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="speaker-form-field full">
                                    <label class="speaker-form-label" for="special_requirements">Do you have any requests or suggestions for your speaking slot? Or any specific requirements.</label>
                                    <textarea id="special_requirements" class="speaker-form-textarea" name="special_requirements" rows="4" placeholder="Do you have any requests or suggestions for your speaking slot? or Any specific requirements.">{{ old('special_requirements') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="speaker-consent-wrap">
                            <label class="speaker-consent">
                                <input type="checkbox" name="confirm_accuracy" value="1" @checked(old('confirm_accuracy')) required>
                                <span>I confirm that all information submitted is true and accurate.</span>
                            </label>
                            <label class="speaker-consent">
                                <input type="checkbox" name="consent_promotional_use" value="1" @checked(old('consent_promotional_use')) required>
                                <span>I grant permission to use my profile, bio, and photo for event promotion.</span>
                            </label>
                            <label class="speaker-consent">
                                <input type="checkbox" name="consent_selection_terms" value="1" @checked(old('consent_selection_terms')) required>
                                <span>I agree to the speaker selection terms and accept the organizer’s decision as final.</span>
                            </label>
                        </div>

                        <div class="speaker-submit-row">
                            <button type="submit" class="speaker-submit-btn">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

@include('frontend.partials.footer')

<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script>
    (function () {
        var form = document.querySelector('form[action="{{ route('frontend.speakers.apply') }}"]');
        var photoInput = document.getElementById('photo_file');
        var photoName = document.getElementById('photo_file_name');
        var serverErrors = @json($errors->messages());

        if (!form) {
            return;
        }

        if (photoInput && photoName) {
            photoInput.addEventListener('change', function () {
                var file = photoInput.files && photoInput.files.length ? photoInput.files[0].name : 'No file chosen';
                photoName.value = file;
            });
        }

        function safeSelectorValue(value) {
            return String(value || '').replace(/"/g, '\\"');
        }

        function fieldLabel(field) {
            if (!field) return null;

            if (field.type === 'checkbox') {
                return field.closest('.speaker-consent');
            }

            if (!field.id) return null;
            return form.querySelector('label[for="' + safeSelectorValue(field.id) + '"]');
        }

        function markRequiredLabels() {
            var requiredFields = Array.prototype.slice.call(form.querySelectorAll('input[required], select[required], textarea[required]'));
            requiredFields.forEach(function (field) {
                var label = fieldLabel(field);
                if (label) {
                    label.classList.add('required-label');

                    if (label.classList.contains('speaker-consent')) {
                        return;
                    }

                    if (label.querySelector('.speaker-required-asterisk')) {
                        return;
                    }

                    if (label.innerHTML.indexOf('*') !== -1) {
                        label.innerHTML = label.innerHTML.replace('*', '<span class="speaker-required-asterisk">*</span>');
                    } else {
                        label.insertAdjacentHTML('beforeend', ' <span class="speaker-required-asterisk">*</span>');
                    }
                }
            });
        }

        function clearFieldError(field) {
            if (!field) return;
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');

            var parent = field.closest('.speaker-form-field, .speaker-consent');
            if (!parent) return;
            var error = parent.querySelector('.speaker-required-error[data-client-error="1"]');
            if (error) {
                error.remove();
            }
        }

        function setFieldError(field, message) {
            if (!field) return;
            clearFieldError(field);

            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');

            var parent = field.closest('.speaker-form-field, .speaker-consent');
            if (!parent) {
                parent = field.parentElement || field;
            }

            var error = document.createElement('div');
            error.className = 'speaker-required-error';
            error.setAttribute('data-client-error', '1');
            error.textContent = message || 'This field is required.';
            parent.appendChild(error);
        }

        function validateRequiredField(field) {
            if (!field || field.disabled) return true;

            if (field.type === 'checkbox') {
                if (field.required && !field.checked) {
                    setFieldError(field, 'Please accept this required field.');
                    return false;
                }
                return true;
            }

            if (field.type === 'file') {
                if (field.required && (!field.files || field.files.length === 0)) {
                    setFieldError(field, 'Please upload a file.');
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

        Object.keys(serverErrors || {}).forEach(function (name) {
            var firstMessage = Array.isArray(serverErrors[name]) && serverErrors[name].length > 0 ? serverErrors[name][0] : null;
            if (!firstMessage) return;

            var field = form.querySelector('[name="' + safeSelectorValue(name) + '"]');
            if (!field) {
                field = form.querySelector('[name="' + safeSelectorValue(name) + '[]"]');
            }
            if (field) {
                setFieldError(field, firstMessage);
            }
        });

        markRequiredLabels();
    })();
</script>
</body>
</html>
