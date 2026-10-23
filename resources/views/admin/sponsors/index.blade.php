@extends('layouts.admin')

@section('styles')
    @parent
    <style>
        .sponsor-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .sponsor-toolbar__actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .sponsor-admin-filters {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 420px) auto;
            gap: 12px;
            margin-bottom: 12px;
            align-items: end;
        }
        .sponsor-admin-filters .filter-panel {
            align-items: stretch;
            padding: 14px 16px;
            min-width: 0;
        }
        .sponsor-admin-filters .filter-panel__title {
            min-width: auto;
            margin-bottom: 8px;
        }
        .sponsor-admin-filters .filter-panel:first-child .form-row {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
        .sponsor-admin-filters .filter-panel:nth-child(2) .form-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .sponsor-admin-filters .filter-panel__grid .form-group,
        .sponsor-admin-filters .filter-panel__grid .mb-2 {
            margin-bottom: 0 !important;
        }
        .sponsor-filter-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            flex-wrap: nowrap;
            white-space: nowrap;
            align-self: center;
        }
        .sponsor-checkbox-col,
        .sponsor-checkbox-cell {
            width: 10px;
            text-align: center;
        }
        .sponsor-checkbox-cell input,
        .sponsor-checkbox-col input {
            width: 16px;
            height: 16px;
        }
        .sponsor-mobile-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }
        .sponsor-mobile-meta {
            display: grid;
            gap: 6px;
        }
        @media (max-width: 991px) {
            .sponsor-admin-filters {
                grid-template-columns: 1fr;
            }
            .sponsor-filter-actions {
                justify-content: center;
                flex-wrap: wrap;
                white-space: normal;
            }
            .sponsor-admin-filters .filter-panel:first-child .form-row,
            .sponsor-admin-filters .filter-panel:nth-child(2) .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')

    @can('sponsor_create')
        <div class="sponsor-toolbar">
            <a class="btn btn-primary" href="{{ route('admin.sponsors.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.sponsor.title_singular') }}
            </a>
            <div class="sponsor-toolbar__actions">
                @can('sponsor_edit')
                    <button class="btn btn-outline-success" type="submit" form="sponsorSortForm">Save Order</button>
                @endcan
                <a class="btn btn-outline-info" href="{{ route('admin.sponsors.export', request()->query()) }}">Export</a>
                @can('sponsor_delete')
                    <button class="btn btn-danger" type="submit" form="sponsorDeleteForm" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('sponsor_edit')
                    <button type="button" class="btn btn-success" data-bulk-status="publish">Publish Selected</button>
                    <button type="button" class="btn btn-warning" data-bulk-status="unpublish">Unpublish Selected</button>
                @endcan
            </div>
        </div>
    @else
        <div class="sponsor-toolbar">
            <div></div>
            <div class="sponsor-toolbar__actions">
                @can('sponsor_edit')
                    <button class="btn btn-outline-success" type="submit" form="sponsorSortForm">Save Order</button>
                @endcan
                <a class="btn btn-outline-info" href="{{ route('admin.sponsors.export', request()->query()) }}">Export</a>
                @can('sponsor_delete')
                    <button class="btn btn-danger" type="submit" form="sponsorDeleteForm" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('sponsor_edit')
                    <button type="button" class="btn btn-success" data-bulk-status="publish">Publish Selected</button>
                    <button type="button" class="btn btn-warning" data-bulk-status="unpublish">Unpublish Selected</button>
                @endcan
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            {{ trans('cruds.sponsor.title') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            @if (session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.sponsors.index') }}" class="filter-form mb-3">
                <div class="sponsor-admin-filters">
                    <div class="filter-panel">
                        <div class="filter-panel__title">Filters</div>
                        <div class="form-row filter-panel__grid">
                            <div class="col-md-4 form-group">
                                <input class="form-control" name="search" type="text" value="{{ request('search') }}" placeholder="Search name, short name, website, category">
                            </div>
                            <div class="col-md-4 form-group">
                                <select class="form-control" name="sponsor_category_id">
                                    <option value="">{{ trans('global.all') }} {{ trans('cruds.sponsor.fields.sponsor_category') }}</option>
                                    @foreach($sponsorCategories as $sponsorCategory)
                                        <option value="{{ $sponsorCategory->id }}" @selected((string) $sponsorCategory->id === (string) request('sponsor_category_id'))>
                                            {{ $sponsorCategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 form-group">
                                <select class="form-control" name="is_active">
                                    <option value="">{{ trans('global.all') }} {{ trans('global.status') }}</option>
                                    <option value="1" @selected(request('is_active') === '1')>Published</option>
                                    <option value="0" @selected(request('is_active') === '0')>Unpublished</option>
                                </select>
                            </div>
                            <div class="col-md-2 form-group">
                                <select class="form-control" name="show_on_homepage">
                                    <option value="">{{ trans('global.all') }} {{ trans('cruds.sponsor.fields.show_on_homepage') }}</option>
                                    <option value="1" @selected(request('show_on_homepage') === '1')>Visible</option>
                                    <option value="0" @selected(request('show_on_homepage') === '0')>Hidden</option>
                                </select>
                            </div>
                            <div class="col-md-2 form-group">
                                <select class="form-control" name="show_on_scrollbar">
                                    <option value="">{{ trans('global.all') }} {{ trans('cruds.sponsor.fields.show_on_scrollbar') }}</option>
                                    <option value="1" @selected(request('show_on_scrollbar') === '1')>{{ trans('global.yes') }}</option>
                                    <option value="0" @selected(request('show_on_scrollbar') === '0')>{{ trans('global.no') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="filter-panel">
                        <div class="filter-panel__title">Sorting</div>
                        <div class="form-row filter-panel__grid">
                            <div class="col-md-8 form-group">
                                <select class="form-control" name="sort_by">
                                    <option value="sort_order" @selected(request('sort_by', 'sort_order') === 'sort_order')>{{ trans('cruds.sponsor.fields.sort_order') }}</option>
                                    <option value="name" @selected(request('sort_by') === 'name')>{{ trans('cruds.sponsor.fields.name') }}</option>
                                    <option value="id" @selected(request('sort_by') === 'id')>{{ trans('cruds.sponsor.fields.id') }}</option>
                                    <option value="website_url" @selected(request('sort_by') === 'website_url')>{{ trans('cruds.sponsor.fields.website_url') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <select class="form-control" name="sort_dir">
                                    <option value="asc" @selected(request('sort_dir', 'asc') === 'asc')>Ascending</option>
                                    <option value="desc" @selected(request('sort_dir') === 'desc')>Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sponsor-filter-actions">
                    <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.sponsors.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </form>

            @canany(['sponsor_edit', 'sponsor_delete'])
                <form method="POST" action="{{ route('admin.sponsors.bulkStatus') }}" id="sponsorBulkForm">
                    @csrf
                    <input type="hidden" name="status" id="bulkStatusAction" value="">
                </form>
                <form method="POST" action="{{ route('admin.sponsors.reorder') }}" id="sponsorSortForm">
                    @csrf
                </form>
                <form method="POST" action="{{ route('admin.sponsors.massDestroy') }}" id="sponsorDeleteForm">
                    @csrf
                    @method('DELETE')
                </form>
            @endcanany

            <div class="d-md-none">
                @forelse($sponsors as $sponsor)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="sponsor-mobile-summary">
                                <div class="sponsor-mobile-meta">
                                    <div>
                                        <strong>{{ trans('cruds.sponsor.fields.name') }}:</strong>
                                        {{ $sponsor->name ?? '' }}
                                    </div>
                                    <div>
                                        <strong>Short Name:</strong>
                                        {{ $sponsor->sub_category ?: '-' }}
                                    </div>
                                </div>
                                @canany(['sponsor_edit', 'sponsor_delete'])
                                    <div>
                                        <input type="checkbox" name="ids[]" value="{{ $sponsor->id }}" form="sponsorBulkForm" class="sponsor-row-checkbox">
                                    </div>
                                @endcanany
                            </div>
                            <div>
                                <strong>Categories:</strong>
                                {{ $sponsor->sponsorCategories->pluck('name')->implode(', ') ?: '-' }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.sponsor.fields.sort_order') }}:</strong>
                                <input
                                    type="number"
                                    name="sort_orders[{{ $sponsor->id }}]"
                                    value="{{ old('sort_orders.' . $sponsor->id, (int) ($sponsor->sort_order ?? 0)) }}"
                                    min="0"
                                    class="form-control form-control-sm d-inline-block"
                                    style="max-width: 120px;"
                                    form="sponsorSortForm"
                                >
                            </div>
                            <div>
                                <strong>{{ trans('cruds.sponsor.fields.show_on_homepage') }}:</strong>
                                {{ $sponsor->show_on_homepage ? trans('global.yes') : trans('global.no') }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.sponsor.fields.show_on_scrollbar') }}:</strong>
                                {{ $sponsor->show_on_scrollbar ? trans('global.yes') : trans('global.no') }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.sponsor.fields.is_active') }}:</strong>
                                {{ $sponsor->is_active ? 'Published' : 'Unpublished' }}
                            </div>
                            <div class="mt-2">
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'sponsor_show',
                                    'editGate' => 'sponsor_edit',
                                    'deleteGate' => 'sponsor_delete',
                                    'crudRoutePart' => 'sponsors',
                                    'row' => $sponsor,
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
                            @canany(['sponsor_edit', 'sponsor_delete'])
                                <th class="sponsor-checkbox-col">
                                    <input type="checkbox" id="sponsorSelectAll" aria-label="Select all sponsors">
                                </th>
                            @endcanany
                            <th>Sponsor Name</th>
                            <th>Logo</th>
                            <th>Categories</th>
                            <th>Sub Category</th>
                            <th>Home Page – Yes No</th>
                            <th>Scroll Bar – Yes No</th>
                            <th>{{ trans('global.status') }}</th>
                            <th>Contact Person Email ID</th>
                            <th>Sort Order</th>
                            <th>Website</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sponsors as $sponsor)
                            @php
                                $logo = trim((string) ($sponsor->logo_url ?? ''));
                                $isAbsoluteLogo = str_starts_with($logo, 'http://')
                                    || str_starts_with($logo, 'https://')
                                    || str_starts_with($logo, '//')
                                    || str_starts_with($logo, '/');
                                $logoSrc = $logo === '' ? '' : ($isAbsoluteLogo ? $logo : asset(ltrim($logo, '/')));
                            @endphp
                            <tr>
                                <td>
                                    @include('partials.datatablesActions', [
                                        'viewGate' => 'sponsor_show',
                                        'editGate' => 'sponsor_edit',
                                        'deleteGate' => 'sponsor_delete',
                                        'crudRoutePart' => 'sponsors',
                                        'row' => $sponsor,
                                    ])
                                </td>
                                @canany(['sponsor_edit', 'sponsor_delete'])
                                    <td class="sponsor-checkbox-cell">
                                        <input type="checkbox" name="ids[]" value="{{ $sponsor->id }}" form="sponsorBulkForm" class="sponsor-row-checkbox">
                                    </td>
                                @endcanany
                                <td>{{ $sponsor->name ?? '' }}</td>
                                <td>
                                    @if($logoSrc !== '')
                                        <img src="{{ $logoSrc }}" alt="{{ $sponsor->name }}" class="img-fluid" style="max-width:110px;">
                                    @else
                                        <span class="text-muted small">No logo</span>
                                    @endif
                                </td>
                                <td>{{ $sponsor->sponsorCategories->pluck('name')->implode(', ') ?: '-' }}</td>
                                <td>{{ $sponsor->sub_category ?: '-' }}</td>
                                <td>{{ $sponsor->show_on_homepage ? 'Yes' : 'No' }}</td>
                                <td>{{ $sponsor->show_on_scrollbar ? trans('global.yes') : trans('global.no') }}</td>
                                <td>{{ $sponsor->is_active ? 'Published' : 'Unpublished' }}</td>
                                <td>{{ $sponsor->contact_email ?: '-' }}</td>
                                <td>{{ $sponsor->sort_order ?? '-' }}</td>
                                <td>
                                    @if($sponsor->website_url)
                                        <a href="{{ $sponsor->website_url }}" target="_blank" rel="noopener noreferrer" title="{{ $sponsor->website_url }}" style="font-size:1.3rem;color:#4a5568;">
                                            <i class="fa fa-globe"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                @canany(['sponsor_edit', 'sponsor_delete'])
                                    <td colspan="12">{{ trans('global.no_entries_in_table') }}</td>
                                @else
                                    <td colspan="11">{{ trans('global.no_entries_in_table') }}</td>
                                @endcanany
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sponsors->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        (function () {
            const bulkForm = document.getElementById('sponsorBulkForm');
            if (!bulkForm) {
                return;
            }

            const deleteForm = document.getElementById('sponsorDeleteForm');
            const statusInput = document.getElementById('bulkStatusAction');
            const actionButtons = document.querySelectorAll('[data-bulk-status]');
            const selectAll = document.getElementById('sponsorSelectAll');

            function getRowCheckboxes() {
                return Array.from(document.querySelectorAll('.sponsor-row-checkbox'));
            }

            function syncDeleteForm() {
                if (!deleteForm) {
                    return;
                }

                deleteForm.querySelectorAll('input[name="ids[]"]').forEach((input) => input.remove());

                getRowCheckboxes().forEach((checkbox) => {
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

            function hasCheckedIds() {
                return getRowCheckboxes().some((input) => input.checked);
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    getRowCheckboxes().forEach((input) => {
                        input.checked = this.checked;
                    });
                    syncDeleteForm();
                });

                getRowCheckboxes().forEach((input) => {
                    input.addEventListener('change', function () {
                        const rowCheckboxes = getRowCheckboxes();
                        selectAll.checked = rowCheckboxes.length > 0 && rowCheckboxes.every((checkbox) => checkbox.checked);
                        syncDeleteForm();
                    });
                });
            }

            actionButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    if (!hasCheckedIds()) {
                        window.alert('Please select at least one sponsor.');
                        return;
                    }

                    statusInput.value = this.getAttribute('data-bulk-status');
                    bulkForm.submit();
                });
            });
        })();
    </script>
@endsection
