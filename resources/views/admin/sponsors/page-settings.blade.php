@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.sponsors.page-settings.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            Sponsor Page Settings
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="page_title">Page Title</label>
                <input class="form-control {{ $errors->has('page_title') ? 'is-invalid' : '' }}" type="text" name="page_title" id="page_title" value="{{ old('page_title', $settings['page_title'] ?? '') }}" required>
                @if($errors->has('page_title'))
                    <div class="invalid-feedback">{{ $errors->first('page_title') }}</div>
                @endif
            </div>

            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label for="subtitle">Subtitle Label</label>
                    <input class="form-control {{ $errors->has('subtitle') ? 'is-invalid' : '' }}" type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $settings['subtitle'] ?? '') }}" required>
                    @if($errors->has('subtitle'))
                        <div class="invalid-feedback">{{ $errors->first('subtitle') }}</div>
                    @endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="subtitle_suffix">Subtitle Suffix</label>
                    <input class="form-control {{ $errors->has('subtitle_suffix') ? 'is-invalid' : '' }}" type="text" name="subtitle_suffix" id="subtitle_suffix" value="{{ old('subtitle_suffix', $settings['subtitle_suffix'] ?? '') }}">
                    @if($errors->has('subtitle_suffix'))
                        <div class="invalid-feedback">{{ $errors->first('subtitle_suffix') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description Text</label>
                <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description" rows="5" required>{{ old('description', $settings['description'] ?? '') }}</textarea>
                @if($errors->has('description'))
                    <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                @endif
            </div>

            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label for="button_text">Button Text</label>
                    <input class="form-control {{ $errors->has('button_text') ? 'is-invalid' : '' }}" type="text" name="button_text" id="button_text" value="{{ old('button_text', $settings['button_text'] ?? '') }}" required>
                    @if($errors->has('button_text'))
                        <div class="invalid-feedback">{{ $errors->first('button_text') }}</div>
                    @endif
                </div>
                <div class="col-md-6 form-group">
                    <label for="button_link">Button Link</label>
                    <input class="form-control {{ $errors->has('button_link') ? 'is-invalid' : '' }}" type="text" name="button_link" id="button_link" value="{{ old('button_link', $settings['button_link'] ?? '') }}" required>
                    @if($errors->has('button_link'))
                        <div class="invalid-feedback">{{ $errors->first('button_link') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label for="guidelines_button_text">Guidelines Button Text</label>
                    <input class="form-control {{ $errors->has('guidelines_button_text') ? 'is-invalid' : '' }}" type="text" name="guidelines_button_text" id="guidelines_button_text" value="{{ old('guidelines_button_text', $settings['guidelines_button_text'] ?? '') }}">
                    @if($errors->has('guidelines_button_text'))
                        <div class="invalid-feedback">{{ $errors->first('guidelines_button_text') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="guidelines_content">Sponsor Guidelines Content (Popup)</label>
                <textarea class="form-control {{ $errors->has('guidelines_content') ? 'is-invalid' : '' }}" name="guidelines_content" id="guidelines_content" rows="5">{{ old('guidelines_content', $settings['guidelines_content'] ?? '') }}</textarea>
                <small class="form-text text-muted">HTML is supported.</small>
                @if($errors->has('guidelines_content'))
                    <div class="invalid-feedback">{{ $errors->first('guidelines_content') }}</div>
                @endif
            </div>

            <hr>

            <h5 class="mb-3">Become Sponsor Page</h5>

            <div class="form-row">
                <div class="col-md-4 form-group">
                    <label for="apply_page_label">Top Label</label>
                    <input class="form-control {{ $errors->has('apply_page_label') ? 'is-invalid' : '' }}" type="text" name="apply_page_label" id="apply_page_label" value="{{ old('apply_page_label', $settings['apply_page_label'] ?? '') }}">
                    @if($errors->has('apply_page_label'))
                        <div class="invalid-feedback">{{ $errors->first('apply_page_label') }}</div>
                    @endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="apply_page_title">Main Title</label>
                    <input class="form-control {{ $errors->has('apply_page_title') ? 'is-invalid' : '' }}" type="text" name="apply_page_title" id="apply_page_title" value="{{ old('apply_page_title', $settings['apply_page_title'] ?? '') }}" required>
                    @if($errors->has('apply_page_title'))
                        <div class="invalid-feedback">{{ $errors->first('apply_page_title') }}</div>
                    @endif
                </div>
                <div class="col-md-4 form-group">
                    <label for="apply_page_title_suffix">Title Suffix</label>
                    <input class="form-control {{ $errors->has('apply_page_title_suffix') ? 'is-invalid' : '' }}" type="text" name="apply_page_title_suffix" id="apply_page_title_suffix" value="{{ old('apply_page_title_suffix', $settings['apply_page_title_suffix'] ?? '') }}">
                    @if($errors->has('apply_page_title_suffix'))
                        <div class="invalid-feedback">{{ $errors->first('apply_page_title_suffix') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="apply_page_description">Description</label>
                <textarea class="form-control {{ $errors->has('apply_page_description') ? 'is-invalid' : '' }}" name="apply_page_description" id="apply_page_description" rows="4">{{ old('apply_page_description', $settings['apply_page_description'] ?? '') }}</textarea>
                @if($errors->has('apply_page_description'))
                    <div class="invalid-feedback">{{ $errors->first('apply_page_description') }}</div>
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
