@extends('layouts.admin')
@section('content')
@can('state_create')
    <div class="row page-actions">
        <div class="col-lg-12">
            <a class="btn btn-primary" href="{{ route('admin.states.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.state.title_singular') }}
            </a>
            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#csvImportModal">
                {{ trans('global.app_csvImport') }}
            </button>
            @include('csvImport.modal', ['model' => 'State', 'route' => 'admin.states.parseCsvImport'])
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.state.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.states.index') }}" class="filter-form mb-3">
            <div class="filter-panel">
                <div class="filter-panel__title">Filters</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-4 mb-2">
                        <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="country_id">
                            <option value="">{{ trans('global.all') }} {{ trans('cruds.state.fields.country') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" @selected((string) $country->id === (string) request('country_id'))>{{ $country->name }}</option>
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
                            <option value="id" @selected(request('sort_by', 'id') === 'id')>{{ trans('cruds.state.fields.id') }}</option>
                            <option value="name" @selected(request('sort_by') === 'name')>{{ trans('cruds.state.fields.name') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select class="form-control" name="sort_dir">
                            <option value="asc" @selected(request('sort_dir', 'asc') === 'asc')>Ascending</option>
                            <option value="desc" @selected(request('sort_dir') === 'desc')>Descending</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.states.index') }}">{{ trans('global.clear') }}</a>
            </div>
        </form>
        @can('state_delete')
            <form method="POST" action="{{ route('admin.states.massDestroy') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                @csrf
                @method('DELETE')
                <div class="table-actions">
                    <button class="btn btn-danger" type="submit">{{ trans('global.delete') }}</button>
                </div>
        @endcan

        <div class="d-md-none">
            @forelse($states as $state)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ trans('cruds.state.fields.name') }}:</strong>
                                {{ $state->name ?? '' }}
                            </div>
                            @can('state_delete')
                                <div>
                                    <input type="checkbox" name="ids[]" value="{{ $state->id }}">
                                </div>
                            @endcan
                        </div>
                        <div>
                            <strong>{{ trans('cruds.state.fields.id') }}:</strong>
                            {{ $state->id ?? '' }}
                        </div>
                        <div>
                            <strong>{{ trans('cruds.state.fields.country') }}:</strong>
                            {{ $state->country?->name ?? '' }}
                        </div>
                        <div class="mt-2">
                            @include('partials.datatablesActions', [
                                'viewGate' => 'state_show',
                                'editGate' => 'state_edit',
                                'deleteGate' => 'state_delete',
                                'crudRoutePart' => 'states',
                                'row' => $state,
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
                        @can('state_delete')
                            <th width="10"></th>
                        @endcan
                        <th>
                            {{ trans('cruds.state.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.state.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.state.fields.country') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($states as $state)
                        <tr>
                            <td>
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'state_show',
                                    'editGate' => 'state_edit',
                                    'deleteGate' => 'state_delete',
                                    'crudRoutePart' => 'states',
                                    'row' => $state,
                                ])
                            </td>
                            @can('state_delete')
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $state->id }}">
                                </td>
                            @endcan
                            <td>
                                {{ $state->id ?? '' }}
                            </td>
                            <td>
                                {{ $state->name ?? '' }}
                            </td>
                            <td>
                                {{ $state->country?->name ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @can('state_delete')
                                <td colspan="5">{{ trans('global.no_entries_in_table') }}</td>
                            @else
                                <td colspan="4">{{ trans('global.no_entries_in_table') }}</td>
                            @endcan
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $states->links() }}

        @can('state_delete')
            </form>
        @endcan
    </div>
</div>
@endsection
