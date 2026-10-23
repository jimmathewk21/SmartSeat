@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.member.title_singular') }}
    </div>
    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.members.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr><th>{{ trans('cruds.member.fields.id') }}</th><td>{{ $member->id }}</td></tr>
                    <tr><th>{{ trans('cruds.member.fields.photo_url') }}</th><td>@if($member->photo_url)<img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="img-fluid" style="max-width:120px">@endif</td></tr>
                    <tr><th>{{ trans('cruds.member.fields.name') }}</th><td>{{ $member->name }}</td></tr>
                    <tr><th>{{ trans('cruds.member.fields.designation') }}</th><td>{{ $member->designation }}</td></tr>
                    <tr><th>{{ trans('cruds.member.fields.company') }}</th><td>{{ $member->company }}</td></tr>
                    <tr><th>{{ trans('cruds.member.fields.sort_order') }}</th><td>{{ $member->sort_order }}</td></tr>
                    <tr><th>{{ trans('cruds.member.fields.is_active') }}</th><td>{{ $member->is_active ? trans('global.yes') : trans('global.no') }}</td></tr>
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.members.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
    </div>
</div>
@endsection
