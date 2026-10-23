<div class="form-group">
    <label class="required" for="path">Page Link</label>
    <input class="form-control {{ $errors->has('path') ? 'is-invalid' : '' }}" type="text" name="path" id="path" value="{{ old('path', $seoTitle->path ?? '') }}" placeholder="/speakers" required>
    <small class="form-text text-muted">Use a frontend path such as /, /speakers, /contact-us, or /pages/privacy-policy.</small>
    @if($errors->has('path'))
        <div class="invalid-feedback">{{ $errors->first('path') }}</div>
    @endif
</div>

<div class="form-group">
    <label class="required" for="title">Page Title</label>
    <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', $seoTitle->title ?? '') }}" maxlength="255" required>
    <small class="form-text text-muted">This value appears in the browser title and search engine title.</small>
    @if($errors->has('title'))
        <div class="invalid-feedback">{{ $errors->first('title') }}</div>
    @endif
</div>

<div class="form-group">
    <div class="form-check">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $seoTitle->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Active</label>
    </div>
</div>
