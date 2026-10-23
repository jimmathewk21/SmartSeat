@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.venue.reservation-settings.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            Reservation Page Settings
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="reservation_eyebrow_text">Reservation Eyebrow Text</label>
                    <input type="text" class="form-control {{ $errors->has('reservation_eyebrow_text') ? 'is-invalid' : '' }}" name="reservation_eyebrow_text" id="reservation_eyebrow_text" value="{{ old('reservation_eyebrow_text', $settings['reservation_eyebrow_text'] ?? '') }}">
                    @if($errors->has('reservation_eyebrow_text'))
                        <div class="invalid-feedback">{{ $errors->first('reservation_eyebrow_text') }}</div>
                    @endif
                </div>
                <div class="col-md-8 form-group">
                    <label for="reservation_heading">Reservation Heading</label>
                    <input type="text" class="form-control {{ $errors->has('reservation_heading') ? 'is-invalid' : '' }}" name="reservation_heading" id="reservation_heading" value="{{ old('reservation_heading', $settings['reservation_heading'] ?? '') }}" required>
                    @if($errors->has('reservation_heading'))
                        <div class="invalid-feedback">{{ $errors->first('reservation_heading') }}</div>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="reservation_heading_suffix">Reservation Heading Suffix</label>
                <input type="text" class="form-control {{ $errors->has('reservation_heading_suffix') ? 'is-invalid' : '' }}" name="reservation_heading_suffix" id="reservation_heading_suffix" value="{{ old('reservation_heading_suffix', $settings['reservation_heading_suffix'] ?? '') }}">
                @if($errors->has('reservation_heading_suffix'))
                    <div class="invalid-feedback">{{ $errors->first('reservation_heading_suffix') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label>Live Heading Preview</label>
                <div class="border rounded px-3 py-2 bg-light" id="reservation-title-preview"></div>
            </div>

            <div class="form-group">
                <label for="reservation_description">Reservation Description</label>
                <textarea class="form-control ckeditor {{ $errors->has('reservation_description') ? 'is-invalid' : '' }}" name="reservation_description" id="reservation_description" rows="4">{{ old('reservation_description', $settings['reservation_description'] ?? '') }}</textarea>
                @if($errors->has('reservation_description'))
                    <div class="invalid-feedback">{{ $errors->first('reservation_description') }}</div>
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
        const headingInput = document.getElementById('reservation_heading');
        const suffixInput = document.getElementById('reservation_heading_suffix');
        const preview = document.getElementById('reservation-title-preview');

        function syncPreview() {
            if (!preview || !headingInput || !suffixInput) {
                return;
            }
            const heading = String(headingInput.value || '').trim();
            const suffix = String(suffixInput.value || '').trim();
            preview.textContent = suffix ? `${heading} / ${suffix}` : heading;
        }

        if (headingInput && suffixInput && preview) {
            headingInput.addEventListener('input', syncPreview);
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
