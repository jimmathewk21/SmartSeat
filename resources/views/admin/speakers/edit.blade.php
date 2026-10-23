@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">{{ trans('global.edit') }} {{ trans('cruds.speaker.title_singular') }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.speakers.update', [$speaker->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <h5 class="mb-3">Basic Information</h5>
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label for="photo_url">{{ trans('cruds.speaker.fields.photo_url') }}</label>
                        <input class="form-control {{ $errors->has('photo_url') ? 'is-invalid' : '' }}" type="text" name="photo_url" id="photo_url" value="{{ old('photo_url', $speaker->photo_url) }}" placeholder="https:// or uploads/speakers/image.webp">
                        @if($errors->has('photo_url'))<div class="invalid-feedback">{{ $errors->first('photo_url') }}</div>@endif
                        <div class="mt-2">
                            <input class="form-control-file {{ $errors->has('photo_file') ? 'is-invalid' : '' }}" type="file" name="photo_file" id="photo_file" accept="image/*">
                            @if($errors->has('photo_file'))<div class="invalid-feedback d-block">{{ $errors->first('photo_file') }}</div>@endif
                        </div>
                        <small class="form-text text-muted">Image Size - 256 x 256 recommended. If no photo is uploaded, the selected gender default image will be used.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="required" for="name">{{ trans('cruds.speaker.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $speaker->name) }}" required>
                        @if($errors->has('name'))<div class="invalid-feedback">{{ $errors->first('name') }}</div>@endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="designation">{{ trans('cruds.speaker.fields.designation') }}</label>
                        <input class="form-control {{ $errors->has('designation') ? 'is-invalid' : '' }}" type="text" name="designation" id="designation" value="{{ old('designation', $speaker->designation) }}">
                        @if($errors->has('designation'))<div class="invalid-feedback">{{ $errors->first('designation') }}</div>@endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label for="gender">{{ trans('cruds.speaker.fields.gender') }}</label>
                        <select class="form-control {{ $errors->has('gender') ? 'is-invalid' : '' }}" name="gender" id="gender">
                            <option value="">{{ trans('global.pleaseSelect') }}</option>
                            <option value="male" @selected(old('gender', $speaker->gender) === 'male')>Male</option>
                            <option value="female" @selected(old('gender', $speaker->gender) === 'female')>Female</option>
                        </select>
                        @if($errors->has('gender'))<div class="invalid-feedback">{{ $errors->first('gender') }}</div>@endif
                        <small class="form-text text-muted">{{ trans('cruds.speaker.fields.gender_helper') }}</small>
                    </div>
                    <div class="col-md-8 mb-3">
                        @php($speakerRole = old('speaker_role', $speaker->speaker_role ?? 'speaker'))
                        <label class="d-block">Speaker / Moderator</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="speaker_role" id="speaker_role_speaker" value="speaker" @checked($speakerRole === 'speaker')>
                            <label class="form-check-label" for="speaker_role_speaker">Speaker</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="speaker_role" id="speaker_role_moderator" value="moderator" @checked($speakerRole === 'moderator')>
                            <label class="form-check-label" for="speaker_role_moderator">Moderator</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="speaker_role" id="speaker_role_both" value="both" @checked($speakerRole === 'both')>
                            <label class="form-check-label" for="speaker_role_both">Speaker & Moderator</label>
                        </div>
                        @if($errors->has('speaker_role'))<div class="invalid-feedback d-block">{{ $errors->first('speaker_role') }}</div>@endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label for="company_name">{{ trans('cruds.speaker.fields.company_name') }}</label>
                        <input class="form-control {{ $errors->has('company_name') ? 'is-invalid' : '' }}" type="text" name="company_name" id="company_name" value="{{ old('company_name', $speaker->company_name) }}">
                        @if($errors->has('company_name'))<div class="invalid-feedback">{{ $errors->first('company_name') }}</div>@endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="country">{{ trans('cruds.speaker.fields.country') }}</label>
                        <select class="form-control {{ $errors->has('country') ? 'is-invalid' : '' }}" name="country" id="country">
                            @foreach($countryOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('country', $speaker->country) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('country'))<div class="invalid-feedback">{{ $errors->first('country') }}</div>@endif
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <h5 class="mb-3">Contact</h5>
                <div class="form-row">
                    <div class="col-md-6 mb-3">
                        <label for="email">{{ trans('cruds.speaker.fields.email') }}</label>
                        <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" id="email" value="{{ old('email', $speaker->email) }}" data-speaker-email data-check-url="{{ route('admin.speakers.check-email') }}" data-speaker-id="{{ $speaker->id }}" data-original-email="{{ strtolower(trim((string) old('email', $speaker->email))) }}">
                        @if($errors->has('email'))<div class="invalid-feedback">{{ $errors->first('email') }}</div>@endif
                        <small class="form-text" id="email-availability-message" data-email-message></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone">{{ trans('cruds.speaker.fields.phone') }}</label>
                        <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" type="text" name="phone" id="phone" value="{{ old('phone', $speaker->phone) }}">
                        @if($errors->has('phone'))<div class="invalid-feedback">{{ $errors->first('phone') }}</div>@endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-6 mb-3">
                        <label for="linkedin_url">LinkedIn URL</label>
                        <input class="form-control {{ $errors->has('linkedin_url') ? 'is-invalid' : '' }}" type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url', $speaker->linkedin_url) }}" placeholder="https://linkedin.com/in/...">
                        @if($errors->has('linkedin_url'))<div class="invalid-feedback">{{ $errors->first('linkedin_url') }}</div>@endif
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <h5 class="mb-3">Topic & Category</h5>
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label for="topic">{{ trans('cruds.speaker.fields.topic') }}</label>
                        <input class="form-control {{ $errors->has('topic') ? 'is-invalid' : '' }}" type="text" name="topic" id="topic" value="{{ old('topic', $speaker->topic) }}">
                        @if($errors->has('topic'))<div class="invalid-feedback">{{ $errors->first('topic') }}</div>@endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="topic_category">{{ trans('cruds.speaker.fields.topic_category') }}</label>
                        <input class="form-control {{ $errors->has('topic_category') ? 'is-invalid' : '' }}" type="text" name="topic_category" id="topic_category" value="{{ old('topic_category', $speaker->topic_category) }}">
                        @if($errors->has('topic_category'))<div class="invalid-feedback">{{ $errors->first('topic_category') }}</div>@endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="category">{{ trans('cruds.speaker.fields.category') }}</label>
                        <input class="form-control {{ $errors->has('category') ? 'is-invalid' : '' }}" type="text" name="category" id="category" value="{{ old('category', $speaker->category) }}">
                        @if($errors->has('category'))<div class="invalid-feedback">{{ $errors->first('category') }}</div>@endif
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <h5 class="mb-3">Biography</h5>
                <div class="form-group">
                    @php($biographyType = old('biography_type', $speaker->biography_type ?? 'text'))
                    <label>{{ trans('cruds.speaker.fields.biography_type') }}</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="biography_type" id="biography_type_text" value="text" @checked($biographyType === 'text')>
                        <label class="form-check-label" for="biography_type_text">Text</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="biography_type" id="biography_type_link" value="link" @checked($biographyType === 'link')>
                        <label class="form-check-label" for="biography_type_link">Link</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="biography_type" id="biography_type_pdf" value="pdf" @checked($biographyType === 'pdf')>
                        <label class="form-check-label" for="biography_type_pdf">PDF</label>
                    </div>
                    @if($errors->has('biography_type'))<div class="invalid-feedback d-block">{{ $errors->first('biography_type') }}</div>@endif
                </div>
                <div class="form-group">
                    <label for="resource_url">{{ trans('cruds.speaker.fields.resource_url') }}</label>
                    <input class="form-control {{ $errors->has('resource_url') ? 'is-invalid' : '' }}" type="text" name="resource_url" id="resource_url" value="{{ old('resource_url', $speaker->resource_url) }}" placeholder="https://... or uploads/speakers/file.pdf">
                    @if($errors->has('resource_url'))<div class="invalid-feedback">{{ $errors->first('resource_url') }}</div>@endif
                </div>
                <div class="form-group">
                    <label for="bio_file">Bio Upload (PDF / Word)</label>
                    <input class="form-control-file {{ $errors->has('bio_file') ? 'is-invalid' : '' }}" type="file" name="bio_file" id="bio_file" accept=".pdf,.doc,.docx">
                    @if($errors->has('bio_file'))<div class="invalid-feedback d-block">{{ $errors->first('bio_file') }}</div>@endif
                    @if(!empty($speaker->resource_url))
                        @php($bioFile = trim((string) $speaker->resource_url))
                        <div class="mt-2">
                            <a href="{{ \Illuminate\Support\Str::startsWith($bioFile, ['http://', 'https://', '/']) ? $bioFile : asset($bioFile) }}" target="_blank" rel="noopener noreferrer">View current bio file</a>
                        </div>
                    @endif
                    <small class="form-text text-muted">Use this when Biography Type is PDF. Accepted formats: PDF, DOC, DOCX.</small>
                </div>
                <div class="form-row">
                    <div class="col-md-6 mb-3">
                        <label for="advert_url">Advert File URL/Path</label>
                        <input class="form-control {{ $errors->has('advert_url') ? 'is-invalid' : '' }}" type="text" name="advert_url" id="advert_url" value="{{ old('advert_url', $speaker->advert_url) }}" placeholder="uploads/speakers/file.pdf or https://...">
                        @if($errors->has('advert_url'))<div class="invalid-feedback">{{ $errors->first('advert_url') }}</div>@endif
                        <div class="mt-2">
                            <input class="form-control-file {{ $errors->has('advert_file') ? 'is-invalid' : '' }}" type="file" name="advert_file" id="advert_file" accept=".jpg,.jpeg,.png,.pdf">
                            @if($errors->has('advert_file'))<div class="invalid-feedback d-block">{{ $errors->first('advert_file') }}</div>@endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="presentation_file_url">Presentation File URL/Path</label>
                        <input class="form-control {{ $errors->has('presentation_file_url') ? 'is-invalid' : '' }}" type="text" name="presentation_file_url" id="presentation_file_url" value="{{ old('presentation_file_url', $speaker->presentation_file_url) }}" placeholder="uploads/speakers/file.pdf or https://...">
                        @if($errors->has('presentation_file_url'))<div class="invalid-feedback">{{ $errors->first('presentation_file_url') }}</div>@endif
                        <div class="mt-2">
                            <input class="form-control-file {{ $errors->has('presentation_file') ? 'is-invalid' : '' }}" type="file" name="presentation_file" id="presentation_file" accept=".pdf,.doc,.docx,.ppt,.pptx">
                            @if($errors->has('presentation_file'))<div class="invalid-feedback d-block">{{ $errors->first('presentation_file') }}</div>@endif
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="bio">{{ trans('cruds.speaker.fields.bio') }}</label>
                    <textarea class="form-control ckeditor {{ $errors->has('bio') ? 'is-invalid' : '' }}" name="bio" id="bio" rows="4">{{ old('bio', $speaker->bio) }}</textarea>
                    @if($errors->has('bio'))<div class="invalid-feedback d-block">{{ $errors->first('bio') }}</div>@endif
                </div>
            </div>
            <div class="mb-4">
                <h5 class="mb-3">Visibility</h5>
                @foreach([
                    'show_on_homepage' => 'cruds.speaker.fields.show_on_homepage',
                    'show_on_scrollbar' => 'Show On Scroll Bar (Sponsors Detail Page, Agenda)',
                ] as $field => $labelKey)
                    <div class="form-check">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input class="form-check-input" type="checkbox" name="{{ $field }}" id="{{ $field }}" value="1" @checked(old($field, $speaker->{$field}))>
                        <label class="form-check-label" for="{{ $field }}">{{ str_contains($labelKey, 'cruds.') ? trans($labelKey) : $labelKey }}</label>
                    </div>
                @endforeach
                <div class="form-check mt-2">
                    <input type="hidden" name="is_key_figure" value="0">
                    <input class="form-check-input" type="checkbox" name="is_key_figure" id="is_key_figure" value="1" @checked(old('is_key_figure', $speaker->is_key_figure))>
                    <label class="form-check-label" for="is_key_figure">{{ trans('cruds.speaker.fields.is_key_figure') }}</label>
                </div>
                @if($errors->has('is_key_figure'))<div class="invalid-feedback d-block">{{ $errors->first('is_key_figure') }}</div>@endif
                <div class="form-check mt-2">
                    <input type="hidden" name="show_contact_details" value="0">
                    <input class="form-check-input" type="checkbox" name="show_contact_details" id="show_contact_details" value="1" @checked(old('show_contact_details', $speaker->show_contact_details))>
                    <label class="form-check-label" for="show_contact_details">Show Contact Details on Speaker Profile (Email & Mobile)</label>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-3 mb-3">
                    <label class="required" for="sort_order">{{ trans('cruds.speaker.fields.sort_order') }}</label>
                    <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $speaker->sort_order) }}" required>
                    @if($errors->has('sort_order'))<div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>@endif
                </div>
                <div class="col-md-3 mb-3">
                    <label class="required" for="is_active">Publish</label>
                    <select class="form-control" name="is_active" id="is_active">
                        <option value="1" @selected((string)old('is_active', $speaker->is_active ? '1' : '0') === '1')>{{ trans('global.yes') }}</option>
                        <option value="0" @selected((string)old('is_active', $speaker->is_active ? '1' : '0') === '0')>{{ trans('global.no') }}</option>
                    </select>
                </div>
            </div>
            <div class="form-group"><button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button></div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
    @parent
    <script>
        (function () {
            const emailInput = document.querySelector('[data-speaker-email]');
            const emailMessage = document.querySelector('[data-email-message]');
            const submitButton = document.querySelector('form[action="{{ route('admin.speakers.update', [$speaker->id]) }}"] button[type="submit"]');
            if (emailInput && emailMessage && submitButton) {
                let timer = null;
                let pendingController = null;

                const originalEmail = (emailInput.dataset.originalEmail || '').trim().toLowerCase();

                const setState = function (isTaken, message) {
                    emailInput.setCustomValidity(isTaken ? message : '');
                    emailMessage.textContent = message || '';
                    emailMessage.className = isTaken ? 'form-text text-danger' : 'form-text text-muted';
                    submitButton.disabled = isTaken;
                };

                const checkEmail = function () {
                    const email = emailInput.value.trim().toLowerCase();
                    if (!email || email === originalEmail) {
                        if (pendingController) {
                            pendingController.abort();
                            pendingController = null;
                        }
                        setState(false, '');
                        return;
                    }

                    if (pendingController) {
                        pendingController.abort();
                    }

                    pendingController = new AbortController();
                    const params = new URLSearchParams({
                        email: email,
                        speaker_id: emailInput.dataset.speakerId || '',
                    });

                    fetch(`${emailInput.dataset.checkUrl}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                        },
                        signal: pendingController.signal,
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Email check failed');
                            }
                            return response.json();
                        })
                        .then(function (data) {
                            const isTaken = Boolean(data.exists);
                            setState(isTaken, data.message || (isTaken ? 'This email is already used by another speaker.' : ''));
                        })
                        .catch(function (error) {
                            if (error.name !== 'AbortError') {
                                console.error(error);
                            }
                        });
                };

                emailInput.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(checkEmail, 300);
                });

                emailInput.addEventListener('blur', checkEmail);
            }

            document.querySelectorAll('.ckeditor').forEach(function (element) {
                if (element.dataset.ckeditorInitialized === '1') {
                    return;
                }

                ClassicEditor
                    .create(element)
                    .then(function (editor) {
                        element.dataset.ckeditorInitialized = '1';
                        editor.model.document.on('change:data', function () {
                            element.value = editor.getData();
                        });
                    })
                    .catch(function (error) {
                        console.error(error);
                    });
            });
        })();
    </script>
@endsection
