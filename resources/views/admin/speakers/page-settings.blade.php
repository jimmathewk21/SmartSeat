@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.speakers.page-settings.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            Speaker Page Settings
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
                    <input type="text" class="form-control {{ $errors->has('subtitle_suffix') ? 'is-invalid' : '' }}" name="subtitle_suffix" id="subtitle_suffix" value="{{ old('subtitle_suffix', $settings['subtitle_suffix'] ?? '') }}" placeholder="meet the experts">
                    @if($errors->has('subtitle_suffix'))
                        <div class="invalid-feedback">{{ $errors->first('subtitle_suffix') }}</div>
                    @endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="guidelines_button_text">Guidelines Button Text</label>
                    <input type="text" class="form-control {{ $errors->has('guidelines_button_text') ? 'is-invalid' : '' }}" name="guidelines_button_text" id="guidelines_button_text" value="{{ old('guidelines_button_text', $settings['guidelines_button_text'] ?? '') }}" required>
                    @if($errors->has('guidelines_button_text'))
                        <div class="invalid-feedback">{{ $errors->first('guidelines_button_text') }}</div>
                    @endif
                </div>
                <div class="col-md-3 form-group">
                    <label for="become_button_text">Become Button Text</label>
                    <input type="text" class="form-control {{ $errors->has('become_button_text') ? 'is-invalid' : '' }}" name="become_button_text" id="become_button_text" value="{{ old('become_button_text', $settings['become_button_text'] ?? '') }}" required>
                    @if($errors->has('become_button_text'))
                        <div class="invalid-feedback">{{ $errors->first('become_button_text') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description" rows="3" required>{{ old('description', $settings['description'] ?? '') }}</textarea>
                @if($errors->has('description'))
                    <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="guidelines_content">Speaker Guidelines Content (Popup)</label>
                <textarea class="form-control {{ $errors->has('guidelines_content') ? 'is-invalid' : '' }}" name="guidelines_content" id="guidelines_content" rows="10" required>{{ old('guidelines_content', $settings['guidelines_content'] ?? '') }}</textarea>
                <small class="form-text text-muted">This content is shown in the Speaker Guidelines modal on <code>/speakers</code>. HTML is supported.</small>
                @if($errors->has('guidelines_content'))
                    <div class="invalid-feedback">{{ $errors->first('guidelines_content') }}</div>
                @endif
            </div>

            <hr>
            <h5 class="mb-3">Become Speaker Page Hero</h5>

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
                <textarea class="form-control {{ $errors->has('apply_page_description') ? 'is-invalid' : '' }}" name="apply_page_description" id="apply_page_description" rows="3" required>{{ old('apply_page_description', $settings['apply_page_description'] ?? '') }}</textarea>
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
