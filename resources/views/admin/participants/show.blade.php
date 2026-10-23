@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.participant.title_singular') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.participants.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>{{ trans('cruds.participant.fields.id') }}</th>
                        <td>{{ $participant->id }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.participant.fields.name') }}</th>
                        <td>{{ $participant->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.participant.fields.email') }}</th>
                        <td>{{ $participant->email }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.participant.fields.mobile') }}</th>
                        <td>{{ $participant->mobile ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.participant.fields.user') }}</th>
                        <td>{{ $participant->user?->name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.participant.fields.status') }}</th>
                        <td>{{ $participant->is_approved ? 'Approved' : 'Pending' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.participant.fields.approved_at') }}</th>
                        <td>{{ optional($participant->approved_at)->format('d-M-Y h:i A') ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.participant.fields.last_login_ip') }}</th>
                        <td>{{ $participant->last_login_ip ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.participant.fields.last_login_at') }}</th>
                        <td>{{ optional($participant->last_login_at)->format('d-M-Y h:i A') ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
            @can('participant_approve')
                <form method="POST" action="{{ route('admin.participants.update', $participant) }}">
                    @method('PATCH')
                    @csrf
                    <input type="hidden" name="is_approved" value="{{ $participant->is_approved ? '0' : '1' }}">
                    <button type="submit" class="btn btn-primary">
                        {{ $participant->is_approved ? 'Revoke approval' : 'Approve' }}
                    </button>
                </form>
            @endcan
            <div class="form-group mt-3">
                <a class="btn btn-default" href="{{ route('admin.participants.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
