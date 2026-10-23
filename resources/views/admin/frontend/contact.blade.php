@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.frontend.contact.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">Contact Us Page</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="hero_label">Top Label</label>
                    <input type="text" class="form-control {{ $errors->has('hero_label') ? 'is-invalid' : '' }}" name="hero_label" id="hero_label" value="{{ old('hero_label', $settings['hero_label'] ?? '') }}" required>
                    @if($errors->has('hero_label'))<div class="invalid-feedback">{{ $errors->first('hero_label') }}</div>@endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="hero_title">Main Title</label>
                    <input type="text" class="form-control {{ $errors->has('hero_title') ? 'is-invalid' : '' }}" name="hero_title" id="hero_title" value="{{ old('hero_title', $settings['hero_title'] ?? '') }}" required>
                    @if($errors->has('hero_title'))<div class="invalid-feedback">{{ $errors->first('hero_title') }}</div>@endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="hero_title_suffix">Title Suffix</label>
                    <input type="text" class="form-control {{ $errors->has('hero_title_suffix') ? 'is-invalid' : '' }}" name="hero_title_suffix" id="hero_title_suffix" value="{{ old('hero_title_suffix', $settings['hero_title_suffix'] ?? '') }}">
                    @if($errors->has('hero_title_suffix'))<div class="invalid-feedback">{{ $errors->first('hero_title_suffix') }}</div>@endif
                </div>
            </div>

            <div class="form-group">
                <label for="hero_description">Description</label>
                <textarea class="form-control {{ $errors->has('hero_description') ? 'is-invalid' : '' }}" name="hero_description" id="hero_description" rows="3" required>{{ old('hero_description', $settings['hero_description'] ?? '') }}</textarea>
                @if($errors->has('hero_description'))<div class="invalid-feedback">{{ $errors->first('hero_description') }}</div>@endif
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="organizer_heading">Organizer Heading</label>
                    <input type="text" class="form-control {{ $errors->has('organizer_heading') ? 'is-invalid' : '' }}" name="organizer_heading" id="organizer_heading" value="{{ old('organizer_heading', $settings['organizer_heading'] ?? '') }}" required>
                    @if($errors->has('organizer_heading'))<div class="invalid-feedback">{{ $errors->first('organizer_heading') }}</div>@endif
                </div>
                <div class="col-md-5 form-group">
                    <label for="organizer_logo_url">Organizer Logo URL/Path</label>
                    <input type="text" class="form-control {{ $errors->has('organizer_logo_url') ? 'is-invalid' : '' }}" name="organizer_logo_url" id="organizer_logo_url" value="{{ old('organizer_logo_url', $settings['organizer_logo_url'] ?? '') }}">
                    @if($errors->has('organizer_logo_url'))<div class="invalid-feedback">{{ $errors->first('organizer_logo_url') }}</div>@endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="organizer_logo_file">Upload Logo</label>
                    <input type="file" class="form-control-file {{ $errors->has('organizer_logo_file') ? 'is-invalid' : '' }}" name="organizer_logo_file" id="organizer_logo_file" accept="image/*">
                    @if($errors->has('organizer_logo_file'))<div class="invalid-feedback d-block">{{ $errors->first('organizer_logo_file') }}</div>@endif
                    <small class="form-text text-muted">Recommended image size: 400x160px.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="address_heading">Address Heading</label>
                    <input type="text" class="form-control {{ $errors->has('address_heading') ? 'is-invalid' : '' }}" name="address_heading" id="address_heading" value="{{ old('address_heading', $settings['address_heading'] ?? '') }}" required>
                    @if($errors->has('address_heading'))<div class="invalid-feedback">{{ $errors->first('address_heading') }}</div>@endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="contact_heading">Contact Heading</label>
                    <input type="text" class="form-control {{ $errors->has('contact_heading') ? 'is-invalid' : '' }}" name="contact_heading" id="contact_heading" value="{{ old('contact_heading', $settings['contact_heading'] ?? '') }}" required>
                    @if($errors->has('contact_heading'))<div class="invalid-feedback">{{ $errors->first('contact_heading') }}</div>@endif
                </div>
            </div>

            <div class="form-group">
                <label for="address_text">Address Text</label>
                <textarea class="form-control {{ $errors->has('address_text') ? 'is-invalid' : '' }}" name="address_text" id="address_text" rows="3" required>{{ old('address_text', $settings['address_text'] ?? '') }}</textarea>
                @if($errors->has('address_text'))<div class="invalid-feedback">{{ $errors->first('address_text') }}</div>@endif
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="contact_email">Contact Email</label>
                    <input type="text" class="form-control {{ $errors->has('contact_email') ? 'is-invalid' : '' }}" name="contact_email" id="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" required>
                    @if($errors->has('contact_email'))<div class="invalid-feedback">{{ $errors->first('contact_email') }}</div>@endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="contact_phone">Contact Phone</label>
                    <input type="text" class="form-control {{ $errors->has('contact_phone') ? 'is-invalid' : '' }}" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" required>
                    @if($errors->has('contact_phone'))<div class="invalid-feedback">{{ $errors->first('contact_phone') }}</div>@endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="hours_heading">Business Hours Heading</label>
                    <input type="text" class="form-control {{ $errors->has('hours_heading') ? 'is-invalid' : '' }}" name="hours_heading" id="hours_heading" value="{{ old('hours_heading', $settings['hours_heading'] ?? '') }}" required>
                    @if($errors->has('hours_heading'))<div class="invalid-feedback">{{ $errors->first('hours_heading') }}</div>@endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="social_heading">Social Media Heading</label>
                    <input type="text" class="form-control {{ $errors->has('social_heading') ? 'is-invalid' : '' }}" name="social_heading" id="social_heading" value="{{ old('social_heading', $settings['social_heading'] ?? '') }}" required>
                    @if($errors->has('social_heading'))<div class="invalid-feedback">{{ $errors->first('social_heading') }}</div>@endif
                </div>
            </div>

            <div class="form-group">
                <label for="hours_text">Business Hours Text</label>
                <textarea class="form-control {{ $errors->has('hours_text') ? 'is-invalid' : '' }}" name="hours_text" id="hours_text" rows="3" required>{{ old('hours_text', $settings['hours_text'] ?? '') }}</textarea>
                @if($errors->has('hours_text'))<div class="invalid-feedback">{{ $errors->first('hours_text') }}</div>@endif
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="social_linkedin_url">LinkedIn URL</label>
                    <input type="text" class="form-control" name="social_linkedin_url" id="social_linkedin_url" value="{{ old('social_linkedin_url', $settings['social_linkedin_url'] ?? '') }}">
                </div>
                <div class="col-md-6 form-group">
                    <label for="social_facebook_url">Facebook URL</label>
                    <input type="text" class="form-control" name="social_facebook_url" id="social_facebook_url" value="{{ old('social_facebook_url', $settings['social_facebook_url'] ?? '') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="social_x_url">X URL</label>
                    <input type="text" class="form-control" name="social_x_url" id="social_x_url" value="{{ old('social_x_url', $settings['social_x_url'] ?? '') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="social_instagram_url">Instagram URL</label>
                    <input type="text" class="form-control" name="social_instagram_url" id="social_instagram_url" value="{{ old('social_instagram_url', $settings['social_instagram_url'] ?? '') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="social_whatsapp_url">WhatsApp URL</label>
                    <input type="text" class="form-control" name="social_whatsapp_url" id="social_whatsapp_url" value="{{ old('social_whatsapp_url', $settings['social_whatsapp_url'] ?? '') }}">
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="form_title">Form Title</label>
                    <input type="text" class="form-control {{ $errors->has('form_title') ? 'is-invalid' : '' }}" name="form_title" id="form_title" value="{{ old('form_title', $settings['form_title'] ?? '') }}" required>
                    @if($errors->has('form_title'))<div class="invalid-feedback">{{ $errors->first('form_title') }}</div>@endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="form_button_text">Submit Button Text</label>
                    <input type="text" class="form-control {{ $errors->has('form_button_text') ? 'is-invalid' : '' }}" name="form_button_text" id="form_button_text" value="{{ old('form_button_text', $settings['form_button_text'] ?? '') }}" required>
                    @if($errors->has('form_button_text'))<div class="invalid-feedback">{{ $errors->first('form_button_text') }}</div>@endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="form_name_placeholder">Name Placeholder</label>
                    <input type="text" class="form-control {{ $errors->has('form_name_placeholder') ? 'is-invalid' : '' }}" name="form_name_placeholder" id="form_name_placeholder" value="{{ old('form_name_placeholder', $settings['form_name_placeholder'] ?? '') }}" required>
                    @if($errors->has('form_name_placeholder'))<div class="invalid-feedback">{{ $errors->first('form_name_placeholder') }}</div>@endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="form_email_placeholder">Email Placeholder</label>
                    <input type="text" class="form-control {{ $errors->has('form_email_placeholder') ? 'is-invalid' : '' }}" name="form_email_placeholder" id="form_email_placeholder" value="{{ old('form_email_placeholder', $settings['form_email_placeholder'] ?? '') }}" required>
                    @if($errors->has('form_email_placeholder'))<div class="invalid-feedback">{{ $errors->first('form_email_placeholder') }}</div>@endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="form_subject_placeholder">Subject Placeholder</label>
                    <input type="text" class="form-control {{ $errors->has('form_subject_placeholder') ? 'is-invalid' : '' }}" name="form_subject_placeholder" id="form_subject_placeholder" value="{{ old('form_subject_placeholder', $settings['form_subject_placeholder'] ?? '') }}" required>
                    @if($errors->has('form_subject_placeholder'))<div class="invalid-feedback">{{ $errors->first('form_subject_placeholder') }}</div>@endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 form-group">
                    <label for="form_message_placeholder">Message Placeholder</label>
                    <input type="text" class="form-control {{ $errors->has('form_message_placeholder') ? 'is-invalid' : '' }}" name="form_message_placeholder" id="form_message_placeholder" value="{{ old('form_message_placeholder', $settings['form_message_placeholder'] ?? '') }}" required>
                    @if($errors->has('form_message_placeholder'))<div class="invalid-feedback">{{ $errors->first('form_message_placeholder') }}</div>@endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="testimonial_title">Footer Testimonial Title</label>
                    <input type="text" class="form-control {{ $errors->has('testimonial_title') ? 'is-invalid' : '' }}" name="testimonial_title" id="testimonial_title" value="{{ old('testimonial_title', $settings['testimonial_title'] ?? '') }}" required>
                    @if($errors->has('testimonial_title'))<div class="invalid-feedback">{{ $errors->first('testimonial_title') }}</div>@endif
                </div>
            </div>

            <div class="form-group">
                <label for="form_success_message">Success Message</label>
                <textarea class="form-control {{ $errors->has('form_success_message') ? 'is-invalid' : '' }}" name="form_success_message" id="form_success_message" rows="3" required>{{ old('form_success_message', $settings['form_success_message'] ?? '') }}</textarea>
                @if($errors->has('form_success_message'))<div class="invalid-feedback">{{ $errors->first('form_success_message') }}</div>@endif
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
