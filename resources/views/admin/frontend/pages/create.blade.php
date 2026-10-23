@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Create Page</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.frontend.pages.store') }}">
            @csrf

            <div class="form-group">
                <label for="subtitle">Subtitle</label>
                <input class="form-control {{ $errors->has('subtitle') ? 'is-invalid' : '' }}" type="text" name="subtitle" id="subtitle" value="{{ old('subtitle') }}">
                @if($errors->has('subtitle'))<div class="invalid-feedback">{{ $errors->first('subtitle') }}</div>@endif
            </div>

            <div class="form-group">
                <label class="required" for="title">Title</label>
                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title') }}" required>
                @if($errors->has('title'))<div class="invalid-feedback">{{ $errors->first('title') }}</div>@endif
            </div>

            <div class="form-group">
                <label for="title_suffix">Title Suffix</label>
                <input class="form-control {{ $errors->has('title_suffix') ? 'is-invalid' : '' }}" type="text" name="title_suffix" id="title_suffix" value="{{ old('title_suffix') }}">
                @if($errors->has('title_suffix'))<div class="invalid-feedback">{{ $errors->first('title_suffix') }}</div>@endif
            </div>

            <div class="form-group">
                <label class="required" for="slug">Slug</label>
                <input class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}" type="text" name="slug" id="slug" value="{{ old('slug') }}" required>
                <small class="form-text text-muted">Example: <code>about-us</code></small>
                @if($errors->has('slug'))<div class="invalid-feedback">{{ $errors->first('slug') }}</div>@endif
            </div>

            <div class="form-group">
                <label for="content">Page Content (Text or HTML)</label>
                <textarea class="form-control {{ $errors->has('content') ? 'is-invalid' : '' }}" name="content" id="content" rows="12">{{ old('content') }}</textarea>
                <small class="form-text text-muted">Use the editor to format content visually. HTML content will render on frontend.</small>
                @if($errors->has('content'))<div class="invalid-feedback">{{ $errors->first('content') }}</div>@endif
            </div>

            <div class="form-group">
                <label class="required" for="sort_order">Sort Order</label>
                <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" required>
                @if($errors->has('sort_order'))<div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>@endif
            </div>

            <div class="form-group">
                <label class="required" for="is_active">Active</label>
                <select class="form-control" name="is_active" id="is_active">
                    <option value="1" @selected(old('is_active', '1') === '1')>{{ trans('global.yes') }}</option>
                    <option value="0" @selected(old('is_active') === '0')>{{ trans('global.no') }}</option>
                </select>
            </div>

            <div class="form-group">
                <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editorElement = document.querySelector('#content');

        if (editorElement) {
            ClassicEditor
                .create(editorElement)
                .catch(function (error) {
                    console.error(error);
                });
        }
    });
</script>
@endsection
