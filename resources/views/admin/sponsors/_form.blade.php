@php
    $sponsorRecord = $sponsor ?? null;
    $selectedCategoryIds = old('sponsor_category_ids', $sponsorRecord?->sponsorCategories?->pluck('id')->all() ?? []);
    $primaryCategoryId = old('sponsor_category_id', $sponsorRecord?->sponsor_category_id);
    $showWebsiteUrl = (string) old('show_website_url', ($sponsorRecord?->show_website_url ?? true) ? '1' : '0');
    $showLinkedinUrl = (string) old('show_linkedin_url', ($sponsorRecord?->show_linkedin_url ?? true) ? '1' : '0');
    $showBio = (string) old('show_bio', ($sponsorRecord?->show_bio ?? true) ? '1' : '0');
    $showAdvert = (string) old('show_advert', ($sponsorRecord?->show_advert ?? true) ? '1' : '0');
    $useSubCategoryText = (string) old('use_sub_category_text', ($sponsorRecord?->use_sub_category_text ?? false) ? '1' : '0');

    if (empty($selectedCategoryIds) && ! empty($primaryCategoryId)) {
        $selectedCategoryIds = [$primaryCategoryId];
    }

    $selectedCategoryIds = array_map('strval', (array) $selectedCategoryIds);
@endphp

<div class="mb-4">
    <h5 class="mb-3">Basic Information</h5>
    <div class="form-row">
        <div class="col-md-5 mb-3">
            <label class="required" for="name">Sponsor Full Name</label>
            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $sponsorRecord?->name ?? '') }}" required>
            @if($errors->has('name'))<div class="invalid-feedback">{{ $errors->first('name') }}</div>@endif
        </div>
        <div class="col-md-4 mb-3">
            <label for="sub_category">Short Name</label>
            <input class="form-control {{ $errors->has('sub_category') ? 'is-invalid' : '' }}" type="text" name="sub_category" id="sub_category" value="{{ old('sub_category', $sponsorRecord?->sub_category ?? '') }}">
            @if($errors->has('sub_category'))<div class="invalid-feedback">{{ $errors->first('sub_category') }}</div>@endif
            <small class="form-text text-muted">Optional short name. If filled, the sponsors page card will show this label instead of the sponsor name.</small>
        </div>
        <div class="col-md-3 mb-3">
            <label class="required" for="sort_order">Sort Order</label>
            <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $sponsorRecord?->sort_order ?? 0) }}" required>
            @if($errors->has('sort_order'))<div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>@endif
        </div>
    </div>

    <div class="form-row">
        <div class="col-md-5 mb-3">
            <div class="form-check mt-2">
                <input type="hidden" name="use_sub_category_text" value="0">
                <input class="form-check-input" type="checkbox" name="use_sub_category_text" id="use_sub_category_text" value="1" @checked($useSubCategoryText === '1')>
                <label class="form-check-label" for="use_sub_category_text">Sub category</label>
            </div>
            @if($errors->has('use_sub_category_text'))<div class="invalid-feedback d-block">{{ $errors->first('use_sub_category_text') }}</div>@endif
        </div>
        <div class="col-md-7 mb-3 {{ $useSubCategoryText === '1' ? '' : 'd-none' }}" id="sub_category_text_wrap">
            <label for="sub_category_text">Sub category text</label>
            <input class="form-control {{ $errors->has('sub_category_text') ? 'is-invalid' : '' }}" type="text" name="sub_category_text" id="sub_category_text" value="{{ old('sub_category_text', $sponsorRecord?->sub_category_text ?? '') }}">
            @if($errors->has('sub_category_text'))<div class="invalid-feedback">{{ $errors->first('sub_category_text') }}</div>@endif
            <small class="form-text text-muted">When enabled, this text displays on the sponsors page instead of Short Name.</small>
        </div>
    </div>

    <div class="form-row">
        <div class="col-md-6 mb-3">
            <label class="required" for="sponsor_category_id">Sponsor Category</label>
            <select class="form-control {{ $errors->has('sponsor_category_id') ? 'is-invalid' : '' }}" name="sponsor_category_id" id="sponsor_category_id" required>
                <option value="">{{ trans('global.pleaseSelect') }}</option>
                @foreach($sponsorCategories as $category)
                    <option value="{{ $category->id }}" @selected((string) $primaryCategoryId === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @if($errors->has('sponsor_category_id'))<div class="invalid-feedback">{{ $errors->first('sponsor_category_id') }}</div>@endif
            <small class="form-text text-muted">Sponsor card logo size is controlled by the selected sponsor category.</small>
        </div>
        <div class="col-md-6 mb-3">
            <label for="sponsor_category_ids">Additional / Multiple Categories</label>
            <select class="form-control {{ $errors->has('sponsor_category_ids') ? 'is-invalid' : '' }}" name="sponsor_category_ids[]" id="sponsor_category_ids" multiple size="4">
                @foreach($sponsorCategories as $category)
                    <option value="{{ $category->id }}" @selected(in_array((string) $category->id, $selectedCategoryIds, true))>{{ $category->name }}</option>
                @endforeach
            </select>
            @if($errors->has('sponsor_category_ids'))<div class="invalid-feedback d-block">{{ $errors->first('sponsor_category_ids') }}</div>@endif
            <small class="form-text text-muted">Select 1 or multiple categories.</small>
        </div>
    </div>

    <div class="form-row">
        <div class="col-md-6 mb-3">
            <label for="website_url">{{ trans('cruds.sponsor.fields.website_url') }}</label>
            <input class="form-control {{ $errors->has('website_url') ? 'is-invalid' : '' }}" type="url" name="website_url" id="website_url" value="{{ old('website_url', $sponsorRecord?->website_url ?? '') }}">
            @if($errors->has('website_url'))<div class="invalid-feedback">{{ $errors->first('website_url') }}</div>@endif
            <div class="mt-2">
                <label class="mb-0 mr-3">Display</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_website_url" id="show_website_url_yes" value="1" @checked($showWebsiteUrl === '1')>
                    <label class="form-check-label" for="show_website_url_yes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_website_url" id="show_website_url_no" value="0" @checked($showWebsiteUrl === '0')>
                    <label class="form-check-label" for="show_website_url_no">No</label>
                </div>
            </div>
            @if($errors->has('show_website_url'))<div class="invalid-feedback d-block">{{ $errors->first('show_website_url') }}</div>@endif
        </div>
        <div class="col-md-6 mb-3">
            <label for="linkedin_url">LinkedIn URL</label>
            <input class="form-control {{ $errors->has('linkedin_url') ? 'is-invalid' : '' }}" type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url', $sponsorRecord?->linkedin_url ?? '') }}">
            @if($errors->has('linkedin_url'))<div class="invalid-feedback">{{ $errors->first('linkedin_url') }}</div>@endif
            <div class="mt-2">
                <label class="mb-0 mr-3">Display</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_linkedin_url" id="show_linkedin_url_yes" value="1" @checked($showLinkedinUrl === '1')>
                    <label class="form-check-label" for="show_linkedin_url_yes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_linkedin_url" id="show_linkedin_url_no" value="0" @checked($showLinkedinUrl === '0')>
                    <label class="form-check-label" for="show_linkedin_url_no">No</label>
                </div>
            </div>
            @if($errors->has('show_linkedin_url'))<div class="invalid-feedback d-block">{{ $errors->first('show_linkedin_url') }}</div>@endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var checkbox = document.getElementById('use_sub_category_text');
        var wrapper = document.getElementById('sub_category_text_wrap');

        if (!checkbox || !wrapper) {
            return;
        }

        function syncSubCategoryText() {
            wrapper.classList.toggle('d-none', !checkbox.checked);
        }

        checkbox.addEventListener('change', syncSubCategoryText);
        syncSubCategoryText();
    });
</script>

<div class="mb-4">
    <h5 class="mb-3">Details & Files</h5>
    <input type="hidden" name="asset_type" value="{{ old('asset_type', $sponsorRecord?->asset_type ?? 'file') }}">

    <div class="form-row">
        <div class="col-md-8 mb-3">
            <label for="detail">Biography (Text)</label>
            <textarea class="form-control ckeditor {{ $errors->has('detail') ? 'is-invalid' : '' }}" name="detail" id="detail" rows="6">{{ old('detail', $sponsorRecord?->detail ?? '') }}</textarea>
            @if($errors->has('detail'))<div class="invalid-feedback d-block">{{ $errors->first('detail') }}</div>@endif
            <div class="mt-2">
                <label class="mb-0 mr-3">Display</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_bio" id="show_bio_yes" value="1" @checked($showBio === '1')>
                    <label class="form-check-label" for="show_bio_yes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_bio" id="show_bio_no" value="0" @checked($showBio === '0')>
                    <label class="form-check-label" for="show_bio_no">No</label>
                </div>
            </div>
            @if($errors->has('show_bio'))<div class="invalid-feedback d-block">{{ $errors->first('show_bio') }}</div>@endif
        </div>
        <div class="col-md-4 mb-3">
            <label for="resource_url">Biography (Link)</label>
            <input class="form-control {{ $errors->has('resource_url') ? 'is-invalid' : '' }}" type="url" name="resource_url" id="resource_url" value="{{ old('resource_url', $sponsorRecord?->resource_url ?? '') }}" placeholder="{{ trans('cruds.sponsor.fields.resource_url_helper') }}">
            @if($errors->has('resource_url'))<div class="invalid-feedback">{{ $errors->first('resource_url') }}</div>@endif
            <small class="form-text text-muted">Use this when the sponsor biography should open from an external link or saved file URL/path.</small>
        </div>
    </div>

    <div class="form-row">
        <div class="col-md-6 mb-3">
            <label for="logo_url">{{ trans('cruds.sponsor.fields.logo_url') }}</label>
            <input class="form-control {{ $errors->has('logo_url') ? 'is-invalid' : '' }}" type="text" name="logo_url" id="logo_url" value="{{ old('logo_url', $sponsorRecord?->logo_url ?? '') }}">
            @if($errors->has('logo_url'))<div class="invalid-feedback">{{ $errors->first('logo_url') }}</div>@endif
            <small class="form-text text-muted">Recommended source image size: 280x150. Frontend display size comes from the sponsor category setting: Small, Medium, or Large.</small>
        </div>
        <div class="col-md-6 mb-3">
            <label for="logo_file">Choose Logo File</label>
            <input class="form-control-file {{ $errors->has('logo_file') ? 'is-invalid' : '' }}" type="file" name="logo_file" id="logo_file" accept=".jpg,.jpeg,.png,.webp,.svg">
            @if($errors->has('logo_file'))<div class="invalid-feedback d-block">{{ $errors->first('logo_file') }}</div>@endif
            <small class="form-text text-muted">If selected, uploaded image will replace the logo URL for frontend display.</small>
        </div>
    </div>

    <div class="form-row">
        <div class="col-md-6 mb-3">
            <label for="brochure_url">Brochure URL / Path</label>
            <input class="form-control {{ $errors->has('brochure_url') ? 'is-invalid' : '' }}" type="text" name="brochure_url" id="brochure_url" value="{{ old('brochure_url', $sponsorRecord?->brochure_url ?? '') }}">
            @if($errors->has('brochure_url'))<div class="invalid-feedback">{{ $errors->first('brochure_url') }}</div>@endif
        </div>
        <div class="col-md-6 mb-3">
            <label for="brochure_file">Choose Brochure File</label>
            <input class="form-control-file {{ $errors->has('brochure_file') ? 'is-invalid' : '' }}" type="file" name="brochure_file" id="brochure_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.jpg,.jpeg,.png,.webp">
            @if($errors->has('brochure_file'))<div class="invalid-feedback d-block">{{ $errors->first('brochure_file') }}</div>@endif
        </div>
    </div>

    <div class="form-row">
        <div class="col-md-6 mb-3">
            <label for="advert_url">Advert URL / Path</label>
            <input class="form-control {{ $errors->has('advert_url') ? 'is-invalid' : '' }}" type="text" name="advert_url" id="advert_url" value="{{ old('advert_url', $sponsorRecord?->advert_url ?? '') }}">
            @if($errors->has('advert_url'))<div class="invalid-feedback">{{ $errors->first('advert_url') }}</div>@endif
            <div class="mt-2">
                <label class="mb-0 mr-3">Display</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_advert" id="show_advert_yes" value="1" @checked($showAdvert === '1')>
                    <label class="form-check-label" for="show_advert_yes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="show_advert" id="show_advert_no" value="0" @checked($showAdvert === '0')>
                    <label class="form-check-label" for="show_advert_no">No</label>
                </div>
            </div>
            @if($errors->has('show_advert'))<div class="invalid-feedback d-block">{{ $errors->first('show_advert') }}</div>@endif
        </div>
        <div class="col-md-6 mb-3">
            <label for="advert_file">Choose Advert File</label>
            <input class="form-control-file {{ $errors->has('advert_file') ? 'is-invalid' : '' }}" type="file" name="advert_file" id="advert_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.jpg,.jpeg,.png,.webp">
            @if($errors->has('advert_file'))<div class="invalid-feedback d-block">{{ $errors->first('advert_file') }}</div>@endif
        </div>
    </div>

    <div class="form-row">
        <div class="col-md-6 mb-3">
            <label for="sponsor_video_url">Sponsor Video URL (YouTube/Vimeo)</label>
            <input class="form-control {{ $errors->has('sponsor_video_url') ? 'is-invalid' : '' }}" type="url" name="sponsor_video_url" id="sponsor_video_url" value="{{ old('sponsor_video_url', $sponsorRecord?->sponsor_video_url ?? '') }}">
            @if($errors->has('sponsor_video_url'))<div class="invalid-feedback">{{ $errors->first('sponsor_video_url') }}</div>@endif
        </div>
    </div>
</div>

<div class="mb-4">
    <h5 class="mb-3">Contact Person</h5>
    <div class="form-row">
        <div class="col-md-6 mb-3">
            <label for="contact_name">Contact Person Name</label>
            <input class="form-control {{ $errors->has('contact_name') ? 'is-invalid' : '' }}" type="text" name="contact_name" id="contact_name" value="{{ old('contact_name', $sponsorRecord?->contact_name ?? '') }}">
            @if($errors->has('contact_name'))<div class="invalid-feedback">{{ $errors->first('contact_name') }}</div>@endif
        </div>
        <div class="col-md-6 mb-3">
            <label for="contact_designation">Contact Person Designation</label>
            <input class="form-control {{ $errors->has('contact_designation') ? 'is-invalid' : '' }}" type="text" name="contact_designation" id="contact_designation" value="{{ old('contact_designation', $sponsorRecord?->contact_designation ?? '') }}">
            @if($errors->has('contact_designation'))<div class="invalid-feedback">{{ $errors->first('contact_designation') }}</div>@endif
        </div>
    </div>
    <div class="form-row">
        <div class="col-md-6 mb-3">
            <label for="contact_mobile">Contact Person Mobile</label>
            <input class="form-control {{ $errors->has('contact_mobile') ? 'is-invalid' : '' }}" type="text" name="contact_mobile" id="contact_mobile" value="{{ old('contact_mobile', $sponsorRecord?->contact_mobile ?? '') }}">
            @if($errors->has('contact_mobile'))<div class="invalid-feedback">{{ $errors->first('contact_mobile') }}</div>@endif
        </div>
        <div class="col-md-6 mb-3">
            <label for="contact_email">Contact Person E-mail ID</label>
            <input class="form-control {{ $errors->has('contact_email') ? 'is-invalid' : '' }}" type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $sponsorRecord?->contact_email ?? '') }}">
            @if($errors->has('contact_email'))<div class="invalid-feedback">{{ $errors->first('contact_email') }}</div>@endif
        </div>
    </div>
</div>

<div class="mb-4">
    <h5 class="mb-3">Visibility & Status</h5>
    <div class="form-row align-items-end">
        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input type="hidden" name="show_on_homepage" value="0">
                <input class="form-check-input" type="checkbox" name="show_on_homepage" id="show_on_homepage" value="1" @checked(old('show_on_homepage', $sponsorRecord?->show_on_homepage ?? false))>
                <label class="form-check-label" for="show_on_homepage">{{ trans('cruds.sponsor.fields.show_on_homepage') }}</label>
            </div>
            @if($errors->has('show_on_homepage'))<div class="invalid-feedback d-block">{{ $errors->first('show_on_homepage') }}</div>@endif

            <div class="form-check mt-2">
                <input type="hidden" name="show_on_scrollbar" value="0">
                <input class="form-check-input" type="checkbox" name="show_on_scrollbar" id="show_on_scrollbar" value="1" @checked(old('show_on_scrollbar', $sponsorRecord?->show_on_scrollbar ?? false))>
                <label class="form-check-label" for="show_on_scrollbar">Show On Scroll Bar (Speaker Detail Page, Things to do)</label>
            </div>
            @if($errors->has('show_on_scrollbar'))<div class="invalid-feedback d-block">{{ $errors->first('show_on_scrollbar') }}</div>@endif

            <div class="form-check mt-2">
                <input type="hidden" name="show_contact_details" value="0">
                <input class="form-check-input" type="checkbox" name="show_contact_details" id="show_contact_details" value="1" @checked(old('show_contact_details', $sponsorRecord?->show_contact_details ?? true))>
                <label class="form-check-label" for="show_contact_details">{{ trans('cruds.sponsor.fields.show_contact_details') }}</label>
            </div>
            @if($errors->has('show_contact_details'))<div class="invalid-feedback d-block">{{ $errors->first('show_contact_details') }}</div>@endif
        </div>

        <div class="col-md-4 mb-3">
            <label class="required" for="is_active">{{ trans('cruds.sponsor.fields.is_active') }}</label>
            <select class="form-control {{ $errors->has('is_active') ? 'is-invalid' : '' }}" name="is_active" id="is_active" required>
                <option value="1" @selected((string) old('is_active', ($sponsorRecord?->is_active ?? true) ? '1' : '0') === '1')>{{ trans('global.yes') }} (Publish)</option>
                <option value="0" @selected((string) old('is_active', ($sponsorRecord?->is_active ?? true) ? '1' : '0') === '0')>{{ trans('global.no') }} (Unpublish)</option>
            </select>
            @if($errors->has('is_active'))<div class="invalid-feedback">{{ $errors->first('is_active') }}</div>@endif
        </div>
    </div>
</div>
