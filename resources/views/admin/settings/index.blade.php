@extends('layouts.admin')
@section('content')
@can('setting_create')
    <div class="row page-actions">
        <div class="col-lg-12">
            <a class="btn btn-primary" href="{{ route('admin.settings.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.setting.title_singular') }}
            </a>
            <a class="btn btn-outline-primary" href="{{ route('admin.settings.site-meta.edit') }}">
                Site Settings
            </a>
            <a class="btn btn-outline-primary" href="{{ route('admin.settings.email-templates.edit') }}">
                Email Templates
            </a>
            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#csvImportModal">
                {{ trans('global.app_csvImport') }}
            </button>
            <a class="btn btn-outline-primary" href="{{ route('admin.settings.maintenance-page.edit') }}">
                Maintenance Page
            </a>
            @include('csvImport.modal', ['model' => 'Setting', 'route' => 'admin.settings.parseCsvImport'])
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.setting.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.settings.index') }}" class="filter-form mb-3">
            <div class="filter-panel">
                <div class="filter-panel__title">Filters</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-4 mb-2">
                        <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <input class="form-control" type="text" name="filter_key" value="{{ request('filter_key') }}" placeholder="{{ trans('cruds.setting.fields.key') }}">
                    </div>
                </div>
            </div>
            <div class="filter-panel">
                <div class="filter-panel__title">Sorting</div>
                <div class="form-row filter-panel__grid">
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="sort_by">
                            <option value="id" @selected(request('sort_by', 'id') === 'id')>{{ trans('cruds.setting.fields.id') }}</option>
                            <option value="key" @selected(request('sort_by') === 'key')>{{ trans('cruds.setting.fields.key') }}</option>
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
                <a class="btn btn-outline-secondary" href="{{ route('admin.settings.index') }}">{{ trans('global.clear') }}</a>
            </div>
        </form>
        @can('setting_delete')
            <form method="POST" action="{{ route('admin.settings.massDestroy') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                @csrf
                @method('DELETE')
                <div class="table-actions">
                    <button class="btn btn-danger" type="submit">{{ trans('global.delete') }}</button>
                </div>
        @endcan

        <div class="d-md-none">
            @forelse($settings as $setting)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ trans('cruds.setting.fields.key') }}:</strong>
                                {{ $setting->key ?? '' }}
                            </div>
                            @can('setting_delete')
                                <div>
                                    <input type="checkbox" name="ids[]" value="{{ $setting->id }}">
                                </div>
                            @endcan
                        </div>
                        <div>
                            <strong>{{ trans('cruds.setting.fields.id') }}:</strong>
                            {{ $setting->id ?? '' }}
                        </div>
                        <div>
                            <strong>{{ trans('cruds.setting.fields.value') }}:</strong>
                            {{ $setting->value ?? '' }}
                        </div>
                        <div class="mt-2">
                            @include('partials.datatablesActions', [
                                'viewGate' => 'setting_show',
                                'editGate' => 'setting_edit',
                                'deleteGate' => 'setting_delete',
                                'crudRoutePart' => 'settings',
                                'row' => $setting,
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
                        @can('setting_delete')
                            <th width="10"></th>
                        @endcan
                        <th>
                            {{ trans('cruds.setting.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.setting.fields.key') }}
                        </th>
                        <th>
                            {{ trans('cruds.setting.fields.value') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settings as $setting)
                        <tr>
                            <td>
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'setting_show',
                                    'editGate' => 'setting_edit',
                                    'deleteGate' => 'setting_delete',
                                    'crudRoutePart' => 'settings',
                                    'row' => $setting,
                                ])
                            </td>
                            @can('setting_delete')
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $setting->id }}">
                                </td>
                            @endcan
                            <td>
                                {{ $setting->id ?? '' }}
                            </td>
                            <td>
                                {{ $setting->key ?? '' }}
                            </td>
                            <td>
                                {{ $setting->value ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @can('setting_delete')
                                <td colspan="5">{{ trans('global.no_entries_in_table') }}</td>
                            @else
                                <td colspan="4">{{ trans('global.no_entries_in_table') }}</td>
                            @endcan
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $settings->links() }}

        @can('setting_delete')
            </form>
        @endcan
    </div>
</div>
@endsection
