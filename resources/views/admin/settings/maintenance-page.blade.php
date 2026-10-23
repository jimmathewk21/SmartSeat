@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Maintenance Page Settings</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.maintenance-page.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="col-md-4 form-group">
                    <label class="required" for="enabled">Maintenance Page Status</label>
                    <select class="form-control {{ $errors->has('enabled') ? 'is-invalid' : '' }}" name="enabled" id="enabled" required>
                        <option value="1" @selected((string) old('enabled', $settings['enabled'] ? '1' : '0') === '1')>Enabled</option>
                        <option value="0" @selected((string) old('enabled', $settings['enabled'] ? '1' : '0') === '0')>Disabled</option>
                    </select>
                    @if($errors->has('enabled'))
                        <div class="invalid-feedback">{{ $errors->first('enabled') }}</div>
                    @endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="inquiry_email">Inquiry Email</label>
                    <input type="email" class="form-control {{ $errors->has('inquiry_email') ? 'is-invalid' : '' }}" name="inquiry_email" id="inquiry_email" value="{{ old('inquiry_email', $settings['inquiry_email'] ?? '') }}">
                    @if($errors->has('inquiry_email'))
                        <div class="invalid-feedback">{{ $errors->first('inquiry_email') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-4 form-group">
                    <label for="background_image">Background Image URL/Path</label>
                    <input type="text" class="form-control {{ $errors->has('background_image') ? 'is-invalid' : '' }}" name="background_image" id="background_image" value="{{ old('background_image', $settings['background_image'] ?? '') }}" placeholder="uploads/maintenance-page/background.webp or https://...">
                    @if($errors->has('background_image'))
                        <div class="invalid-feedback">{{ $errors->first('background_image') }}</div>
                    @endif
                    <div class="mt-2">
                        <input class="form-control-file {{ $errors->has('background_image_file') ? 'is-invalid' : '' }}" type="file" name="background_image_file" id="background_image_file" accept=".jpg,.jpeg,.png,.webp">
                        @if($errors->has('background_image_file'))
                            <div class="invalid-feedback d-block">{{ $errors->first('background_image_file') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label for="logo_url">Logo URL/Path</label>
                    <input type="text" class="form-control {{ $errors->has('logo_url') ? 'is-invalid' : '' }}" name="logo_url" id="logo_url" value="{{ old('logo_url', $settings['logo_url'] ?? '') }}" placeholder="uploads/maintenance-page/logo.webp or https://...">
                    @if($errors->has('logo_url'))
                        <div class="invalid-feedback">{{ $errors->first('logo_url') }}</div>
                    @endif
                    <div class="mt-2">
                        <input class="form-control-file {{ $errors->has('logo_file') ? 'is-invalid' : '' }}" type="file" name="logo_file" id="logo_file" accept=".jpg,.jpeg,.png,.webp,.svg">
                        @if($errors->has('logo_file'))
                            <div class="invalid-feedback d-block">{{ $errors->first('logo_file') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label for="badge_icon">Center Badge Icon URL/Path</label>
                    <input type="text" class="form-control {{ $errors->has('badge_icon') ? 'is-invalid' : '' }}" name="badge_icon" id="badge_icon" value="{{ old('badge_icon', $settings['badge_icon'] ?? '') }}" placeholder="uploads/maintenance-page/icon.webp or https://...">
                    @if($errors->has('badge_icon'))
                        <div class="invalid-feedback">{{ $errors->first('badge_icon') }}</div>
                    @endif
                    <div class="mt-2">
                        <input class="form-control-file {{ $errors->has('badge_icon_file') ? 'is-invalid' : '' }}" type="file" name="badge_icon_file" id="badge_icon_file" accept=".jpg,.jpeg,.png,.webp,.svg,.ico">
                        @if($errors->has('badge_icon_file'))
                            <div class="invalid-feedback d-block">{{ $errors->first('badge_icon_file') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title" id="title" value="{{ old('title', $settings['title'] ?? '') }}" placeholder="Website Under Maintenance">
                @if($errors->has('title'))
                    <div class="invalid-feedback">{{ $errors->first('title') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="headline">Main Content</label>
                <textarea class="form-control {{ $errors->has('headline') ? 'is-invalid' : '' }}" name="headline" id="headline" rows="3" required>{{ old('headline', $settings['headline'] ?? '') }}</textarea>
                @if($errors->has('headline'))
                    <div class="invalid-feedback">{{ $errors->first('headline') }}</div>
                @endif
            </div>

            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label class="required" for="thanks_text">Thank You Text</label>
                    <input type="text" class="form-control {{ $errors->has('thanks_text') ? 'is-invalid' : '' }}" name="thanks_text" id="thanks_text" value="{{ old('thanks_text', $settings['thanks_text'] ?? '') }}" required>
                    @if($errors->has('thanks_text'))
                        <div class="invalid-feedback">{{ $errors->first('thanks_text') }}</div>
                    @endif
                </div>
                <div class="col-md-6 form-group">
                    <label class="required" for="inquiry_prefix">Inquiry Line Prefix</label>
                    <input type="text" class="form-control {{ $errors->has('inquiry_prefix') ? 'is-invalid' : '' }}" name="inquiry_prefix" id="inquiry_prefix" value="{{ old('inquiry_prefix', $settings['inquiry_prefix'] ?? '') }}" required>
                    @if($errors->has('inquiry_prefix'))
                        <div class="invalid-feedback">{{ $errors->first('inquiry_prefix') }}</div>
                    @endif
                </div>
            </div>

            <button class="btn btn-primary" type="submit">Save Settings</button>
            <a class="btn btn-default" href="{{ route('admin.settings.index') }}">Cancel</a>
        </form>
    </div>
</div>
@endsection
