@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.events.page-settings.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            Events Page Settings
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="hero_label">Top Label</label>
                    <input type="text" class="form-control {{ $errors->has('hero_label') ? 'is-invalid' : '' }}" name="hero_label" id="hero_label" value="{{ old('hero_label', $settings['hero_label'] ?? '') }}" required>
                    @if($errors->has('hero_label'))
                        <div class="invalid-feedback">{{ $errors->first('hero_label') }}</div>
                    @endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="hero_title">Main Title</label>
                    <input type="text" class="form-control {{ $errors->has('hero_title') ? 'is-invalid' : '' }}" name="hero_title" id="hero_title" value="{{ old('hero_title', $settings['hero_title'] ?? '') }}" required>
                    @if($errors->has('hero_title'))
                        <div class="invalid-feedback">{{ $errors->first('hero_title') }}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="hero_title_suffix">Title Suffix</label>
                    <input type="text" class="form-control {{ $errors->has('hero_title_suffix') ? 'is-invalid' : '' }}" name="hero_title_suffix" id="hero_title_suffix" value="{{ old('hero_title_suffix', $settings['hero_title_suffix'] ?? '') }}">
                    @if($errors->has('hero_title_suffix'))
                        <div class="invalid-feedback">{{ $errors->first('hero_title_suffix') }}</div>
                    @endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="upcoming_title">Upcoming Section Title</label>
                    <input type="text" class="form-control {{ $errors->has('upcoming_title') ? 'is-invalid' : '' }}" name="upcoming_title" id="upcoming_title" value="{{ old('upcoming_title', $settings['upcoming_title'] ?? '') }}" required>
                    @if($errors->has('upcoming_title'))
                        <div class="invalid-feedback">{{ $errors->first('upcoming_title') }}</div>
                    @endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="past_title">Past Section Title</label>
                    <input type="text" class="form-control {{ $errors->has('past_title') ? 'is-invalid' : '' }}" name="past_title" id="past_title" value="{{ old('past_title', $settings['past_title'] ?? '') }}" required>
                    @if($errors->has('past_title'))
                        <div class="invalid-feedback">{{ $errors->first('past_title') }}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="testimonial_title">Footer Testimonial Title</label>
                    <input type="text" class="form-control {{ $errors->has('testimonial_title') ? 'is-invalid' : '' }}" name="testimonial_title" id="testimonial_title" value="{{ old('testimonial_title', $settings['testimonial_title'] ?? '') }}" required>
                    @if($errors->has('testimonial_title'))
                        <div class="invalid-feedback">{{ $errors->first('testimonial_title') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="hero_description">Description</label>
                <textarea class="form-control {{ $errors->has('hero_description') ? 'is-invalid' : '' }}" name="hero_description" id="hero_description" rows="3" required>{{ old('hero_description', $settings['hero_description'] ?? '') }}</textarea>
                @if($errors->has('hero_description'))
                    <div class="invalid-feedback">{{ $errors->first('hero_description') }}</div>
                @endif
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
