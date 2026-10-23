@php
    $startDateValue = old('start_date', isset($worldEvent) && $worldEvent->start_date ? \Illuminate\Support\Carbon::parse($worldEvent->start_date)->format('Y-m-d') : '');
    $endDateValue = old('end_date', isset($worldEvent) && $worldEvent->end_date ? \Illuminate\Support\Carbon::parse($worldEvent->end_date)->format('Y-m-d') : '');
    $logoPath = old('logo_path', $worldEvent->logo_path ?? '');
    $logoPreview = '';
    if (trim((string) $logoPath) !== '') {
        $logoPreview = str_starts_with((string) $logoPath, 'http://')
            || str_starts_with((string) $logoPath, 'https://')
            || str_starts_with((string) $logoPath, '//')
            || str_starts_with((string) $logoPath, '/')
            ? (string) $logoPath
            : asset(ltrim((string) $logoPath, '/'));
    }
@endphp
@csrf
<div class="row">
    <div class="col-md-4 form-group">
        <label>Name of Event *</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $worldEvent->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2 form-group">
        <label>Start Date *</label>
        <input type="date" id="world-event-start-date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ $startDateValue }}" required>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2 form-group">
        <label>End Date *</label>
        <input type="date" id="world-event-end-date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ $endDateValue }}" required>
        <small class="form-text text-muted">End date must be on or after the start date.</small>
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2 form-group">
        <label>Stars (0-5)</label>
        <input type="number" min="0" max="5" name="stars" class="form-control" value="{{ old('stars', $worldEvent->stars ?? 0) }}">
    </div>
    <div class="col-md-2 form-group">
        <label>Days</label>
        <input type="text" name="days" class="form-control" value="{{ old('days', $worldEvent->days ?? '') }}" placeholder="Mon/Tue">
    </div>
</div>

<div class="row">
    <div class="col-md-4 form-group">
        <label>City</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $worldEvent->city ?? '') }}">
    </div>
    <div class="col-md-4 form-group">
        <label>Country</label>
        <input type="text" name="country" class="form-control" value="{{ old('country', $worldEvent->country ?? '') }}">
    </div>
    <div class="col-md-4 form-group">
        <label>Theme</label>
        <input type="text" name="theme" class="form-control" value="{{ old('theme', $worldEvent->theme ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-12 form-group">
        <label>Website</label>
        <input type="url" name="website" class="form-control" value="{{ old('website', $worldEvent->website ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-12 form-group">
        <label>Event Logo Link or Path</label>
        <input type="text" name="logo_path" class="form-control @error('logo_path') is-invalid @enderror" value="{{ $logoPath }}" placeholder="https://example.com/logo.webp or uploads/world-events/logos/logo.webp">
        @error('logo_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="form-text text-muted">You can paste a public logo URL or keep the uploaded file path. Uploading a file below will override this value.</small>
    </div>
    <div class="col-md-12 form-group">
        <label>Upload Event Logo</label>
        <input type="file" name="logo_file" class="form-control-file @error('logo_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
        @error('logo_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <small class="form-text text-muted">Upload a 280x150px image. The logo links to the event website on the frontend.</small>
        @if($logoPreview !== '')
            <div class="mt-2">
                <img src="{{ $logoPreview }}" alt="Event logo preview" style="width:140px; height:75px; object-fit:contain; border:1px solid #d9dfe7; border-radius:8px; background:#fff;">
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-12 form-group">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" @checked(old('is_active', $worldEvent->is_active ?? true))>
            <label class="form-check-label" for="is_active">Published</label>
        </div>
    </div>
</div>

<div class="form-group">
    <button class="btn btn-primary" type="submit">Save</button>
    <a class="btn btn-default" href="{{ route('admin.world-events.index') }}">Cancel</a>
</div>
