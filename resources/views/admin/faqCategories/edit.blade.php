@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.faqCategory.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.faq-categories.update', [$faq_category->id]) }}">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="name">{{ trans('cruds.faqCategory.fields.name') }}</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $faq_category->name) }}" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
            </div>
            <div class="form-group">
                <label class="required" for="sort_order">{{ trans('cruds.faqCategory.fields.sort_order') }}</label>
                <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $faq_category->sort_order) }}" required>
                @if($errors->has('sort_order'))
                    <div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>
                @endif
            </div>
            <div class="form-group">
                <label class="required" for="is_active">{{ trans('cruds.faqCategory.fields.is_active') }}</label>
                <select class="form-control {{ $errors->has('is_active') ? 'is-invalid' : '' }}" name="is_active" id="is_active" required>
                    <option value="1" @selected((string) old('is_active', $faq_category->is_active ? '1' : '0') === '1')>{{ trans('global.yes') }}</option>
                    <option value="0" @selected((string) old('is_active', $faq_category->is_active ? '1' : '0') === '0')>{{ trans('global.no') }}</option>
                </select>
                @if($errors->has('is_active'))
                    <div class="invalid-feedback">{{ $errors->first('is_active') }}</div>
                @endif
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

