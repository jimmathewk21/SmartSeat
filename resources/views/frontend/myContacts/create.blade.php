<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $pageTitle = 'Add My Contact';
        $siteIcon = $siteMeta['site_icon'] ?? 'images/icon.webp';
        $isAbsoluteIcon = str_starts_with($siteIcon, 'http://') || str_starts_with($siteIcon, 'https://') || str_starts_with($siteIcon, '//') || str_starts_with($siteIcon, '/');
        $siteIconHref = $isAbsoluteIcon ? $siteIcon : asset($siteIcon);
        $pageSettings = $pageSettings ?? [];
        $genderOptions = [
            'male' => 'Male',
            'female' => 'Female',
            'prefer_not_to_say' => 'Prefer not to say',
            'other' => 'Other',
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
        body.add-my-contact-page #wrapper > section:last-of-type { margin-bottom: 0 !important; padding-bottom: 0 !important; }
        body.add-my-contact-page > footer { margin-top: 0 !important; }
        body.add-my-contact-page {
            background: #f4f5f8;
            color: #111;
        }
        body.add-my-contact-page .inside-page-hero-headline {
            color: #000 !important;
            -webkit-text-fill-color: #000 !important;
            background: none !important;
            text-shadow: none !important;
            opacity: 1 !important;
            margin-bottom: 10px;
            line-height: 1.1;
        }
        body.add-my-contact-page .inside-page-hero-main {
            color: #000;
            font-size: 42px;
            font-weight: 700;
            line-height: 1;
        }
        body.add-my-contact-page .inside-page-hero-headline small {
            color: #000;
            font-size: 0.6em;
            font-weight: 400;
        }
        body.add-my-contact-page .inside-page-hero-description {
            font-size: 18px;
            line-height: 1.8;
            color: #111;
            margin-bottom: 10px;
            max-width: 1100px;
        }
        body.add-my-contact-page .inside-page-hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        .add-my-contact-page-wrap {
            position: relative;
            overflow: hidden;
        }
        .add-my-contact-map {
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
        .add-my-contact-page-wrap > .container {
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
        .speaker-form-input,
        .speaker-form-select {
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
        .speaker-form-input:focus,
        .speaker-form-select:focus {
            outline: none;
            border-color: #7a52f4;
            box-shadow: 0 0 0 3px rgba(122, 82, 244, 0.12);
        }
        .speaker-upload-field {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
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
            margin-top: 6px;
            font-size: 12px;
            color: #687082;
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
        .speaker-captcha-label {
            display: block;
            margin-top: 4px;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #222;
        }
        .speaker-captcha {
            width: 100%;
            max-width: 260px;
            border: 1px solid #c9d0dd;
            border-radius: 11px;
            background: #f4f4ff;
            color: #2c2f48;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
            padding: 14px 16px;
            min-height: 48px;
            margin-bottom: 10px;
        }
        .speaker-captcha-answer {
            width: 100%;
            max-width: 260px;
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
        .invalid-feedback.d-block {
            font-size: 12px;
        }
        .official-email-message {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            line-height: 1.4;
            min-height: 18px;
            color: #dc3545;
        }
        @media (max-width: 991.98px) {
            body.add-my-contact-page .inside-page-hero-main {
                font-size: 34px;
                line-height: 1.08;
            }
            body.add-my-contact-page .inside-page-hero-description {
                font-size: 16px;
                line-height: 1.7;
            }
            .speaker-form-section-title {
                font-size: 24px;
            }
        }
        @media (max-width: 767.98px) {
            body.add-my-contact-page .add-my-contact-page-wrap {
                padding-top: 90px !important;
            }
            body.add-my-contact-page .add-my-contact-map {
                top: 160px;
                width: 118vw;
                opacity: 0.18;
            }
            body.add-my-contact-page .inside-page-hero-main {
                font-size: 30px;
            }
            body.add-my-contact-page .inside-page-hero-description {
                font-size: 15px;
            }
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
            .speaker-upload-btn,
            .speaker-submit-btn {
                width: 100%;
            }
            .speaker-captcha-answer {
                max-width: none;
            }
        }
    </style>
</head>
<body class="add-my-contact-page">
<div id="wrapper">
    @include('frontend.partials.header')

    <section class="pt-120 pb-80 bg-light add-my-contact-page-wrap">
        <div class="add-my-contact-map"></div>
        <div class="container">
            <div class="speaker-apply-shell">
                @include('frontend.partials.inner-page-hero', [
                    'heroLabel' => $pageSettings['apply_page_label'] ?? '[Add My Contact]',
                    'heroTitle' => $pageSettings['apply_page_title'] ?? 'MY CONTACT FORM',
                    'heroSuffix' => $pageSettings['apply_page_title_suffix'] ?? '',
                    'heroDescription' => $pageSettings['apply_page_description'] ?? '',
                    'heroButtons' => [],
                ])

                @if(session('message'))
                    <div class="alert alert-success speaker-alert">{{ session('message') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger speaker-alert">
                        Please correct the highlighted fields and submit again.
                    </div>
                @endif

                <form method="POST" action="{{ route('frontend.my-contacts.store') }}" enctype="multipart/form-data" class="speaker-apply-layout">
                    @csrf

                    <div class="speaker-form-section">
                        <h2 class="speaker-form-section-title">Basic Details</h2>
                        <div class="speaker-form-grid">
                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="full_name">Enter Your Full Name*</label>
                                <input id="full_name" type="text" name="full_name" class="speaker-form-input @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required>
                                @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="photo_file">Upload Your Picture* (Accepted formats: JPG, PNG, WEBP)</label>
                                <div class="speaker-upload-field">
                                    <input id="photo_file_name" type="text" class="speaker-form-input" value="{{ old('photo_file_name') }}" placeholder="Upload Your Picture*" readonly>
                                    <label class="speaker-upload-btn" for="photo_file">Upload</label>
                                    <input id="photo_file" type="file" name="photo_file" class="speaker-upload-native @error('photo_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp" required>
                                </div>
                                @error('photo_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="designation">Job Title*</label>
                                <input id="designation" type="text" name="designation" class="speaker-form-input @error('designation') is-invalid @enderror" value="{{ old('designation') }}" required>
                                @error('designation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="company_name">Company Name*</label>
                                <input id="company_name" type="text" name="company_name" class="speaker-form-input @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" required>
                                @error('company_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="nationality">Nationality*</label>
                                <select id="nationality" name="nationality" class="speaker-form-select @error('nationality') is-invalid @enderror" required>
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" @selected(old('nationality') === $country)>{{ $country }}</option>
                                    @endforeach
                                </select>
                                @error('nationality')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="gender">Gender*</label>
                                <select id="gender" name="gender" class="speaker-form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Select Gender</option>
                                    @foreach($genderOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('gender') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('gender')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="category">Category*</label>
                                <select id="category" name="category" class="speaker-form-select @error('category') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="area_of_expertise">Area of Expertise*</label>
                                <select id="area_of_expertise" name="area_of_expertise" class="speaker-form-select @error('area_of_expertise') is-invalid @enderror" required>
                                    <option value="">Select Area of Expertise</option>
                                    @foreach($specializations as $specialization)
                                        <option value="{{ $specialization }}" @selected(old('area_of_expertise') === $specialization)>{{ $specialization }}</option>
                                    @endforeach
                                </select>
                                @error('area_of_expertise')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="speaker-form-section">
                        <h2 class="speaker-form-section-title">Contact Details</h2>
                        <div class="speaker-form-grid">
                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="official_email">Official Email-id</label>
                                <input id="official_email" type="email" name="official_email" class="speaker-form-input @error('official_email') is-invalid @enderror" value="{{ old('official_email') }}" data-check-url="{{ route('frontend.my-contacts.check-email') }}" required>
                                @error('official_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small id="official_email_message" class="official-email-message" aria-live="polite"></small>
                            </div>
                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="alternate_email">Alternate Email-id</label>
                                <input id="alternate_email" type="email" name="alternate_email" class="speaker-form-input @error('alternate_email') is-invalid @enderror" value="{{ old('alternate_email') }}">
                                @error('alternate_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="mobile_whatsapp_number">Mobile/WhatsApp Number</label>
                                <input id="mobile_whatsapp_number" type="text" name="mobile_whatsapp_number" class="speaker-form-input @error('mobile_whatsapp_number') is-invalid @enderror" value="{{ old('mobile_whatsapp_number') }}">
                                @error('mobile_whatsapp_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="speaker-form-field">
                                <label class="speaker-form-label" for="linkedin_url">Linked-In URL</label>
                                <input id="linkedin_url" type="url" name="linkedin_url" class="speaker-form-input @error('linkedin_url') is-invalid @enderror" value="{{ old('linkedin_url') }}">
                                @error('linkedin_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="speaker-consent-wrap">
                        <label class="speaker-consent">
                            <input type="checkbox" name="publish_basic_details" value="1" {{ old('publish_basic_details') ? 'checked' : '' }} required>
                            <span>I confirm that all information submitted is true and accurate and I want my basic details to be published on website.</span>
                        </label>
                        <input type="hidden" name="confirm_accuracy" value="1">
                        <input type="hidden" name="grant_permission" value="1">
                        <label class="speaker-consent">
                            <input type="checkbox" name="publish_contact_details" value="1" {{ old('publish_contact_details') ? 'checked' : '' }}>
                            <span>I want my contact details to be published on website.</span>
                        </label>
                        @error('publish_basic_details')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror

                        <label class="speaker-captcha-label" for="captcha">Captcha</label>
                        <div class="speaker-captcha">{{ $question ?? session('my_contact_captcha_question') }}</div>
                        <input id="captcha" type="text" name="captcha" class="speaker-form-input speaker-captcha-answer @error('captcha') is-invalid @enderror" value="{{ old('captcha') }}" required>
                        @error('captcha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="speaker-submit-row">
                        <button type="submit" class="speaker-submit-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    @include('frontend.partials.footer')
</div>

<script src="{{ asset('js/vendors.js') }}"></script>
<script src="{{ asset('js/designesia.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var fileInput = document.getElementById('photo_file');
        var fileNameField = document.getElementById('photo_file_name');
        var officialEmailInput = document.getElementById('official_email');
        var officialEmailMessage = document.getElementById('official_email_message');
        var form = officialEmailInput ? officialEmailInput.closest('form') : null;
        var checkUrl = officialEmailInput ? officialEmailInput.getAttribute('data-check-url') : '';
        var checkTimer = null;
        var requestCounter = 0;

        function setMessage(message) {
            if (!officialEmailMessage) {
                return;
            }

            officialEmailMessage.textContent = message || '';
        }

        function checkOfficialEmail() {
            if (!officialEmailInput || !checkUrl) {
                return;
            }

            var email = officialEmailInput.value.trim();

            if (!email) {
                setMessage('');
                return;
            }

            var currentRequest = ++requestCounter;

            fetch(checkUrl + '?official_email=' + encodeURIComponent(email), {
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Unable to validate email.');
                    }

                    return response.json();
                })
                .then(function (data) {
                    if (currentRequest !== requestCounter) {
                        return;
                    }

                    if (data && data.exists) {
                        setMessage(data.message || 'This email already exists.');
                    } else {
                        setMessage('');
                    }
                })
                .catch(function () {
                    if (currentRequest !== requestCounter) {
                        return;
                    }

                    setMessage('');
                });
        }

        if (fileInput && fileNameField) {
            fileInput.addEventListener('change', function () {
                fileNameField.value = this.files && this.files[0] ? this.files[0].name : '';
            });
        }

        if (officialEmailInput) {
            officialEmailInput.addEventListener('input', function () {
                window.clearTimeout(checkTimer);
                checkTimer = window.setTimeout(checkOfficialEmail, 300);
            });

            officialEmailInput.addEventListener('blur', function () {
                window.clearTimeout(checkTimer);
                checkOfficialEmail();
            });

            if (officialEmailInput.value.trim()) {
                checkOfficialEmail();
            }
        }
    });
</script>
</body>
</html>
