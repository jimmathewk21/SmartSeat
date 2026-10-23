@php
    $bookingFromValue = old('booking_available_from', isset($venueHotel) && $venueHotel->booking_available_from ? \Illuminate\Support\Carbon::parse($venueHotel->booking_available_from)->format('Y-m-d') : '');
    $bookingToValue = old('booking_available_to', isset($venueHotel) && $venueHotel->booking_available_to ? \Illuminate\Support\Carbon::parse($venueHotel->booking_available_to)->format('Y-m-d') : '');
    $coverImageValue = old('hotel_image', $venueHotel->hotel_image ?? '');
    $coverImagePreview = '';
    if (trim((string) $coverImageValue) !== '') {
        $coverImagePreview = str_starts_with((string) $coverImageValue, 'http://')
            || str_starts_with((string) $coverImageValue, 'https://')
            || str_starts_with((string) $coverImageValue, '//')
            || str_starts_with((string) $coverImageValue, '/')
            ? (string) $coverImageValue
            : asset(ltrim((string) $coverImageValue, '/'));
    }
    $galleryValue = old('hotel_gallery_text');
    if ($galleryValue === null) {
        $galleryValue = isset($venueHotel) && is_array($venueHotel->hotel_gallery)
            ? implode("\n", array_filter(array_map(static fn ($v) => trim((string) $v), $venueHotel->hotel_gallery)))
            : '';
    }
    $roomRows = old('rooms');
    if ($roomRows === null) {
        $storedRooms = isset($venueHotel) && is_array($venueHotel->hotel_rooms) ? $venueHotel->hotel_rooms : [];
        $roomRows = ! empty($storedRooms) ? collect($storedRooms)->map(function ($room) {
            return [
                'name' => (string) ($room['name'] ?? ''),
                'size' => (string) ($room['size'] ?? ''),
                'existing_image' => (string) ($room['image'] ?? ''),
                'benefits_text' => implode("\n", array_filter(array_map('trim', (array) ($room['benefits'] ?? [])))),
                'single_rate_inr' => (string) ($room['single_rate_inr'] ?? $room['rate_inr'] ?? ''),
                'double_rate_inr' => (string) ($room['double_rate_inr'] ?? $room['rate_inr'] ?? ''),
                'single_rate_usd' => (string) ($room['single_rate_usd'] ?? $room['rate_usd'] ?? ''),
                'double_rate_usd' => (string) ($room['double_rate_usd'] ?? $room['rate_usd'] ?? ''),
                'badge' => (string) ($room['badge'] ?? ''),
                'caption' => (string) ($room['caption'] ?? ''),
            ];
        })->values()->all() : [[
            'name' => 'Deluxe Room',
            'caption' => 'Select your room type based on occupancy preference.',
            'size' => '41 sqm / 440 sqf',
            'existing_image' => '',
            'benefits_text' => "Complimentary breakfast\nComplimentary Wi-Fi",
            'single_rate_inr' => '13000',
            'double_rate_inr' => '14000',
            'single_rate_usd' => '178',
            'double_rate_usd' => '187',
            'badge' => '',
        ]];
    }
@endphp
@csrf
<div class="row">
    <div class="col-md-4 form-group">
        <label>Hotel Name *</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $venueHotel->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2 form-group">
        <label>Stars (0-5)</label>
        <input type="number" min="0" max="5" name="stars" class="form-control" value="{{ old('stars', $venueHotel->stars ?? 0) }}">
    </div>
    <div class="col-md-2 form-group">
        <label>Sort Order</label>
        <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $venueHotel->sort_order ?? 0) }}">
    </div>
    <div class="col-md-2 form-group">
        <label>Status</label>
        <div class="form-check mt-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $venueHotel->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label>City</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $venueHotel->city ?? '') }}">
    </div>
    <div class="col-md-3 form-group">
        <label>Country</label>
        <input type="text" name="country" class="form-control" value="{{ old('country', $venueHotel->country ?? '') }}">
    </div>
    <div class="col-md-6 form-group">
        <label>Hotel Cover Image URL/Path</label>
        <input type="text" name="hotel_image" id="hotel-image-path" class="form-control" value="{{ $coverImageValue }}" placeholder="uploads/venue-hotels/covers/hotel-1.jpg">
        <small class="form-text text-muted">Recommended: 1600x900px (16:9).</small>
    </div>
</div>

<div class="row">
    <div class="col-md-12 form-group">
        <label>Upload Hotel Cover Image</label>
        <input type="file" name="hotel_image_file" class="form-control-file @error('hotel_image_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.svg">
        @error('hotel_image_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <small class="form-text text-muted">You can upload a local image from your computer. Max file size: 4 MB (4096 KB). Recommended image size: 1600x900px.</small>
        @if(! empty($venueHotel->hotel_image ?? ''))
            <small class="form-text text-muted">If you upload a file here, it will replace the cover image path above.</small>
        @endif
        <div id="hotel-cover-preview-wrapper" class="{{ $coverImagePreview !== '' ? '' : 'd-none' }}" style="margin-top:10px;">
            <img id="hotel-cover-preview" src="{{ $coverImagePreview }}" alt="Cover image preview" style="width:220px; height:124px; object-fit:cover; border-radius:10px; border:1px solid #d9dfe7;">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 form-group">
        <label>Hotel Address</label>
        <textarea name="hotel_address" class="form-control" rows="2">{{ old('hotel_address', $venueHotel->hotel_address ?? '') }}</textarea>
    </div>
    <div class="col-md-12 form-group">
        <label>Hotel Description</label>
        <textarea name="hotel_description" class="form-control ckeditor" rows="6">{{ old('hotel_description', $venueHotel->hotel_description ?? '') }}</textarea>
        <small class="form-text text-muted">This description appears in the main content block on the venue detail page.</small>
    </div>
    <div class="col-md-6 form-group">
        <label>Room Section Heading</label>
        <input type="text" name="room_section_heading" class="form-control" value="{{ old('room_section_heading', $venueHotel->room_section_heading ?? 'Choose Your Room') }}">
        <small class="form-text text-muted">Example: Choose Your Room</small>
    </div>
    <div class="col-md-12 form-group">
        <label>Room Section Text</label>
        <textarea name="room_section_text" class="form-control ckeditor" rows="4">{{ old('room_section_text', $venueHotel->room_section_text ?? 'Select your room type and review category-wise pricing. Please note that these special rates are available for a limited number of rooms only and will be offered on a first-come, first-served basis.') }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-12 form-group">
        <label>Gallery Image URLs/Paths (one per line)</label>
        <textarea name="hotel_gallery_text" class="form-control @error('hotel_gallery_text') is-invalid @enderror" rows="5">{{ $galleryValue }}</textarea>
        @error('hotel_gallery_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="form-text text-muted">You can paste image paths here or upload gallery images below. Max file size per image: 4 MB (4096 KB). Recommended image size: 1600x900px.</small>
    </div>
    <div class="col-md-12 form-group">
        <label>Upload Gallery Images</label>
        <input type="file" name="hotel_gallery_files[]" class="form-control-file @error('hotel_gallery_files') is-invalid @enderror @error('hotel_gallery_files.*') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.svg" multiple>
        @error('hotel_gallery_files')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('hotel_gallery_files.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <small class="form-text text-muted">Upload from your computer. Max file size per image: 4 MB (4096 KB). Recommended image size: 1600x900px.</small>
    </div>
    @if(isset($venueHotel) && is_array($venueHotel->hotel_gallery) && count($venueHotel->hotel_gallery))
        <div class="col-md-12 form-group mb-0">
            <label>Current Gallery Images</label>
            <div class="d-flex flex-wrap" style="gap:12px;">
                @foreach($venueHotel->hotel_gallery as $galleryImage)
                    <div style="width:120px;">
                        <img src="{{ str_starts_with($galleryImage, 'http://') || str_starts_with($galleryImage, 'https://') || str_starts_with($galleryImage, '//') || str_starts_with($galleryImage, '/') ? $galleryImage : asset(ltrim($galleryImage, '/')) }}" alt="Hotel gallery" style="width:120px; height:80px; object-fit:cover; border-radius:8px; border:1px solid #d9dfe7;">
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<div class="row mt-3">
    <div class="col-md-3 form-group">
        <label>Booking Available From</label>
        <input type="date" name="booking_available_from" class="form-control" value="{{ $bookingFromValue }}">
    </div>
    <div class="col-md-3 form-group">
        <label>Booking Available To</label>
        <input type="date" name="booking_available_to" class="form-control" value="{{ $bookingToValue }}">
    </div>
    <div class="col-md-3 form-group">
        <label>Direction From</label>
        <input type="text" name="direction_from" class="form-control" value="{{ old('direction_from', $venueHotel->direction_from ?? '') }}" placeholder="Airport / City">
    </div>
    <div class="col-md-3 form-group">
        <label>Direction To</label>
        <input type="text" name="direction_to" class="form-control" value="{{ old('direction_to', $venueHotel->direction_to ?? '') }}" placeholder="Hotel Address">
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Rooms</span>
        <button type="button" class="btn btn-sm btn-secondary" id="add-room-row">Add Room</button>
    </div>
    <div class="card-body">
        <div id="room-rows">
            @foreach($roomRows as $index => $room)
                <div class="border rounded p-3 mb-3 room-row">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong>Room {{ $index + 1 }}</strong>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-room-row">Remove</button>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Room Category Name</label>
                            <input type="text" name="rooms[{{ $index }}][name]" class="form-control" value="{{ $room['name'] ?? '' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Category Caption</label>
                            <input type="text" name="rooms[{{ $index }}][caption]" class="form-control" value="{{ $room['caption'] ?? '' }}" placeholder="Select your room type">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Room Size</label>
                            <input type="text" name="rooms[{{ $index }}][size]" class="form-control" value="{{ $room['size'] ?? '' }}" placeholder="38 sqm / 409 sqf">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Badge</label>
                            <input type="text" name="rooms[{{ $index }}][badge]" class="form-control" value="{{ $room['badge'] ?? '' }}" placeholder="Popular">
                        </div>
                    </div>
                    <div class="row align-items-end">
                        <div class="col-md-6 form-group">
                            <label>Upload Room Image</label>
                            <input type="hidden" name="rooms[{{ $index }}][existing_image]" value="{{ $room['existing_image'] ?? '' }}">
                            <input type="file" name="rooms[{{ $index }}][image_file]" class="form-control-file @error('rooms.' . $index . '.image_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.svg">
                            @error('rooms.' . $index . '.image_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">Upload from your computer only. Max file size: 4 MB (4096 KB). Recommended image size: 1200x800px.</small>
                            @if(! empty($room['existing_image']))
                                <div class="mt-2">
                                    <img src="{{ str_starts_with($room['existing_image'], 'http://') || str_starts_with($room['existing_image'], 'https://') || str_starts_with($room['existing_image'], '//') || str_starts_with($room['existing_image'], '/') ? $room['existing_image'] : asset(ltrim($room['existing_image'], '/')) }}" alt="Room image" style="width:120px; height:80px; object-fit:cover; border-radius:8px; border:1px solid #d9dfe7;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Single Rate (INR / Night)</label>
                            <input type="number" step="0.01" min="0" name="rooms[{{ $index }}][single_rate_inr]" class="form-control" value="{{ $room['single_rate_inr'] ?? '' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Double Rate (INR / Night)</label>
                            <input type="number" step="0.01" min="0" name="rooms[{{ $index }}][double_rate_inr]" class="form-control" value="{{ $room['double_rate_inr'] ?? '' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Single Rate (USD / Night)</label>
                            <input type="number" step="0.01" min="0" name="rooms[{{ $index }}][single_rate_usd]" class="form-control" value="{{ $room['single_rate_usd'] ?? '' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Double Rate (USD / Night)</label>
                            <input type="number" step="0.01" min="0" name="rooms[{{ $index }}][double_rate_usd]" class="form-control" value="{{ $room['double_rate_usd'] ?? '' }}">
                        </div>
                        <div class="col-md-12">
                            <small class="form-text text-muted mb-2">Tax note text is managed from Venue &gt; Tax Option.</small>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Room Benefits (one per line)</label>
                        <textarea name="rooms[{{ $index }}][benefits_text]" class="form-control" rows="4">{{ $room['benefits_text'] ?? '' }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="form-group">
    <button class="btn btn-primary" type="submit">Save</button>
    <a class="btn btn-default" href="{{ route('admin.venue-hotels.index') }}">Cancel</a>
</div>

<template id="room-row-template">
    <div class="border rounded p-3 mb-3 room-row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong>Room __LABEL__</strong>
            <button type="button" class="btn btn-sm btn-outline-danger remove-room-row">Remove</button>
        </div>
        <div class="row">
            <div class="col-md-3 form-group">
                <label>Room Category Name</label>
                <input type="text" name="rooms[__INDEX__][name]" class="form-control">
            </div>
            <div class="col-md-3 form-group">
                <label>Category Caption</label>
                <input type="text" name="rooms[__INDEX__][caption]" class="form-control" placeholder="Select your room type">
            </div>
            <div class="col-md-3 form-group">
                <label>Room Size</label>
                <input type="text" name="rooms[__INDEX__][size]" class="form-control" placeholder="38 sqm / 409 sqf">
            </div>
            <div class="col-md-3 form-group">
                <label>Badge</label>
                <input type="text" name="rooms[__INDEX__][badge]" class="form-control" placeholder="Popular">
            </div>
        </div>
        <div class="row align-items-end">
            <div class="col-md-6 form-group">
                <label>Upload Room Image</label>
                <input type="hidden" name="rooms[__INDEX__][existing_image]" value="">
                <input type="file" name="rooms[__INDEX__][image_file]" class="form-control-file" accept=".jpg,.jpeg,.png,.webp,.svg">
                <small class="form-text text-muted">Upload from your computer only. Max file size: 4 MB (4096 KB). Recommended image size: 1200x800px.</small>
            </div>
            <div class="col-md-3 form-group">
                <label>Single Rate (INR / Night)</label>
                <input type="number" step="0.01" min="0" name="rooms[__INDEX__][single_rate_inr]" class="form-control">
            </div>
            <div class="col-md-3 form-group">
                <label>Double Rate (INR / Night)</label>
                <input type="number" step="0.01" min="0" name="rooms[__INDEX__][double_rate_inr]" class="form-control">
            </div>
            <div class="col-md-3 form-group">
                <label>Single Rate (USD / Night)</label>
                <input type="number" step="0.01" min="0" name="rooms[__INDEX__][single_rate_usd]" class="form-control">
            </div>
            <div class="col-md-3 form-group">
                <label>Double Rate (USD / Night)</label>
                <input type="number" step="0.01" min="0" name="rooms[__INDEX__][double_rate_usd]" class="form-control">
            </div>
            <div class="col-md-12">
                <small class="form-text text-muted mb-2">18% GST will be applicable extra on the above rates.</small>
            </div>
        </div>
        <div class="form-group mb-0">
            <label>Room Benefits (one per line)</label>
            <textarea name="rooms[__INDEX__][benefits_text]" class="form-control" rows="4"></textarea>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
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

    const coverInput = document.getElementById('hotel-image-path');
    const coverPreviewWrapper = document.getElementById('hotel-cover-preview-wrapper');
    const coverPreviewImage = document.getElementById('hotel-cover-preview');
    const assetBase = @json(rtrim(asset('/'), '/') . '/');

    function resolveAssetPath(value) {
        const trimmed = String(value || '').trim();
        if (!trimmed) {
            return '';
        }
        if (
            trimmed.startsWith('http://')
            || trimmed.startsWith('https://')
            || trimmed.startsWith('//')
            || trimmed.startsWith('/')
        ) {
            return trimmed;
        }
        return assetBase + trimmed.replace(/^\/+/, '');
    }

    function syncCoverPreview() {
        if (!coverInput || !coverPreviewWrapper || !coverPreviewImage) {
            return;
        }
        const source = resolveAssetPath(coverInput.value);
        if (!source) {
            coverPreviewImage.setAttribute('src', '');
            coverPreviewWrapper.classList.add('d-none');
            return;
        }
        coverPreviewImage.setAttribute('src', source);
        coverPreviewWrapper.classList.remove('d-none');
    }

    if (coverInput) {
        coverInput.addEventListener('input', syncCoverPreview);
        syncCoverPreview();
    }

    const wrapper = document.getElementById('room-rows');
    const addBtn = document.getElementById('add-room-row');
    const template = document.getElementById('room-row-template');
    if (!wrapper || !addBtn || !template) {
        return;
    }

    let roomIndex = wrapper.querySelectorAll('.room-row').length;

    function relabelRows() {
        wrapper.querySelectorAll('.room-row').forEach(function (row, index) {
            const label = row.querySelector('strong');
            if (label) {
                label.textContent = 'Room ' + String(index + 1);
            }
        });
    }

    function bindRemove(button) {
        button.addEventListener('click', function () {
            const rows = wrapper.querySelectorAll('.room-row');
            if (rows.length <= 1) {
                const textInputs = rows[0].querySelectorAll('input[type="text"], input[type="number"], textarea');
                textInputs.forEach(function (input) { input.value = ''; });
                const hiddenImage = rows[0].querySelector('input[type="hidden"]');
                if (hiddenImage) {
                    hiddenImage.value = '';
                }
                const fileInput = rows[0].querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.value = '';
                }
                const preview = rows[0].querySelector('img');
                if (preview) {
                    preview.remove();
                }
                return;
            }

            button.closest('.room-row')?.remove();
            relabelRows();
        });
    }

    wrapper.querySelectorAll('.remove-room-row').forEach(bindRemove);
    relabelRows();

    addBtn.addEventListener('click', function () {
        const html = template.innerHTML
            .replaceAll('__INDEX__', String(roomIndex))
            .replaceAll('__LABEL__', String(roomIndex + 1));
        wrapper.insertAdjacentHTML('beforeend', html);
        const row = wrapper.lastElementChild;
        const removeBtn = row?.querySelector('.remove-room-row');
        if (removeBtn) {
            bindRemove(removeBtn);
        }
        relabelRows();
        roomIndex++;
    });
});
</script>
