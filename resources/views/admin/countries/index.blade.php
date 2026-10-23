@extends('layouts.admin')
@section('content')
    @can('country_create')
        <div class="row page-actions">
            <div class="col-lg-12">
                <a class="btn btn-primary" href="{{ route('admin.countries.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.country.title_singular') }}
                </a>
                <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#csvImportModal">
                    {{ trans('global.app_csvImport') }}
                </button>
                @include('csvImport.modal', [
                    'model' => 'Country',
                    'route' => 'admin.countries.parseCsvImport',
                ])
            </div>
        </div>
    @endcan
    <div class="card">
        <div class="card-header">
            {{ trans('cruds.country.title_singular') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.countries.index') }}" class="filter-form mb-3">
                <div class="filter-panel">
                    <div class="filter-panel__title">Filters</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-4 mb-2">
                            <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input class="form-control" type="text" name="filter_short_code" value="{{ request('filter_short_code') }}" placeholder="{{ trans('cruds.country.fields.short_code') }}">
                        </div>
                    </div>
                </div>
                <div class="filter-panel">
                    <div class="filter-panel__title">Sorting</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-3 mb-2">
                            <select class="form-control" name="sort_by">
                                <option value="id" @selected(request('sort_by', 'id') === 'id')>{{ trans('cruds.country.fields.id') }}</option>
                                <option value="name" @selected(request('sort_by') === 'name')>{{ trans('cruds.country.fields.name') }}</option>
                                <option value="short_code" @selected(request('sort_by') === 'short_code')>{{ trans('cruds.country.fields.short_code') }}</option>
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
                    <a class="btn btn-outline-secondary" href="{{ route('admin.countries.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </form>
            @can('country_delete')
                <form method="POST" action="{{ route('admin.countries.massDestroy') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                    @csrf
                    @method('DELETE')
                    <div class="table-actions">
                        <button class="btn btn-danger" type="submit">{{ trans('global.delete') }}</button>
                    </div>
            @endcan

            <div class="d-md-none">
                @forelse($countries as $country)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ trans('cruds.country.fields.name') }}:</strong>
                                    {{ $country->name ?? '' }}
                                </div>
                                @can('country_delete')
                                    <div>
                                        <input type="checkbox" name="ids[]" value="{{ $country->id }}">
                                    </div>
                                @endcan
                            </div>
                            <div>
                                <strong>{{ trans('cruds.country.fields.id') }}:</strong>
                                {{ $country->id ?? '' }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.country.fields.short_code') }}:</strong>
                                {{ $country->short_code ?? '' }}
                            </div>
                            <div class="mt-2">
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'country_show',
                                    'editGate' => 'country_edit',
                                    'deleteGate' => 'country_delete',
                                    'crudRoutePart' => 'countries',
                                    'row' => $country,
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
                            @can('country_delete')
                                <th width="10"></th>
                            @endcan
                            <th>
                                {{ trans('cruds.country.fields.id') }}
                            </th>
                            <th>
                                {{ trans('cruds.country.fields.name') }}
                            </th>
                            <th>
                                {{ trans('cruds.country.fields.short_code') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($countries as $country)
                            <tr>
                                <td>
                                    @include('partials.datatablesActions', [
                                        'viewGate' => 'country_show',
                                        'editGate' => 'country_edit',
                                        'deleteGate' => 'country_delete',
                                        'crudRoutePart' => 'countries',
                                        'row' => $country,
                                    ])
                                </td>
                                @can('country_delete')
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $country->id }}">
                                    </td>
                                @endcan
                                <td>
                                    {{ $country->id ?? '' }}
                                </td>
                                <td>
                                    {{ $country->name ?? '' }}
                                </td>
                                <td>
                                    {{ $country->short_code ?? '' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                @can('country_delete')
                                    <td colspan="5">{{ trans('global.no_entries_in_table') }}</td>
                                @else
                                    <td colspan="4">{{ trans('global.no_entries_in_table') }}</td>
                                @endcan
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $countries->links() }}

            @can('country_delete')
                </form>
            @endcan
        </div>
    </div>
@endsection
