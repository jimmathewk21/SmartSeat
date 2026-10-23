@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('cruds.loginActivity.title') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            @can('login_activity_access')
                <form method="POST" action="{{ route('admin.login-activities.clearAll') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ trans('global.delete') }} All</button>
                </form>
            @endcan
        </div>
        <form method="GET" action="{{ route('admin.login-activities.index') }}" class="filter-form mb-3">
            <div class="filter-panel">
                <div class="filter-panel__title">Filters</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-5 mb-2">
                        <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="status">
                            <option value="">{{ trans('global.all') }} {{ trans('cruds.loginActivity.fields.status') }}</option>
                            <option value="success" @selected(request('status') === 'success')>Success</option>
                            <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-panel">
                <div class="filter-panel__title">Sorting</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="sort_by">
                            <option value="attempted_at" @selected(request('sort_by', 'attempted_at') === 'attempted_at')>{{ trans('cruds.loginActivity.fields.attempted_at') }}</option>
                            <option value="id" @selected(request('sort_by') === 'id')>{{ trans('cruds.loginActivity.fields.id') }}</option>
                            <option value="created_at" @selected(request('sort_by') === 'created_at')>{{ trans('cruds.loginActivity.fields.created_at') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select class="form-control" name="sort_dir">
                            <option value="desc" @selected(request('sort_dir', 'desc') === 'desc')>Descending</option>
                            <option value="asc" @selected(request('sort_dir') === 'asc')>Ascending</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.login-activities.index') }}">{{ trans('global.clear') }}</a>
            </div>
        </form>

        <div class="d-md-none">
            @forelse($loginActivities as $loginActivity)
                <div class="card mb-2">
                    <div class="card-body">
                        @php($activityAt = $loginActivity->attempted_at ?? $loginActivity->created_at)
                        <div><strong>{{ trans('cruds.loginActivity.fields.id') }}:</strong> {{ $loginActivity->id }}</div>
                        <div><strong>{{ trans('cruds.loginActivity.fields.status') }}:</strong> {{ ucfirst($loginActivity->status) }}</div>
                        <div><strong>{{ trans('cruds.loginActivity.fields.email') }}:</strong> {{ $loginActivity->email }}</div>
                        <div><strong>Company Name:</strong> {{ $loginActivity->user?->company_name ?: '-' }}</div>
                        <div><strong>{{ trans('cruds.loginActivity.fields.ip_address') }}:</strong> {{ $loginActivity->ip_address ?: '-' }}</div>
                        <div><strong>City:</strong> {{ $loginActivity->geo_location['city'] ?? '-' }}</div>
                        <div><strong>Country:</strong> {{ $loginActivity->geo_location['country'] ?? '-' }}</div>
                        <div><strong>Activity Date & Time:</strong> {{ $activityAt ? \Carbon\Carbon::parse($activityAt)->format('d-M-Y h:i A') : '-' }}</div>
                        <div class="mt-2">
                            @include('partials.datatablesActions', [
                                'viewGate' => 'login_activity_show',
                                'editGate' => 'login_activity_edit',
                                'deleteGate' => 'login_activity_delete',
                                'crudRoutePart' => 'login-activities',
                                'row' => $loginActivity,
                            ])
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-muted">{{ trans('global.no_entries_in_table') }}</div>
            @endforelse
        </div>

        <div class="table-responsive d-none d-md-block">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ trans('global.actions') }}</th>
                        <th>{{ trans('cruds.loginActivity.fields.id') }}</th>
                        <th>{{ trans('cruds.loginActivity.fields.status') }}</th>
                        <th>{{ trans('cruds.loginActivity.fields.email') }}</th>
                        <th>Company Name</th>
                        <th>{{ trans('cruds.loginActivity.fields.ip_address') }}</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Activity Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loginActivities as $loginActivity)
                        @php($activityAt = $loginActivity->attempted_at ?? $loginActivity->created_at)
                        <tr>
                            <td>
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'login_activity_show',
                                    'editGate' => 'login_activity_edit',
                                    'deleteGate' => 'login_activity_delete',
                                    'crudRoutePart' => 'login-activities',
                                    'row' => $loginActivity,
                                ])
                            </td>
                            <td>{{ $loginActivity->id }}</td>
                            <td>{{ ucfirst($loginActivity->status) }}</td>
                            <td>{{ $loginActivity->email }}</td>
                            <td>{{ $loginActivity->user?->company_name ?: '-' }}</td>
                            <td>{{ $loginActivity->ip_address ?: '-' }}</td>
                            <td>{{ $loginActivity->geo_location['city'] ?? '-' }}</td>
                            <td>{{ $loginActivity->geo_location['country'] ?? '-' }}</td>
                            <td>{{ $activityAt ? \Carbon\Carbon::parse($activityAt)->format('d-M-Y h:i A') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">{{ trans('global.no_entries_in_table') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $loginActivities->links() }}
    </div>
</div>

@endsection
