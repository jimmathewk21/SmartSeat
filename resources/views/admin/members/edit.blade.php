@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.member.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.members.update', [$member->id]) }}">
            @csrf
            @method('PUT')
            <div class="form-row mb-3">
                <div class="col-md-6">
                    <label class="required" for="name">{{ trans('cruds.member.fields.name') }}</label>
                    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $member->name) }}" required>
                    @if($errors->has('name'))<div class="invalid-feedback">{{ $errors->first('name') }}</div>@endif
                </div>
                <div class="col-md-6">
                    <label for="photo_url">{{ trans('cruds.member.fields.photo_url') }}</label>
                    <input class="form-control {{ $errors->has('photo_url') ? 'is-invalid' : '' }}" type="url" name="photo_url" id="photo_url" value="{{ old('photo_url', $member->photo_url) }}" placeholder="https://">
                    @if($errors->has('photo_url'))<div class="invalid-feedback">{{ $errors->first('photo_url') }}</div>@endif
                    <small class="form-text text-muted">Image size 256×256 recommended.</small>
                </div>
            </div>
            <div class="form-row mb-3">
                <div class="col-md-6">
                    <label for="designation">{{ trans('cruds.member.fields.designation') }}</label>
                    <input class="form-control {{ $errors->has('designation') ? 'is-invalid' : '' }}" type="text" name="designation" id="designation" value="{{ old('designation', $member->designation) }}">
                    @if($errors->has('designation'))<div class="invalid-feedback">{{ $errors->first('designation') }}</div>@endif
                </div>
                <div class="col-md-6">
                    <label for="company">{{ trans('cruds.member.fields.company') }}</label>
                    <input class="form-control {{ $errors->has('company') ? 'is-invalid' : '' }}" type="text" name="company" id="company" value="{{ old('company', $member->company) }}">
                    @if($errors->has('company'))<div class="invalid-feedback">{{ $errors->first('company') }}</div>@endif
                </div>
            </div>
            <div class="form-row mb-4">
                <div class="col-md-3">
                    <label class="required" for="sort_order">{{ trans('cruds.member.fields.sort_order') }}</label>
                    <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $member->sort_order) }}" required>
                    @if($errors->has('sort_order'))<div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>@endif
                </div>
                <div class="col-md-3">
                    <label class="required" for="is_active">{{ trans('cruds.member.fields.is_active') }}</label>
                    <select class="form-control" name="is_active" id="is_active">
                        <option value="1" @selected((string)old('is_active', $member->is_active ? '1' : '0') === '1')>{{ trans('global.yes') }}</option>
                        <option value="0" @selected((string)old('is_active', $member->is_active ? '1' : '0') === '0')>{{ trans('global.no') }}</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-success" type="submit">{{ trans('global.update') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
