@extends('layouts.admin')
@section('content')
    @can('sponsor_category_create')
        <div class="row page-actions">
            <div class="col-lg-12">
                <a class="btn btn-primary" href="{{ route('admin.sponsor-categories.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.sponsorCategory.title_singular') }}
                </a>
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            {{ trans('cruds.sponsorCategory.title') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.sponsor-categories.index') }}" class="filter-form mb-3">
                <div class="filter-panel">
                    <div class="filter-panel__title">Filters</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-4 mb-2">
                            <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select class="form-control" name="is_active">
                                <option value="">{{ trans('global.all') }} {{ trans('global.status') }}</option>
                                <option value="1" @selected(request('is_active') === '1')>{{ trans('global.yes') }}</option>
                                <option value="0" @selected(request('is_active') === '0')>{{ trans('global.no') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="filter-panel">
                    <div class="filter-panel__title">Sorting</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-3 mb-2">
                            <select class="form-control" name="sort_by">
                                <option value="sort_order" @selected(request('sort_by', 'sort_order') === 'sort_order')>{{ trans('cruds.sponsorCategory.fields.sort_order') }}</option>
                                <option value="name" @selected(request('sort_by') === 'name')>{{ trans('cruds.sponsorCategory.fields.name') }}</option>
                                <option value="id" @selected(request('sort_by') === 'id')>{{ trans('cruds.sponsorCategory.fields.id') }}</option>
                                <option value="is_active" @selected(request('sort_by') === 'is_active')>{{ trans('cruds.sponsorCategory.fields.is_active') }}</option>
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
                    <a class="btn btn-outline-secondary" href="{{ route('admin.sponsor-categories.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </form>

            @can('sponsor_category_delete')
                <form method="POST" action="{{ route('admin.sponsor-categories.massDestroy') }}" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                    @csrf
                    @method('DELETE')
                    <div class="table-actions">
                        <button class="btn btn-danger" type="submit">{{ trans('global.delete') }}</button>
                    </div>
            @endcan

            <div class="d-md-none">
                @forelse($sponsorCategories as $sponsorCategory)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ trans('cruds.sponsorCategory.fields.name') }}:</strong>
                                    {{ $sponsorCategory->name ?? '' }}
                                </div>
                                @can('sponsor_category_delete')
                                    <div>
                                        <input type="checkbox" name="ids[]" value="{{ $sponsorCategory->id }}">
                                    </div>
                                @endcan
                            </div>
                            <div>
                                <strong>{{ trans('cruds.sponsorCategory.fields.id') }}:</strong>
                                {{ $sponsorCategory->id ?? '' }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.sponsorCategory.fields.sort_order') }}:</strong>
                                {{ $sponsorCategory->sort_order ?? 0 }}
                            </div>
                            <div>
                                <strong>Logo Size:</strong>
                                {{ ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'][strtolower($sponsorCategory->logo_size ?? 'md')] ?? 'Medium' }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.sponsorCategory.fields.is_active') }}:</strong>
                                {{ $sponsorCategory->is_active ? trans('global.yes') : trans('global.no') }}
                            </div>
                            <div class="mt-2">
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'sponsor_category_show',
                                    'editGate' => 'sponsor_category_edit',
                                    'deleteGate' => 'sponsor_category_delete',
                                    'crudRoutePart' => 'sponsor-categories',
                                    'row' => $sponsorCategory,
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
                            @can('sponsor_category_delete')
                                <th width="10"></th>
                            @endcan
                            <th>{{ trans('cruds.sponsorCategory.fields.id') }}</th>
                            <th>{{ trans('cruds.sponsorCategory.fields.name') }}</th>
                            <th>{{ trans('cruds.sponsorCategory.fields.sort_order') }}</th>
                            <th>Logo Size</th>
                            <th>{{ trans('cruds.sponsorCategory.fields.is_active') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sponsorCategories as $sponsorCategory)
                            <tr>
                                <td>
                                    @include('partials.datatablesActions', [
                                        'viewGate' => 'sponsor_category_show',
                                        'editGate' => 'sponsor_category_edit',
                                        'deleteGate' => 'sponsor_category_delete',
                                        'crudRoutePart' => 'sponsor-categories',
                                        'row' => $sponsorCategory,
                                    ])
                                </td>
                                @can('sponsor_category_delete')
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $sponsorCategory->id }}">
                                    </td>
                                @endcan
                                <td>{{ $sponsorCategory->id ?? '' }}</td>
                                <td>{{ $sponsorCategory->name ?? '' }}</td>
                                <td>{{ $sponsorCategory->sort_order ?? 0 }}</td>
                                <td>{{ ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'][strtolower($sponsorCategory->logo_size ?? 'md')] ?? 'Medium' }}</td>
                                <td>{{ $sponsorCategory->is_active ? trans('global.yes') : trans('global.no') }}</td>
                            </tr>
                        @empty
                            <tr>
                                @can('sponsor_category_delete')
                                    <td colspan="7">{{ trans('global.no_entries_in_table') }}</td>
                                @else
                                    <td colspan="6">{{ trans('global.no_entries_in_table') }}</td>
                                @endcan
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sponsorCategories->links() }}

            @can('sponsor_category_delete')
                </form>
            @endcan
        </div>
    </div>
@endsection
