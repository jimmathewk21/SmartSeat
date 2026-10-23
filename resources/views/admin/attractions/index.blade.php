@extends('layouts.admin')

@section('styles')
    @parent
    <style>
        .attraction-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .attraction-toolbar__actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .attraction-filter-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 420px) auto;
            gap: 12px;
            align-items: end;
        }

        .attraction-filter-row > .filter-panel {
            min-width: 0;
        }

        .attraction-filter-row > .filter-panel:first-child .form-row {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .attraction-filter-row > .filter-panel:nth-child(2) .form-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .attraction-filter-row .filter-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            white-space: nowrap;
            align-self: center;
            margin: 0;
        }

        @media (max-width: 1199.98px) {
            .attraction-filter-row {
                grid-template-columns: 1fr;
            }

            .attraction-filter-row > .filter-panel:first-child .form-row,
            .attraction-filter-row > .filter-panel:nth-child(2) .form-row {
                grid-template-columns: 1fr;
            }

            .attraction-filter-row .filter-actions {
                flex-wrap: wrap;
                white-space: normal;
            }
        }
    </style>
@endsection

@section('content')
    @php use Illuminate\Support\Str; @endphp

    @can('attraction_create')
        <div class="attraction-toolbar">
            <div class="attraction-toolbar__actions">
                <a class="btn btn-primary" href="{{ route('admin.attractions.create') }}">{{ trans('global.add') }} {{ trans('cruds.attraction.title_singular') }}</a>
                <a class="btn btn-info" href="{{ route('admin.attractions.page-settings.edit') }}">Page Settings</a>
            </div>
            <div class="attraction-toolbar__actions">
                <a class="btn btn-outline-success" href="{{ route('admin.attractions.export', request()->query()) }}">Export CSV</a>
                @can('attraction_delete')
                    <button class="btn btn-danger" type="submit" form="attractionDeleteForm" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('attraction_edit')
                    <button class="btn btn-success" type="button" data-bulk-status="publish">Publish Selected</button>
                    <button class="btn btn-warning" type="button" data-bulk-status="unpublish">Unpublish Selected</button>
                @endcan
            </div>
        </div>
    @else
        <div class="attraction-toolbar">
            <div class="attraction-toolbar__actions">
                <a class="btn btn-info" href="{{ route('admin.attractions.page-settings.edit') }}">Page Settings</a>
            </div>
            <div class="attraction-toolbar__actions">
                <a class="btn btn-outline-success" href="{{ route('admin.attractions.export', request()->query()) }}">Export CSV</a>
                @can('attraction_delete')
                    <button class="btn btn-danger" type="submit" form="attractionDeleteForm" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('attraction_edit')
                    <button class="btn btn-success" type="button" data-bulk-status="publish">Publish Selected</button>
                    <button class="btn btn-warning" type="button" data-bulk-status="unpublish">Unpublish Selected</button>
                @endcan
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">{{ trans('cruds.attraction.title') }} {{ trans('global.list') }}</div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attractions.index') }}" class="filter-form mb-3">
                <div class="attraction-filter-row">
                    <div class="filter-panel">
                        <div class="filter-panel__title">Filters</div>
                        <div class="form-row filter-panel__grid">
                            <div class="col-md-4 mb-2">
                                <input class="form-control" name="search" type="text" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" name="category">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" name="is_active">
                                    <option value="">{{ trans('global.all') }} {{ trans('global.status') }}</option>
                                    <option value="1" @selected(request('is_active') === '1')>{{ trans('global.yes') }}</option>
                                    <option value="0" @selected(request('is_active') === '0')>{{ trans('global.no') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" name="show_on_homepage">
                                    <option value="">{{ trans('global.all') }} {{ trans('cruds.attraction.fields.show_on_homepage') }}</option>
                                    <option value="1" @selected(request('show_on_homepage') === '1')>{{ trans('global.yes') }}</option>
                                    <option value="0" @selected(request('show_on_homepage') === '0')>{{ trans('global.no') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="filter-panel">
                        <div class="filter-panel__title">Sorting</div>
                        <div class="form-row filter-panel__grid">
                            <div class="col-md-3 mb-2">
                                <select class="form-control" name="sort_by">
                                    <option value="sort_order" @selected(request('sort_by', 'sort_order') === 'sort_order')>{{ trans('cruds.attraction.fields.sort_order') }}</option>
                                    <option value="name" @selected(request('sort_by') === 'name')>{{ trans('cruds.attraction.fields.name') }}</option>
                                    <option value="id" @selected(request('sort_by') === 'id')>{{ trans('cruds.attraction.fields.id') }}</option>
                                    <option value="is_active" @selected(request('sort_by') === 'is_active')>{{ trans('cruds.attraction.fields.is_active') }}</option>
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
                        <a class="btn btn-outline-secondary" href="{{ route('admin.attractions.index') }}">{{ trans('global.clear') }}</a>
                    </div>
                </div>
            </form>

            @canany(['attraction_delete', 'attraction_edit'])
                <form id="attractionBulkForm" method="POST" action="{{ route('admin.attractions.bulkStatus') }}">
                    @csrf
                    <input type="hidden" name="status" id="attractionBulkStatus" value="">
                </form>
                <form id="attractionDeleteForm" method="POST" action="{{ route('admin.attractions.massDestroy') }}">
                    @csrf
                    @method('DELETE')
                </form>
            @endcanany

            <div class="d-md-none">
                @forelse($attractions as $attraction)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    @if($attraction->image_url)
                                        <img src="{{ str_starts_with($attraction->image_url, 'http://') || str_starts_with($attraction->image_url, 'https://') || str_starts_with($attraction->image_url, '//') || str_starts_with($attraction->image_url, '/') ? $attraction->image_url : asset(ltrim($attraction->image_url, '/')) }}" alt="{{ $attraction->name }}" class="img-fluid" style="max-width:50px; margin-right:8px;">
                                    @endif
                                    <strong>{{ $attraction->name }}</strong>
                                </div>
                                @canany(['attraction_delete', 'attraction_edit'])
                                    <div>
                                        <input type="checkbox" name="ids[]" value="{{ $attraction->id }}" class="attraction-row-checkbox" form="attractionBulkForm">
                                    </div>
                                @endcanany
                            </div>
                            <div>{{ $attraction->category ?: 'Uncategorized' }}</div>
                            <div>{{ Str::limit($attraction->detail ?? '', 60) }}</div>
                            <div class="mt-2">{{ trans('cruds.attraction.fields.sort_order') }}: {{ $attraction->sort_order }}</div>
                            <div>{{ $attraction->show_on_homepage ? trans('global.yes') : trans('global.no') }} | {{ $attraction->is_active ? 'Published' : 'Unpublished' }}</div>
                            <div class="mt-2">
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'attraction_show',
                                    'editGate' => 'attraction_edit',
                                    'deleteGate' => 'attraction_delete',
                                    'crudRoutePart' => 'attractions',
                                    'row' => $attraction,
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
                            @canany(['attraction_delete', 'attraction_edit'])
                                <th width="10">
                                    <input type="checkbox" id="select-all-attractions" aria-label="Select all attractions">
                                </th>
                            @endcanany
                            <th>{{ trans('cruds.attraction.fields.id') }}</th>
                            <th>{{ trans('cruds.attraction.fields.image_url') }}</th>
                            <th>Category</th>
                            <th>{{ trans('cruds.attraction.fields.name') }}</th>
                            <th>{{ trans('cruds.attraction.fields.detail') }}</th>
                            <th>{{ trans('cruds.attraction.fields.sort_order') }}</th>
                            <th>{{ trans('cruds.attraction.fields.show_on_homepage') }}</th>
                            <th>{{ trans('cruds.attraction.fields.is_active') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attractions as $attraction)
                            <tr>
                                <td>
                                    @include('partials.datatablesActions', [
                                        'viewGate' => 'attraction_show',
                                        'editGate' => 'attraction_edit',
                                        'deleteGate' => 'attraction_delete',
                                        'crudRoutePart' => 'attractions',
                                        'row' => $attraction,
                                    ])
                                </td>
                                @canany(['attraction_delete', 'attraction_edit'])
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $attraction->id }}" class="attraction-row-checkbox" form="attractionBulkForm">
                                    </td>
                                @endcanany
                                <td>{{ $attraction->id }}</td>
                                <td>
                                    @if($attraction->image_url)
                                        <img src="{{ str_starts_with($attraction->image_url, 'http://') || str_starts_with($attraction->image_url, 'https://') || str_starts_with($attraction->image_url, '//') || str_starts_with($attraction->image_url, '/') ? $attraction->image_url : asset(ltrim($attraction->image_url, '/')) }}" alt="{{ $attraction->name }}" class="img-fluid" style="max-width:60px;">
                                    @else
                                        <span class="text-muted small">No image</span>
                                    @endif
                                </td>
                                <td>{{ $attraction->category ?: '-' }}</td>
                                <td>{{ $attraction->name }}</td>
                                <td>{{ Str::limit($attraction->detail ?? '', 60) }}</td>
                                <td>{{ $attraction->sort_order }}</td>
                                <td>{{ $attraction->show_on_homepage ? trans('global.yes') : 'Hidden' }}</td>
                                <td>{{ $attraction->is_active ? 'Published' : 'Unpublished' }}</td>
                            </tr>
                        @empty
                            <tr>
                                @canany(['attraction_delete', 'attraction_edit'])
                                    <td colspan="10">{{ trans('global.no_entries_in_table') }}</td>
                                @else
                                    <td colspan="9">{{ trans('global.no_entries_in_table') }}</td>
                                @endcanany
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $attractions->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const bulkForm = document.getElementById('attractionBulkForm');
            const deleteForm = document.getElementById('attractionDeleteForm');
            const selectAll = document.getElementById('select-all-attractions');
            const statusInput = document.getElementById('attractionBulkStatus');
            const actionButtons = document.querySelectorAll('[data-bulk-status]');
            const rowCheckboxes = Array.from(document.querySelectorAll('.attraction-row-checkbox'));

            if (!bulkForm || rowCheckboxes.length === 0) {
                return;
            }

            function syncDeleteForm() {
                if (!deleteForm) {
                    return;
                }

                deleteForm.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
                    input.remove();
                });

                rowCheckboxes.forEach(function (checkbox) {
                    if (!checkbox.checked) {
                        return;
                    }

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ids[]';
                    hidden.value = checkbox.value;
                    deleteForm.appendChild(hidden);
                });
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    rowCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                    syncDeleteForm();
                });
            }

            rowCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (selectAll) {
                        selectAll.checked = rowCheckboxes.every(function (item) {
                            return item.checked;
                        });
                    }
                    syncDeleteForm();
                });
            });

            actionButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!rowCheckboxes.some(function (checkbox) { return checkbox.checked; })) {
                        window.alert('Please select at least one attraction.');
                        return;
                    }

                    statusInput.value = button.getAttribute('data-bulk-status');
                    bulkForm.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
                        input.remove();
                    });

                    rowCheckboxes.forEach(function (checkbox) {
                        if (!checkbox.checked) {
                            return;
                        }

                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'ids[]';
                        hidden.value = checkbox.value;
                        bulkForm.appendChild(hidden);
                    });

                    bulkForm.submit();
                });
            });
        })();
    </script>
@endsection
