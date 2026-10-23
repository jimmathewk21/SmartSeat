@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.attraction.title_singular') }}
    </div>
    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.attractions.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr><th>{{ trans('cruds.attraction.fields.id') }}</th><td>{{ $attraction->id }}</td></tr>
                    <tr><th>{{ trans('cruds.attraction.fields.name') }}</th><td>{{ $attraction->name }}</td></tr>
                    <tr><th>Category</th><td>{{ $attraction->category ?: '-' }}</td></tr>
                    <tr><th>{{ trans('cruds.attraction.fields.image_url') }}</th><td>@if($attraction->image_url)<img src="{{ str_starts_with($attraction->image_url, 'http://') || str_starts_with($attraction->image_url, 'https://') || str_starts_with($attraction->image_url, '//') || str_starts_with($attraction->image_url, '/') ? $attraction->image_url : asset(ltrim($attraction->image_url, '/')) }}" alt="{{ $attraction->name }}" class="img-fluid" style="max-width:140px">@endif</td></tr>
                    <tr><th>Website Link</th><td>@if($attraction->website_url)<a href="{{ $attraction->website_url }}" target="_blank" rel="noopener">{{ $attraction->website_url }}</a>@else - @endif</td></tr>
                    <tr><th>Instagram Link</th><td>@if($attraction->instagram_url)<a href="{{ $attraction->instagram_url }}" target="_blank" rel="noopener">{{ $attraction->instagram_url }}</a>@else - @endif</td></tr>
                    <tr><th>Direction Link</th><td>@if($attraction->direction_url)<a href="{{ $attraction->direction_url }}" target="_blank" rel="noopener">{{ $attraction->direction_url }}</a>@else - @endif</td></tr>
                    <tr><th>{{ trans('cruds.attraction.fields.detail') }}</th><td>{{ $attraction->detail }}</td></tr>
                    <tr><th>{{ trans('cruds.attraction.fields.sort_order') }}</th><td>{{ $attraction->sort_order }}</td></tr>
                    <tr><th>{{ trans('cruds.attraction.fields.show_on_homepage') }}</th><td>{{ $attraction->show_on_homepage ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.attraction.fields.is_active') }}</th><td>{{ $attraction->is_active ? trans('global.yes') : trans('global.no') }}</td></tr>
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.attractions.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
    </div>
</div>
@endsection
