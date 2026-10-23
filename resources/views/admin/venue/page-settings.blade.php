@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.venue.page-settings.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            Venue Page Settings
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="hero_label">Top Label</label>
                    <input type="text" class="form-control {{ $errors->has('hero_label') ? 'is-invalid' : '' }}" name="hero_label" id="hero_label" value="{{ old('hero_label', $settings['hero_label'] ?? '') }}">
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
                    <small class="form-text text-muted">This is shown together with Title Suffix on frontend heading.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="hero_title_suffix">Title Suffix</label>
                    <input type="text" class="form-control {{ $errors->has('hero_title_suffix') ? 'is-invalid' : '' }}" name="hero_title_suffix" id="hero_title_suffix" value="{{ old('hero_title_suffix', $settings['hero_title_suffix'] ?? '') }}">
                    @if($errors->has('hero_title_suffix'))
                        <div class="invalid-feedback">{{ $errors->first('hero_title_suffix') }}</div>
                    @endif
                    <small class="form-text text-muted">Example display: Main Title / Title Suffix</small>
                </div>
                <div class="col-md-6 form-group">
                    <label for="empty_state_text">Empty State Text</label>
                    <input type="text" class="form-control {{ $errors->has('empty_state_text') ? 'is-invalid' : '' }}" name="empty_state_text" id="empty_state_text" value="{{ old('empty_state_text', $settings['empty_state_text'] ?? '') }}">
                    @if($errors->has('empty_state_text'))
                        <div class="invalid-feedback">{{ $errors->first('empty_state_text') }}</div>
                    @endif
                    <small class="form-text text-muted">Shown on the frontend venue list when no active hotels are available.</small>
                </div>
            </div>

            <div class="form-group">
                <label>Live Title Preview</label>
                <div class="border rounded px-3 py-2 bg-light" id="venue-title-preview"></div>
                <small class="form-text text-muted">Main Title and Title Suffix are shown together on the frontend hero.</small>
            </div>

            <div class="form-group">
                <label for="hero_description">Description</label>
                <textarea class="form-control ckeditor {{ $errors->has('hero_description') ? 'is-invalid' : '' }}" name="hero_description" id="hero_description" rows="3">{{ old('hero_description', $settings['hero_description'] ?? '') }}</textarea>
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

@section('scripts')
@parent
<script>
    (function () {
        const titleInput = document.getElementById('hero_title');
        const suffixInput = document.getElementById('hero_title_suffix');
        const preview = document.getElementById('venue-title-preview');

        function syncPreview() {
            if (!preview || !titleInput || !suffixInput) {
                return;
            }
            const title = String(titleInput.value || '').trim();
            const suffix = String(suffixInput.value || '').trim();
            preview.textContent = suffix ? `${title} / ${suffix}` : title;
        }

        if (titleInput && suffixInput && preview) {
            titleInput.addEventListener('input', syncPreview);
            suffixInput.addEventListener('input', syncPreview);
            syncPreview();
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
