@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Testimonials Page Settings</div>
    <div class="card-body">
        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.testimonials.page-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="col-md-4 form-group">
                    <label for="hero_label">Header Label</label>
                    <input type="text" class="form-control {{ $errors->has('hero_label') ? 'is-invalid' : '' }}" name="hero_label" id="hero_label" value="{{ old('hero_label', $settings['hero_label'] ?? '') }}">
                    @if($errors->has('hero_label'))
                        <div class="invalid-feedback">{{ $errors->first('hero_label') }}</div>
                    @endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="hero_title">Header Title</label>
                    <input type="text" class="form-control {{ $errors->has('hero_title') ? 'is-invalid' : '' }}" name="hero_title" id="hero_title" value="{{ old('hero_title', $settings['hero_title'] ?? '') }}" required>
                    @if($errors->has('hero_title'))
                        <div class="invalid-feedback">{{ $errors->first('hero_title') }}</div>
                    @endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="hero_suffix">Header Suffix</label>
                    <input type="text" class="form-control {{ $errors->has('hero_suffix') ? 'is-invalid' : '' }}" name="hero_suffix" id="hero_suffix" value="{{ old('hero_suffix', $settings['hero_suffix'] ?? '') }}">
                    @if($errors->has('hero_suffix'))
                        <div class="invalid-feedback">{{ $errors->first('hero_suffix') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="hero_description">Header Description</label>
                <textarea class="form-control {{ $errors->has('hero_description') ? 'is-invalid' : '' }}" name="hero_description" id="hero_description" rows="3">{{ old('hero_description', $settings['hero_description'] ?? '') }}</textarea>
                @if($errors->has('hero_description'))
                    <div class="invalid-feedback">{{ $errors->first('hero_description') }}</div>
                @endif
            </div>

            <div class="form-row">
                <div class="col-md-3 form-group">
                    <label for="filter_title">Filter Panel Title</label>
                    <input type="text" class="form-control {{ $errors->has('filter_title') ? 'is-invalid' : '' }}" name="filter_title" id="filter_title" value="{{ old('filter_title', $settings['filter_title'] ?? '') }}">
                    @if($errors->has('filter_title'))
                        <div class="invalid-feedback">{{ $errors->first('filter_title') }}</div>
                    @endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="search_placeholder">Search Placeholder</label>
                    <input type="text" class="form-control {{ $errors->has('search_placeholder') ? 'is-invalid' : '' }}" name="search_placeholder" id="search_placeholder" value="{{ old('search_placeholder', $settings['search_placeholder'] ?? '') }}">
                    @if($errors->has('search_placeholder'))
                        <div class="invalid-feedback">{{ $errors->first('search_placeholder') }}</div>
                    @endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="feedback_title">Feedback Form Title</label>
                    <input type="text" class="form-control {{ $errors->has('feedback_title') ? 'is-invalid' : '' }}" name="feedback_title" id="feedback_title" value="{{ old('feedback_title', $settings['feedback_title'] ?? '') }}">
                    @if($errors->has('feedback_title'))
                        <div class="invalid-feedback">{{ $errors->first('feedback_title') }}</div>
                    @endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="empty_state_text">Empty State Text</label>
                    <input type="text" class="form-control {{ $errors->has('empty_state_text') ? 'is-invalid' : '' }}" name="empty_state_text" id="empty_state_text" value="{{ old('empty_state_text', $settings['empty_state_text'] ?? '') }}">
                    @if($errors->has('empty_state_text'))
                        <div class="invalid-feedback">{{ $errors->first('empty_state_text') }}</div>
                    @endif
                </div>
            </div>

            <button class="btn btn-primary" type="submit">Save Settings</button>
            <a class="btn btn-default" href="{{ route('admin.testimonials.index') }}">Cancel</a>
        </form>
    </div>
</div>
@endsection
