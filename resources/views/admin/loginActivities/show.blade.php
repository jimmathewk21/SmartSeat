@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.loginActivity.title_singular') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.login-activities.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>{{ trans('cruds.loginActivity.fields.id') }}</th>
                        <td>{{ $loginActivity->id }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.loginActivity.fields.status') }}</th>
                        <td>{{ ucfirst($loginActivity->status) }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.loginActivity.fields.email') }}</th>
                        <td>{{ $loginActivity->email ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Company Name</th>
                        <td>{{ $loginActivity->user?->company_name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.loginActivity.fields.ip_address') }}</th>
                        <td>{{ $loginActivity->ip_address ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>City</th>
                        <td>{{ $loginActivity->geo_location['city'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Country</th>
                        <td>{{ $loginActivity->geo_location['country'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.loginActivity.fields.user_agent') }}</th>
                        <td>{{ $loginActivity->user_agent ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Activity Date & Time</th>
                        <td>{{ ($loginActivity->attempted_at ?? $loginActivity->created_at) ? \Carbon\Carbon::parse($loginActivity->attempted_at ?? $loginActivity->created_at)->format('d-M-Y h:i A') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('cruds.loginActivity.fields.created_at') }}</th>
                        <td>{{ optional($loginActivity->created_at)->format('d-M-Y h:i A') ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.login-activities.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
