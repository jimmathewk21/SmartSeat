@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.my-contacts.page-settings.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            My Contacts Page Settings
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="page_title">Section Name</label>
                    <input type="text" class="form-control {{ $errors->has('page_title') ? 'is-invalid' : '' }}" name="page_title" id="page_title" value="{{ old('page_title', $settings['page_title'] ?? '') }}" required>
                    @if($errors->has('page_title'))
                        <div class="invalid-feedback">{{ $errors->first('page_title') }}</div>
                    @endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="subtitle">Main Title</label>
                    <input type="text" class="form-control {{ $errors->has('subtitle') ? 'is-invalid' : '' }}" name="subtitle" id="subtitle" value="{{ old('subtitle', $settings['subtitle'] ?? '') }}" required>
                    @if($errors->has('subtitle'))
                        <div class="invalid-feedback">{{ $errors->first('subtitle') }}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="subtitle_suffix">Title Suffix</label>
                    <input type="text" class="form-control {{ $errors->has('subtitle_suffix') ? 'is-invalid' : '' }}" name="subtitle_suffix" id="subtitle_suffix" value="{{ old('subtitle_suffix', $settings['subtitle_suffix'] ?? '') }}">
                    @if($errors->has('subtitle_suffix'))
                        <div class="invalid-feedback">{{ $errors->first('subtitle_suffix') }}</div>
                    @endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="disclaimer_button_text">Disclaimer Button Text</label>
                    <input type="text" class="form-control {{ $errors->has('disclaimer_button_text') ? 'is-invalid' : '' }}" name="disclaimer_button_text" id="disclaimer_button_text" value="{{ old('disclaimer_button_text', $settings['disclaimer_button_text'] ?? '') }}" required>
                    @if($errors->has('disclaimer_button_text'))
                        <div class="invalid-feedback">{{ $errors->first('disclaimer_button_text') }}</div>
                    @endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="form_button_text">Form Button Text</label>
                    <input type="text" class="form-control {{ $errors->has('form_button_text') ? 'is-invalid' : '' }}" name="form_button_text" id="form_button_text" value="{{ old('form_button_text', $settings['form_button_text'] ?? '') }}" required>
                    @if($errors->has('form_button_text'))
                        <div class="invalid-feedback">{{ $errors->first('form_button_text') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description" rows="4" required>{{ old('description', $settings['description'] ?? '') }}</textarea>
                @if($errors->has('description'))
                    <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="form_button_link">Form Button Link</label>
                <input type="text" class="form-control {{ $errors->has('form_button_link') ? 'is-invalid' : '' }}" name="form_button_link" id="form_button_link" value="{{ old('form_button_link', $settings['form_button_link'] ?? '') }}" required>
                <small class="form-text text-muted">Use an internal path like <code>/add-my-contact</code> or a full URL.</small>
                @if($errors->has('form_button_link'))
                    <div class="invalid-feedback">{{ $errors->first('form_button_link') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="notification_email">Admin Notification Email</label>
                <input type="email" class="form-control {{ $errors->has('notification_email') ? 'is-invalid' : '' }}" name="notification_email" id="notification_email" value="{{ old('notification_email', $settings['notification_email'] ?? '') }}">
                <small class="form-text text-muted">Add the email address that should receive Add My Contact form submissions. Leave blank to use the existing admin email fallback.</small>
                @if($errors->has('notification_email'))
                    <div class="invalid-feedback">{{ $errors->first('notification_email') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="guidelines_content">Disclaimer Modal Content</label>
                <textarea class="form-control {{ $errors->has('guidelines_content') ? 'is-invalid' : '' }}" name="guidelines_content" id="guidelines_content" rows="10" required>{{ old('guidelines_content', $settings['guidelines_content'] ?? '') }}</textarea>
                <small class="form-text text-muted">This content is shown when the Disclaimer button is clicked on <code>/my-contacts</code>. HTML is supported.</small>
                @if($errors->has('guidelines_content'))
                    <div class="invalid-feedback">{{ $errors->first('guidelines_content') }}</div>
                @endif
            </div>

            <hr>
            <h5 class="mb-3">Add My Contact Page Hero</h5>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="apply_page_label">Top Label</label>
                    <input type="text" class="form-control {{ $errors->has('apply_page_label') ? 'is-invalid' : '' }}" name="apply_page_label" id="apply_page_label" value="{{ old('apply_page_label', $settings['apply_page_label'] ?? '') }}" required>
                    @if($errors->has('apply_page_label'))
                        <div class="invalid-feedback">{{ $errors->first('apply_page_label') }}</div>
                    @endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="apply_page_title">Main Title</label>
                    <input type="text" class="form-control {{ $errors->has('apply_page_title') ? 'is-invalid' : '' }}" name="apply_page_title" id="apply_page_title" value="{{ old('apply_page_title', $settings['apply_page_title'] ?? '') }}" required>
                    @if($errors->has('apply_page_title'))
                        <div class="invalid-feedback">{{ $errors->first('apply_page_title') }}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="apply_page_title_suffix">Title Suffix</label>
                    <input type="text" class="form-control {{ $errors->has('apply_page_title_suffix') ? 'is-invalid' : '' }}" name="apply_page_title_suffix" id="apply_page_title_suffix" value="{{ old('apply_page_title_suffix', $settings['apply_page_title_suffix'] ?? '') }}">
                    @if($errors->has('apply_page_title_suffix'))
                        <div class="invalid-feedback">{{ $errors->first('apply_page_title_suffix') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="apply_page_description">Description</label>
                <textarea class="form-control {{ $errors->has('apply_page_description') ? 'is-invalid' : '' }}" name="apply_page_description" id="apply_page_description" rows="4" required>{{ old('apply_page_description', $settings['apply_page_description'] ?? '') }}</textarea>
                @if($errors->has('apply_page_description'))
                    <div class="invalid-feedback">{{ $errors->first('apply_page_description') }}</div>
                @endif
            </div>

            <hr>
            <h5 class="mb-3">Custom Dropdown Lists</h5>
            <p class="text-muted mb-3">One option per line. These values are available in Admin My Contact add/edit form filters and inputs.</p>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="country_options">Country Options</label>
                    <textarea class="form-control {{ $errors->has('country_options') ? 'is-invalid' : '' }}" name="country_options" id="country_options" rows="6" placeholder="India&#10;United States&#10;United Kingdom">{{ old('country_options', $settings['country_options'] ?? '') }}</textarea>
                    @if($errors->has('country_options'))
                        <div class="invalid-feedback">{{ $errors->first('country_options') }}</div>
                    @endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="specialization_options">Area of Expertise Options</label>
                    <textarea class="form-control {{ $errors->has('specialization_options') ? 'is-invalid' : '' }}" name="specialization_options" id="specialization_options" rows="6" placeholder="Patent&#10;Trademark&#10;Copyright">{{ old('specialization_options', $settings['specialization_options'] ?? '') }}</textarea>
                    @if($errors->has('specialization_options'))
                        <div class="invalid-feedback">{{ $errors->first('specialization_options') }}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="speaker_tag_options">Speaker Tag Options</label>
                    <textarea class="form-control {{ $errors->has('speaker_tag_options') ? 'is-invalid' : '' }}" name="speaker_tag_options" id="speaker_tag_options" rows="4" placeholder="Speaker&#10;Delegate&#10;VIP">{{ old('speaker_tag_options', $settings['speaker_tag_options'] ?? '') }}</textarea>
                    @if($errors->has('speaker_tag_options'))
                        <div class="invalid-feedback">{{ $errors->first('speaker_tag_options') }}</div>
                    @endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="experience_options">Experience Options</label>
                    <textarea class="form-control {{ $errors->has('experience_options') ? 'is-invalid' : '' }}" name="experience_options" id="experience_options" rows="4" placeholder="0-3 Years&#10;4-7 Years&#10;8-12 Years">{{ old('experience_options', $settings['experience_options'] ?? '') }}</textarea>
                    @if($errors->has('experience_options'))
                        <div class="invalid-feedback">{{ $errors->first('experience_options') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
@parent
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editorElement = document.querySelector('#guidelines_content');

        if (editorElement) {
            ClassicEditor
                .create(editorElement)
                .catch(function (error) {
                    console.error(error);
                });
        }
    });
</script>
@endsection
