@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('cruds.participant.title') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.participants.index') }}" class="filter-form mb-3">
            <div class="filter-panel">
                <div class="filter-panel__title">Filters</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-5 mb-2">
                        <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="status">
                            <option value="">{{ trans('global.all') }} {{ trans('cruds.participant.fields.status') }}</option>
                            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
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
                            <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>{{ trans('cruds.participant.fields.created_at') }}</option>
                            <option value="approved_at" @selected(request('sort_by') === 'approved_at')>{{ trans('cruds.participant.fields.approved_at') }}</option>
                            <option value="id" @selected(request('sort_by') === 'id')>{{ trans('cruds.participant.fields.id') }}</option>
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
                <a class="btn btn-outline-secondary" href="{{ route('admin.participants.index') }}">{{ trans('global.clear') }}</a>
            </div>
        </form>

        <div class="d-md-none">
            @forelse($participants as $participant)
                <div class="card mb-3">
                    <div class="card-body">
                        <div><strong>{{ trans('cruds.participant.fields.name') }}:</strong> {{ $participant->name }}</div>
                        <div><strong>{{ trans('cruds.participant.fields.email') }}:</strong> {{ $participant->email }}</div>
                        <div><strong>{{ trans('cruds.participant.fields.mobile') }}:</strong> {{ $participant->mobile }}</div>
                        <div><strong>{{ trans('cruds.participant.fields.status') }}:</strong> {{ $participant->is_approved ? 'Approved' : 'Pending' }}</div>
                        <div><strong>{{ trans('cruds.participant.fields.approved_at') }}:</strong> {{ optional($participant->approved_at)->format('d-M-Y h:i A') ?: '-' }}</div>
                        <div class="mt-3">
                            <div class="action-buttons">
                                @can('participant_show')
                                    <a class="btn btn-sm btn-outline-primary btn-icon action-view" href="{{ route('admin.participants.show', $participant) }}" data-toggle="tooltip" title="{{ trans('global.view') }}" aria-label="{{ trans('global.view') }}">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </a>
                                @endcan
                            </div>
                            @can('participant_approve')
                                <form class="d-inline" method="POST" action="{{ route('admin.participants.update', $participant) }}">
                                    @method('PATCH')
                                    @csrf
                                    <input type="hidden" name="is_approved" value="{{ $participant->is_approved ? '0' : '1' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        {{ $participant->is_approved ? 'Revoke approval' : 'Approve' }}
                                    </button>
                                </form>
                            @endcan
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
                        <th>{{ trans('cruds.participant.fields.id') }}</th>
                        <th>{{ trans('cruds.participant.fields.name') }}</th>
                        <th>{{ trans('cruds.participant.fields.email') }}</th>
                        <th>{{ trans('cruds.participant.fields.mobile') }}</th>
                        <th>{{ trans('cruds.participant.fields.user') }}</th>
                        <th>{{ trans('cruds.participant.fields.status') }}</th>
                        <th>{{ trans('cruds.participant.fields.approved_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $participant)
                        <tr>
                            <td>
                                <div class="action-buttons">
                                    @can('participant_show')
                                        <a class="btn btn-sm btn-outline-primary btn-icon action-view" href="{{ route('admin.participants.show', $participant) }}" data-toggle="tooltip" title="{{ trans('global.view') }}" aria-label="{{ trans('global.view') }}">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </a>
                                    @endcan
                                </div>
                                @can('participant_approve')
                                    <form class="d-inline" method="POST" action="{{ route('admin.participants.update', $participant) }}">
                                        @method('PATCH')
                                        @csrf
                                        <input type="hidden" name="is_approved" value="{{ $participant->is_approved ? '0' : '1' }}">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            {{ $participant->is_approved ? 'Revoke approval' : 'Approve' }}
                                        </button>
                                    </form>
                                @endcan
                            </td>
                            <td>{{ $participant->id }}</td>
                            <td>{{ $participant->name }}</td>
                            <td>{{ $participant->email }}</td>
                            <td>{{ $participant->mobile ?: '-' }}</td>
                            <td>{{ $participant->user?->name ?: '-' }}</td>
                            <td>{{ $participant->is_approved ? 'Approved' : 'Pending' }}</td>
                            <td>{{ optional($participant->approved_at)->format('d-M-Y h:i A') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">{{ trans('global.no_entries_in_table') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $participants->links() }}
    </div>
</div>

@endsection
