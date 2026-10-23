@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">{{ trans('global.show') }} {{ trans('cruds.speaker.title_singular') }}</div>
    <div class="card-body">
        <div class="form-group"><a class="btn btn-default" href="{{ route('admin.speakers.index') }}">{{ trans('global.back_to_list') }}</a></div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr><th>{{ trans('cruds.speaker.fields.id') }}</th><td>{{ $speaker->id }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.event') }}</th><td>{{ $speaker->event?->title }}</td></tr>
                    <tr>
                        <th>{{ trans('cruds.speaker.fields.photo_url') }}</th>
                        <td>
                            <img src="{{ $speaker->display_photo_url }}" alt="{{ $speaker->name }}" class="img-fluid" style="max-width:100px">
                        </td>
                    </tr>
                    <tr><th>{{ trans('cruds.speaker.fields.gender') }}</th><td>{{ $speaker->gender ? ucfirst($speaker->gender) : '' }}</td></tr>
                    <tr>
                        <th>Speaker / Moderator</th>
                        <td>{{ match ((string) ($speaker->speaker_role ?? 'speaker')) { 'moderator' => 'Moderator', 'both' => 'Speaker & Moderator', default => 'Speaker' } }}</td>
                    </tr>
                    <tr><th>{{ trans('cruds.speaker.fields.name') }}</th><td>{{ $speaker->name }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.designation') }}</th><td>{{ $speaker->designation }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.company_name') }}</th><td>{{ $speaker->company_name }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.country') }}</th><td>{{ $speaker->country }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.email') }}</th><td>{{ $speaker->email }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.phone') }}</th><td>{{ $speaker->phone }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.topic') }}</th><td>{{ $speaker->topic }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.topic_category') }}</th><td>{{ $speaker->topic_category }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.category') }}</th><td>{{ $speaker->category }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.biography_type') }}</th><td>{{ ucfirst($speaker->biography_type ?? '') }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.resource_url') }}</th><td>@if($speaker->resource_url)<a href="{{ $speaker->resource_url }}" target="_blank" rel="noopener noreferrer">{{ $speaker->resource_url }}</a>@endif</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.bio') }}</th><td>{{ $speaker->bio }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.show_on_homepage') }}</th><td>{{ $speaker->show_on_homepage ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.show_on_scrollbar') }}</th><td>{{ $speaker->show_on_scrollbar ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.is_key_figure') }}</th><td>{{ $speaker->is_key_figure ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.sort_order') }}</th><td>{{ $speaker->sort_order }}</td></tr>
                    <tr><th>{{ trans('cruds.speaker.fields.is_active') }}</th><td>{{ $speaker->is_active ? trans('global.yes') : trans('global.no') }}</td></tr>
                </tbody>
            </table>
        </div>
        <div class="form-group"><a class="btn btn-default" href="{{ route('admin.speakers.index') }}">{{ trans('global.back_to_list') }}</a></div>
    </div>
</div>
@endsection
