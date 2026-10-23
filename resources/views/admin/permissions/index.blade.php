@extends('layouts.admin')
@section('content')
@can('permission_create')
    <div class="row page-actions">
        <div class="col-lg-12">
            <a class="btn btn-primary" href="{{ route('admin.permissions.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.permission.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.permission.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.permissions.index') }}" class="filter-form mb-3">
            <div class="filter-panel">
                <div class="filter-panel__title">Filters</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-4 mb-2">
                        <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <input class="form-control" type="text" name="filter_title" value="{{ request('filter_title') }}" placeholder="{{ trans('cruds.permission.fields.title') }}">
                    </div>
                </div>
            </div>
            <div class="filter-panel">
                <div class="filter-panel__title">Sorting</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="sort_by">
                            <option value="id" @selected(request('sort_by', 'id') === 'id')>{{ trans('cruds.permission.fields.id') }}</option>
                            <option value="title" @selected(request('sort_by') === 'title')>{{ trans('cruds.permission.fields.title') }}</option>
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
                <a class="btn btn-outline-secondary" href="{{ route('admin.permissions.index') }}">{{ trans('global.clear') }}</a>
            </div>
        </form>
        @can('permission_delete')
            <form method="POST" action="{{ route('admin.permissions.massDestroy') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                @csrf
                @method('DELETE')
                <div class="table-actions">
                    <button class="btn btn-danger" type="submit">{{ trans('global.delete') }}</button>
                </div>
        @endcan

        <div class="d-md-none">
            @forelse($permissions as $permission)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ trans('cruds.permission.fields.title') }}:</strong>
                                {{ $permission->title ?? '' }}
                            </div>
                            @can('permission_delete')
                                <div>
                                    <input type="checkbox" name="ids[]" value="{{ $permission->id }}">
                                </div>
                            @endcan
                        </div>
                        <div>
                            <strong>{{ trans('cruds.permission.fields.id') }}:</strong>
                            {{ $permission->id ?? '' }}
                        </div>
                        <div class="mt-2">
                            @include('partials.datatablesActions', [
                                'viewGate' => 'permission_show',
                                'editGate' => 'permission_edit',
                                'deleteGate' => 'permission_delete',
                                'crudRoutePart' => 'permissions',
                                'row' => $permission,
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
                        @can('permission_delete')
                            <th width="10"></th>
                        @endcan
                        <th>
                            {{ trans('cruds.permission.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.permission.fields.title') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr>
                            <td>
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'permission_show',
                                    'editGate' => 'permission_edit',
                                    'deleteGate' => 'permission_delete',
                                    'crudRoutePart' => 'permissions',
                                    'row' => $permission,
                                ])
                            </td>
                            @can('permission_delete')
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $permission->id }}">
                                </td>
                            @endcan
                            <td>
                                {{ $permission->id ?? '' }}
                            </td>
                            <td>
                                {{ $permission->title ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @can('permission_delete')
                                <td colspan="4">{{ trans('global.no_entries_in_table') }}</td>
                            @else
                                <td colspan="3">{{ trans('global.no_entries_in_table') }}</td>
                            @endcan
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $permissions->links() }}

        @can('permission_delete')
            </form>
        @endcan
    </div>
</div>
@endsection
