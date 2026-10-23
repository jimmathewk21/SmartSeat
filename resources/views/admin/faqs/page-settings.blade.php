@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.faqs.page-settings.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">FAQ Page Settings</div>
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
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description" rows="4" required>{{ old('description', $settings['description'] ?? '') }}</textarea>
                @if($errors->has('description'))
                    <div class="invalid-feedback">{{ $errors->first('description') }}</div>
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
