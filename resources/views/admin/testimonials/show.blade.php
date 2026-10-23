@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.testimonial.title_singular') }}
    </div>
    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.testimonials.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr><th>{{ trans('cruds.testimonial.fields.id') }}</th><td>{{ $testimonial->id }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.event') }}</th><td>{{ $testimonial->event?->title }}</td></tr>
                    <tr>
                        <th>{{ trans('cruds.testimonial.fields.photo_url') }}</th>
                        <td>
                            @if($testimonial->photo_url)
                                @php
                                    $thumb = trim((string) $testimonial->photo_url);
                                    $isInvalidThumb = in_array($thumb, ['http://', 'https://', '//'], true);
                                    $thumbSrc = (str_starts_with($thumb, 'http://') || str_starts_with($thumb, 'https://') || str_starts_with($thumb, '//') || str_starts_with($thumb, '/'))
                                        ? ($isInvalidThumb ? null : $thumb)
                                        : asset(ltrim($thumb, '/'));
                                @endphp
                                @if($thumbSrc)
                                    <img src="{{ $thumbSrc }}" alt="{{ $testimonial->name }}" class="img-fluid" style="max-width:120px">
                                @endif
                            @endif
                        </td>
                    </tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.name') }}</th><td>{{ $testimonial->name }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.country') }}</th><td>{{ $testimonial->country?->name }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.company') }}</th><td>{{ $testimonial->company }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.designation') }}</th><td>{{ $testimonial->designation }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.message') }}</th><td>{{ $testimonial->message }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.experience_word') }}</th><td>{{ $testimonial->experience_word }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.stars') }}</th><td>{{ $testimonial->stars }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.testimonial_date') }}</th><td>{{ optional($testimonial->testimonial_date)->format('Y-m-d') }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.year') }}</th><td>{{ $testimonial->year }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.show_on_homepage') }}</th><td>{{ $testimonial->show_on_homepage ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.show_on_scrollbar') }}</th><td>{{ $testimonial->show_on_scrollbar ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.is_key_figure') }}</th><td>{{ $testimonial->is_key_figure ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.sort_order') }}</th><td>{{ $testimonial->sort_order }}</td></tr>
                    <tr><th>{{ trans('cruds.testimonial.fields.is_active') }}</th><td>{{ $testimonial->is_active ? trans('global.yes') : trans('global.no') }}</td></tr>
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.testimonials.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
    </div>
</div>
@endsection
