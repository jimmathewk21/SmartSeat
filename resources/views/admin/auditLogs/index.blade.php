@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('cruds.auditLog.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="filter-form mb-3">
            <div class="filter-panel">
                <div class="filter-panel__title">Filters</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-4 mb-2">
                        <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="subject_type">
                            <option value="">{{ trans('global.all') }} {{ trans('cruds.auditLog.fields.subject_type') }}</option>
                            @foreach($subjectTypes as $subjectType)
                                <option value="{{ $subjectType }}" @selected($subjectType === request('subject_type'))>{{ $subjectType }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-panel">
                <div class="filter-panel__title">Sorting</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="sort_by">
                            <option value="id" @selected(request('sort_by', 'id') === 'id')>{{ trans('cruds.auditLog.fields.id') }}</option>
                            <option value="created_at" @selected(request('sort_by') === 'created_at')>{{ trans('cruds.auditLog.fields.created_at') }}</option>
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
                <a class="btn btn-outline-secondary" href="{{ route('admin.audit-logs.index') }}">{{ trans('global.clear') }}</a>
            </div>
        </form>
        <div class="d-md-none">
            @forelse($auditLogs as $auditLog)
                <div class="card mb-2">
                    <div class="card-body">
                        <div>
                            <strong>{{ trans('cruds.auditLog.fields.id') }}:</strong>
                            {{ $auditLog->id ?? '' }}
                        </div>
                        <div>
                            <strong>{{ trans('cruds.auditLog.fields.description') }}:</strong>
                            {{ $auditLog->description ?? '' }}
                        </div>
                        <div>
                            <strong>{{ trans('cruds.auditLog.fields.subject_id') }}:</strong>
                            {{ $auditLog->subject_id ?? '' }}
                        </div>
                        <div>
                            <strong>{{ trans('cruds.auditLog.fields.subject_type') }}:</strong>
                            {{ $auditLog->subject_type ?? '' }}
                        </div>
                        <div>
                            <strong>{{ trans('cruds.auditLog.fields.user_id') }}:</strong>
                            {{ $auditLog->user_id ?? '' }}
                        </div>
                        <div>
                            <strong>{{ trans('cruds.auditLog.fields.host') }}:</strong>
                            {{ $auditLog->host ?? '' }}
                        </div>
                        <div>
                            <strong>{{ trans('cruds.auditLog.fields.created_at') }}:</strong>
                            {{ optional($auditLog->created_at)->format('d-M-Y h:i A') ?? '' }}
                        </div>
                        <div class="mt-2">
                            @include('partials.datatablesActions', [
                                'viewGate' => 'audit_log_show',
                                'editGate' => 'audit_log_edit',
                                'deleteGate' => 'audit_log_delete',
                                'crudRoutePart' => 'audit-logs',
                                'row' => $auditLog,
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
                        <th>
                            {{ trans('global.actions') }}
                        </th>
                        <th>
                            {{ trans('cruds.auditLog.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.auditLog.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.auditLog.fields.subject_id') }}
                        </th>
                        <th>
                            {{ trans('cruds.auditLog.fields.subject_type') }}
                        </th>
                        <th>
                            {{ trans('cruds.auditLog.fields.user_id') }}
                        </th>
                        <th>
                            {{ trans('cruds.auditLog.fields.host') }}
                        </th>
                        <th>
                            {{ trans('cruds.auditLog.fields.created_at') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $auditLog)
                        <tr>
                            <td>
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'audit_log_show',
                                    'editGate' => 'audit_log_edit',
                                    'deleteGate' => 'audit_log_delete',
                                    'crudRoutePart' => 'audit-logs',
                                    'row' => $auditLog,
                                ])
                            </td>
                            <td>
                                {{ $auditLog->id ?? '' }}
                            </td>
                            <td>
                                {{ $auditLog->description ?? '' }}
                            </td>
                            <td>
                                {{ $auditLog->subject_id ?? '' }}
                            </td>
                            <td>
                                {{ $auditLog->subject_type ?? '' }}
                            </td>
                            <td>
                                {{ $auditLog->user_id ?? '' }}
                            </td>
                            <td>
                                {{ $auditLog->host ?? '' }}
                            </td>
                            <td>
                                {{ optional($auditLog->created_at)->format('d-M-Y h:i A') ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">{{ trans('global.no_entries_in_table') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $auditLogs->links() }}
    </div>
</div>

@endsection
