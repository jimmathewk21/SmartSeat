<div class="row">
    <div class="col-md-4 form-group">
        <label for="section_title">Section Title</label>
        <input type="text" class="form-control" name="section_title" id="section_title" value="{{ old('section_title', $downloadMediaItem->section_title ?? '') }}" required>
    </div>
    <div class="col-md-4 form-group">
        <label for="audience_type">Sorting Group</label>
        <select class="form-control" name="audience_type" id="audience_type" required>
            <option value="general" @selected(old('audience_type', $downloadMediaItem->audience_type ?? 'delegate') === 'general')>General</option>
            <option value="speaker" @selected(old('audience_type', $downloadMediaItem->audience_type ?? 'delegate') === 'speaker')>Speaker</option>
            <option value="sponsor" @selected(old('audience_type', $downloadMediaItem->audience_type ?? 'delegate') === 'sponsor')>Sponsor</option>
            <option value="delegate" @selected(old('audience_type', $downloadMediaItem->audience_type ?? 'delegate') === 'delegate')>Delegate</option>
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label for="item_title">Item Title</label>
        <input type="text" class="form-control" name="item_title" id="item_title" value="{{ old('item_title', $downloadMediaItem->item_title ?? '') }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-8 form-group">
        <label for="item_subtitle">Item Subtitle</label>
        <input type="text" class="form-control" name="item_subtitle" id="item_subtitle" value="{{ old('item_subtitle', $downloadMediaItem->item_subtitle ?? '') }}">
    </div>
    <div class="col-md-2 form-group">
        <label for="sort_order">Sort Order</label>
        <input type="number" class="form-control" name="sort_order" id="sort_order" value="{{ old('sort_order', $downloadMediaItem->sort_order ?? 0) }}" min="0">
    </div>
    <div class="col-md-2 form-group">
        <label for="is_active">Active</label>
        <div class="form-check mt-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" {{ old('is_active', $downloadMediaItem->is_active ?? true) ? 'checked' : '' }}>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 form-group">
        <label for="image_url">Image URL/Path</label>
        <input type="text" class="form-control" name="image_url" id="image_url" value="{{ old('image_url', $downloadMediaItem->image_url ?? '') }}">
    </div>
    <div class="col-md-4 form-group">
        <label for="image_file">Upload Image</label>
        <input type="file" class="form-control-file" name="image_file" id="image_file" accept="image/*">
        <small class="form-text text-muted">Recommended image size: 1920x1080px for wide assets, 1080x1080px for square assets, or 1200x300px for logo strips.</small>
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label for="primary_button_text">Primary Button Text</label>
        <input type="text" class="form-control" name="primary_button_text" id="primary_button_text" value="{{ old('primary_button_text', $downloadMediaItem->primary_button_text ?? '') }}">
    </div>
    <div class="col-md-5 form-group">
        <label for="primary_file_url">Primary File URL/Path</label>
        <input type="text" class="form-control" name="primary_file_url" id="primary_file_url" value="{{ old('primary_file_url', $downloadMediaItem->primary_file_url ?? '') }}">
    </div>
    <div class="col-md-4 form-group">
        <label for="primary_file_upload">Upload Primary File</label>
        <input type="file" class="form-control-file" name="primary_file_upload" id="primary_file_upload">
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label for="secondary_button_text">Secondary Button Text</label>
        <input type="text" class="form-control" name="secondary_button_text" id="secondary_button_text" value="{{ old('secondary_button_text', $downloadMediaItem->secondary_button_text ?? '') }}">
    </div>
    <div class="col-md-5 form-group">
        <label for="secondary_file_url">Secondary File URL/Path</label>
        <input type="text" class="form-control" name="secondary_file_url" id="secondary_file_url" value="{{ old('secondary_file_url', $downloadMediaItem->secondary_file_url ?? '') }}">
    </div>
    <div class="col-md-4 form-group">
        <label for="secondary_file_upload">Upload Secondary File</label>
        <input type="file" class="form-control-file" name="secondary_file_upload" id="secondary_file_upload">
    </div>
</div>

<hr>
<div class="form-group">
    <input type="hidden" name="is_grouped_versions" value="0">
    <div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_grouped_versions" id="is_grouped_versions" value="1" {{ old('is_grouped_versions', $downloadMediaItem->is_grouped_versions ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_grouped_versions">Grouped Logo Versions</label>
    </div>
    <small class="form-text text-muted">Enable this when one item should control Version 1, Version 2, and Version 3 together, such as the white or dark logo strip sections.</small>
</div>

<div id="grouped-versions-fields" style="{{ old('is_grouped_versions', $downloadMediaItem->is_grouped_versions ?? false) ? '' : 'display:none;' }}">
    <h5>Grouped Logo Versions</h5>
    @foreach([1, 2, 3] as $version)
        <div class="card mb-3">
            <div class="card-header">Version {{ $version }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="version_{{ $version }}_label">Label</label>
                        <input type="text" class="form-control" name="version_{{ $version }}_label" id="version_{{ $version }}_label" value="{{ old('version_' . $version . '_label', $downloadMediaItem->{'version_' . $version . '_label'} ?? 'Version ' . $version) }}">
                    </div>
                    <div class="col-md-5 form-group">
                        <label for="version_{{ $version }}_image_url">Preview Image URL/Path</label>
                        <input type="text" class="form-control" name="version_{{ $version }}_image_url" id="version_{{ $version }}_image_url" value="{{ old('version_' . $version . '_image_url', $downloadMediaItem->{'version_' . $version . '_image_url'} ?? '') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="version_{{ $version }}_image_upload">Upload Preview Image</label>
                        <input type="file" class="form-control-file" name="version_{{ $version }}_image_upload" id="version_{{ $version }}_image_upload" accept="image/*">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="version_{{ $version }}_pdf_url">PDF URL/Path</label>
                        <input type="text" class="form-control" name="version_{{ $version }}_pdf_url" id="version_{{ $version }}_pdf_url" value="{{ old('version_' . $version . '_pdf_url', $downloadMediaItem->{'version_' . $version . '_pdf_url'} ?? '') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="version_{{ $version }}_pdf_upload">Upload PDF</label>
                        <input type="file" class="form-control-file" name="version_{{ $version }}_pdf_upload" id="version_{{ $version }}_pdf_upload" accept=".pdf">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="version_{{ $version }}_png_url">PNG URL/Path</label>
                        <input type="text" class="form-control" name="version_{{ $version }}_png_url" id="version_{{ $version }}_png_url" value="{{ old('version_' . $version . '_png_url', $downloadMediaItem->{'version_' . $version . '_png_url'} ?? '') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="version_{{ $version }}_png_upload">Upload PNG</label>
                        <input type="file" class="form-control-file" name="version_{{ $version }}_png_upload" id="version_{{ $version }}_png_upload" accept=".png">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10 form-group">
                        <label for="version_{{ $version }}_jpg_url">JPG URL/Path</label>
                        <input type="text" class="form-control" name="version_{{ $version }}_jpg_url" id="version_{{ $version }}_jpg_url" value="{{ old('version_' . $version . '_jpg_url', $downloadMediaItem->{'version_' . $version . '_jpg_url'} ?? '') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="version_{{ $version }}_jpg_upload">Upload JPG</label>
                        <input type="file" class="form-control-file" name="version_{{ $version }}_jpg_upload" id="version_{{ $version }}_jpg_upload" accept=".jpg,.jpeg">
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    (function () {
        var toggle = document.getElementById('is_grouped_versions');
        var fields = document.getElementById('grouped-versions-fields');

        if (!toggle || !fields) {
            return;
        }

        function syncGroupedFields() {
            fields.style.display = toggle.checked ? '' : 'none';
        }

        toggle.addEventListener('change', syncGroupedFields);
        syncGroupedFields();
    })();
</script>
