@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.frontend.auth-page.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">Auth Pages</div>
        <div class="card-body">
            <div class="mb-4">
                <h5 class="mb-1">Shared Auth Content</h5>
                <p class="text-muted mb-0">These settings are used on both the Sign Up and Sign In pages.</p>
            </div>
            <div class="row">
                <div class="col-md-8 form-group">
                    <label for="brand_image_url">Brand Image URL/Path</label>
                    <input type="text" class="form-control {{ $errors->has('brand_image_url') ? 'is-invalid' : '' }}" name="brand_image_url" id="brand_image_url" value="{{ old('brand_image_url', $settings['brand_image_url'] ?? '') }}">
                    @if($errors->has('brand_image_url'))<div class="invalid-feedback">{{ $errors->first('brand_image_url') }}</div>@endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="brand_image_file">Upload Brand Image</label>
                    <input type="file" class="form-control-file {{ $errors->has('brand_image_file') ? 'is-invalid' : '' }}" name="brand_image_file" id="brand_image_file" accept="image/*">
                    @if($errors->has('brand_image_file'))<div class="invalid-feedback d-block">{{ $errors->first('brand_image_file') }}</div>@endif
                    <small class="form-text text-muted">Recommended image size: 600x220px.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="ip_label">IP Box Label</label>
                    <input type="text" class="form-control {{ $errors->has('ip_label') ? 'is-invalid' : '' }}" name="ip_label" id="ip_label" value="{{ old('ip_label', $settings['ip_label'] ?? '') }}" required>
                    @if($errors->has('ip_label'))<div class="invalid-feedback">{{ $errors->first('ip_label') }}</div>@endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="location_label">Location Box Label</label>
                    <input type="text" class="form-control {{ $errors->has('location_label') ? 'is-invalid' : '' }}" name="location_label" id="location_label" value="{{ old('location_label', $settings['location_label'] ?? '') }}">
                    @if($errors->has('location_label'))<div class="invalid-feedback">{{ $errors->first('location_label') }}</div>@endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="location_value">Location Value</label>
                    <input type="text" class="form-control {{ $errors->has('location_value') ? 'is-invalid' : '' }}" name="location_value" id="location_value" value="{{ old('location_value', $settings['location_value'] ?? '') }}">
                    @if($errors->has('location_value'))<div class="invalid-feedback">{{ $errors->first('location_value') }}</div>@endif
                </div>
            </div>

            <hr>

            <div class="mb-3">
                <h5 class="mb-1">Sign Up Page</h5>
                <p class="text-muted mb-0">Registration page content and labels.</p>
            </div>

            <div class="form-group">
                <label for="signup_left_content">Left Content</label>
                <textarea class="form-control {{ $errors->has('signup_left_content') ? 'is-invalid' : '' }}" name="signup_left_content" id="signup_left_content" rows="8" required>{{ old('signup_left_content', $settings['signup_left_content'] ?? '') }}</textarea>
                @if($errors->has('signup_left_content'))<div class="invalid-feedback">{{ $errors->first('signup_left_content') }}</div>@endif
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="signup_form_title">Sign Up Title</label>
                    <input type="text" class="form-control {{ $errors->has('signup_form_title') ? 'is-invalid' : '' }}" name="signup_form_title" id="signup_form_title" value="{{ old('signup_form_title', $settings['signup_form_title'] ?? '') }}" required>
                    @if($errors->has('signup_form_title'))<div class="invalid-feedback">{{ $errors->first('signup_form_title') }}</div>@endif
                </div>
                <div class="col-md-5 form-group">
                    <label for="signup_form_subtitle">Sign Up Subtitle</label>
                    <input type="text" class="form-control {{ $errors->has('signup_form_subtitle') ? 'is-invalid' : '' }}" name="signup_form_subtitle" id="signup_form_subtitle" value="{{ old('signup_form_subtitle', $settings['signup_form_subtitle'] ?? '') }}" required>
                    @if($errors->has('signup_form_subtitle'))<div class="invalid-feedback">{{ $errors->first('signup_form_subtitle') }}</div>@endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="signup_button_text">Sign Up Button</label>
                    <input type="text" class="form-control {{ $errors->has('signup_button_text') ? 'is-invalid' : '' }}" name="signup_button_text" id="signup_button_text" value="{{ old('signup_button_text', $settings['signup_button_text'] ?? '') }}" required>
                    @if($errors->has('signup_button_text'))<div class="invalid-feedback">{{ $errors->first('signup_button_text') }}</div>@endif
                </div>
            </div>

            <div class="form-group">
                <label for="verification_note">Sign Up Bottom Note</label>
                <input type="text" class="form-control {{ $errors->has('verification_note') ? 'is-invalid' : '' }}" name="verification_note" id="verification_note" value="{{ old('verification_note', $settings['verification_note'] ?? '') }}" required>
                @if($errors->has('verification_note'))<div class="invalid-feedback">{{ $errors->first('verification_note') }}</div>@endif
            </div>

            <hr>

            <div class="mb-3">
                <h5 class="mb-1">Sign In Page</h5>
                <p class="text-muted mb-0">Login page content and labels.</p>
            </div>

            <div class="form-group">
                <label for="login_left_content">Left Content</label>
                <textarea class="form-control {{ $errors->has('login_left_content') ? 'is-invalid' : '' }}" name="login_left_content" id="login_left_content" rows="8" required>{{ old('login_left_content', $settings['login_left_content'] ?? '') }}</textarea>
                @if($errors->has('login_left_content'))<div class="invalid-feedback">{{ $errors->first('login_left_content') }}</div>@endif
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="login_form_title">Sign In Title</label>
                    <input type="text" class="form-control {{ $errors->has('login_form_title') ? 'is-invalid' : '' }}" name="login_form_title" id="login_form_title" value="{{ old('login_form_title', $settings['login_form_title'] ?? '') }}" required>
                    @if($errors->has('login_form_title'))<div class="invalid-feedback">{{ $errors->first('login_form_title') }}</div>@endif
                </div>
                <div class="col-md-5 form-group">
                    <label for="login_form_subtitle">Sign In Subtitle</label>
                    <input type="text" class="form-control {{ $errors->has('login_form_subtitle') ? 'is-invalid' : '' }}" name="login_form_subtitle" id="login_form_subtitle" value="{{ old('login_form_subtitle', $settings['login_form_subtitle'] ?? '') }}" required>
                    @if($errors->has('login_form_subtitle'))<div class="invalid-feedback">{{ $errors->first('login_form_subtitle') }}</div>@endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="login_button_text">Sign In Button</label>
                    <input type="text" class="form-control {{ $errors->has('login_button_text') ? 'is-invalid' : '' }}" name="login_button_text" id="login_button_text" value="{{ old('login_button_text', $settings['login_button_text'] ?? '') }}" required>
                    @if($errors->has('login_button_text'))<div class="invalid-feedback">{{ $errors->first('login_button_text') }}</div>@endif
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
