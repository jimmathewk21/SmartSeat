@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.sponsorCategory.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.sponsor-categories.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="name">{{ trans('cruds.sponsorCategory.fields.name') }}</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', '') }}" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
                <span class="help-block">{{ trans('cruds.sponsorCategory.fields.name_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="sort_order">{{ trans('cruds.sponsorCategory.fields.sort_order') }}</label>
                <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" required>
                @if($errors->has('sort_order'))
                    <div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>
                @endif
                <span class="help-block">{{ trans('cruds.sponsorCategory.fields.sort_order_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="logo_size">Logo Display Size</label>
                <select class="form-control {{ $errors->has('logo_size') ? 'is-invalid' : '' }}" name="logo_size" id="logo_size" required>
                    <option value="sm" @selected(old('logo_size') === 'sm')>Small</option>
                    <option value="md" @selected(old('logo_size', 'md') === 'md')>Medium</option>
                    <option value="lg" @selected(old('logo_size') === 'lg')>Large</option>
                </select>
                @if($errors->has('logo_size'))
                    <div class="invalid-feedback">{{ $errors->first('logo_size') }}</div>
                @endif
                <span class="help-block">This size affects logos only on the frontend sponsors page. Small, Medium, and Large control how big each sponsor logo is displayed inside the category cards.</span>
            </div>
            <div class="form-group">
                <label class="required" for="is_active">{{ trans('cruds.sponsorCategory.fields.is_active') }}</label>
                <select class="form-control {{ $errors->has('is_active') ? 'is-invalid' : '' }}" name="is_active" id="is_active" required>
                    <option value="1" @selected(old('is_active', '1') === '1')>{{ trans('global.yes') }}</option>
                    <option value="0" @selected(old('is_active') === '0')>{{ trans('global.no') }}</option>
                </select>
                @if($errors->has('is_active'))
                    <div class="invalid-feedback">{{ $errors->first('is_active') }}</div>
                @endif
                <span class="help-block">{{ trans('cruds.sponsorCategory.fields.is_active_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection
