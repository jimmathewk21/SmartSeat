@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.attraction.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.attractions.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-row mb-3">
                <div class="col-md-6">
                    <label class="required" for="name">{{ trans('cruds.attraction.fields.name') }}</label>
                    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name') }}" required>
                    @if($errors->has('name'))<div class="invalid-feedback">{{ $errors->first('name') }}</div>@endif
                </div>
                <div class="col-md-3">
                    <label for="category">Category</label>
                    <input class="form-control {{ $errors->has('category') ? 'is-invalid' : '' }}" type="text" name="category" id="category" value="{{ old('category') }}" placeholder="Food & Bar">
                    @if($errors->has('category'))<div class="invalid-feedback">{{ $errors->first('category') }}</div>@endif
                </div>
                <div class="col-md-3">
                    <label class="required" for="sort_order">{{ trans('cruds.attraction.fields.sort_order') }}</label>
                    <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" required>
                    @if($errors->has('sort_order'))<div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>@endif
                </div>
            </div>

            <div class="form-row mb-3">
                <div class="col-md-6">
                    <label for="image_url">{{ trans('cruds.attraction.fields.image_url') }}</label>
                    <input class="form-control {{ $errors->has('image_url') ? 'is-invalid' : '' }}" type="text" name="image_url" id="image_url" value="{{ old('image_url') }}" placeholder="https:// or uploads/attractions/file.webp">
                    @if($errors->has('image_url'))<div class="invalid-feedback">{{ $errors->first('image_url') }}</div>@endif
                    <small class="form-text text-muted">Use an image URL or upload a file below. Image size 1000x1000px recommended.</small>
                </div>
                <div class="col-md-6">
                    <label for="image_file">Upload Image</label>
                    <input class="form-control-file {{ $errors->has('image_file') ? 'is-invalid' : '' }}" type="file" name="image_file" id="image_file" accept=".jpg,.jpeg,.png,.webp,.svg">
                    @if($errors->has('image_file'))<div class="invalid-feedback d-block">{{ $errors->first('image_file') }}</div>@endif
                </div>
            </div>

            <div class="form-row mb-3">
                <div class="col-md-4">
                    <label for="website_url">Website Link</label>
                    <input class="form-control {{ $errors->has('website_url') ? 'is-invalid' : '' }}" type="url" name="website_url" id="website_url" value="{{ old('website_url') }}" placeholder="https://">
                    @if($errors->has('website_url'))<div class="invalid-feedback">{{ $errors->first('website_url') }}</div>@endif
                </div>
                <div class="col-md-4">
                    <label for="instagram_url">Instagram Link</label>
                    <input class="form-control {{ $errors->has('instagram_url') ? 'is-invalid' : '' }}" type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url') }}" placeholder="https://www.instagram.com/...">
                    @if($errors->has('instagram_url'))<div class="invalid-feedback">{{ $errors->first('instagram_url') }}</div>@endif
                </div>
                <div class="col-md-4">
                    <label for="direction_url">Google Map Direction Link</label>
                    <input class="form-control {{ $errors->has('direction_url') ? 'is-invalid' : '' }}" type="url" name="direction_url" id="direction_url" value="{{ old('direction_url') }}" placeholder="https://maps.google.com/...">
                    @if($errors->has('direction_url'))<div class="invalid-feedback">{{ $errors->first('direction_url') }}</div>@endif
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="detail">{{ trans('cruds.attraction.fields.detail') }}</label>
                <textarea class="form-control {{ $errors->has('detail') ? 'is-invalid' : '' }}" name="detail" id="detail" rows="5">{{ old('detail') }}</textarea>
                @if($errors->has('detail'))<div class="invalid-feedback">{{ $errors->first('detail') }}</div>@endif
            </div>

            <div class="form-row mb-3">
                <div class="col-md-4">
                    <label for="show_on_homepage">{{ trans('cruds.attraction.fields.show_on_homepage') }}</label>
                    <div class="form-check">
                        <input type="hidden" name="show_on_homepage" value="0">
                        <input class="form-check-input" type="checkbox" name="show_on_homepage" id="show_on_homepage" value="1" @checked(old('show_on_homepage'))>
                        <label class="form-check-label" for="show_on_homepage">{{ trans('cruds.attraction.fields.show_on_homepage') }}</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="required" for="is_active">{{ trans('cruds.attraction.fields.is_active') }}</label>
                    <select class="form-control" name="is_active" id="is_active">
                        <option value="1" @selected(old('is_active', '1') === '1')>{{ trans('global.yes') }} (Publish)</option>
                        <option value="0" @selected(old('is_active') === '0')>{{ trans('global.no') }} (Unpublish)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <button class="btn btn-success" type="submit">{{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
