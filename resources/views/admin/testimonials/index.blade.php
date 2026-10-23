@extends('layouts.admin')
@section('styles')
    @parent
    <style>
        .testimonial-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .testimonial-toolbar__actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .testimonial-filter-row {
            display: grid;
            grid-template-columns: 1fr auto;
            grid-template-areas:
                "filters actions"
                "sorting actions";
            gap: 12px;
            align-items: end;
        }

        .testimonial-filter-row > .filter-panel {
            min-width: 0;
        }

        .testimonial-filter-row > .filter-panel:first-child {
            grid-area: filters;
        }

        .testimonial-filter-row > .filter-panel:nth-child(2) {
            grid-area: sorting;
        }

        .testimonial-filter-row .filter-panel__grid {
            display: grid;
            gap: 12px;
            margin: 0;
        }

        .testimonial-filter-row .filter-panel__grid > [class*='col-'] {
            min-width: 0;
            width: auto;
            max-width: none;
            flex: none;
            margin: 0;
            padding: 0;
        }

        .testimonial-filter-row > .filter-panel:first-child .filter-panel__grid {
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }

        .testimonial-filter-row > .filter-panel:nth-child(2) .filter-panel__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .testimonial-filter-row .filter-panel__grid .form-control {
            width: 100%;
        }

        .testimonial-filter-row .filter-panel__grid select.form-control {
            padding-right: 2.25rem;
            text-overflow: ellipsis;
        }

        .testimonial-filter-row .filter-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            white-space: nowrap;
            align-self: center;
            margin: 0;
            grid-area: actions;
        }

        @media (max-width: 1199.98px) {
            .testimonial-filter-row {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "filters"
                    "sorting"
                    "actions";
            }

            .testimonial-filter-row > .filter-panel:first-child .filter-panel__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .testimonial-filter-row > .filter-panel:nth-child(2) .filter-panel__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .testimonial-filter-row .filter-actions {
                flex-wrap: wrap;
                white-space: normal;
            }
        }

        @media (max-width: 767.98px) {
            .testimonial-filter-row > .filter-panel:first-child .filter-panel__grid,
            .testimonial-filter-row > .filter-panel:nth-child(2) .filter-panel__grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
@section('content')
    @can('testimonial_create')
        <div class="testimonial-toolbar">
            <div class="testimonial-toolbar__actions" style="justify-content:flex-start;">
                <a class="btn btn-primary" href="{{ route('admin.testimonials.create') }}">
                    {{ trans('global.add') }} {{ trans('cruds.testimonial.title_singular') }}
                </a>
                @can('setting_access')
                    <a class="btn btn-outline-secondary" href="{{ route('admin.testimonials.page-settings.edit') }}">
                        Page Settings
                    </a>
                @endcan
            </div>
            <div class="testimonial-toolbar__actions">
                @can('testimonial_delete')
                    <button class="btn btn-danger" type="submit" form="testimonialDeleteForm" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('testimonial_edit')
                    <button class="btn btn-success" type="submit" form="testimonialBulkForm" data-bulk-status="publish">Publish Selected</button>
                    <button class="btn btn-warning" type="submit" form="testimonialBulkForm" data-bulk-status="unpublish">Unpublish Selected</button>
                @endcan
            </div>
        </div>
    @else
        <div class="testimonial-toolbar">
            <div class="testimonial-toolbar__actions" style="justify-content:flex-start;">
                @can('setting_access')
                    <a class="btn btn-outline-secondary" href="{{ route('admin.testimonials.page-settings.edit') }}">
                        Page Settings
                    </a>
                @endcan
            </div>
            <div class="testimonial-toolbar__actions">
                @can('testimonial_delete')
                    <button class="btn btn-danger" type="submit" form="testimonialDeleteForm" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
                @endcan
                @can('testimonial_edit')
                    <button class="btn btn-success" type="submit" form="testimonialBulkForm" data-bulk-status="publish">Publish Selected</button>
                    <button class="btn btn-warning" type="submit" form="testimonialBulkForm" data-bulk-status="unpublish">Unpublish Selected</button>
                @endcan
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            {{ trans('cruds.testimonial.title') }} {{ trans('global.list') }}
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.testimonials.index') }}" class="filter-form mb-3">
                <div class="testimonial-filter-row">
                    <div class="filter-panel">
                        <div class="filter-panel__title">Filters</div>
                        <div class="form-row filter-panel__grid">
                            <div class="col-md-3 mb-2">
                                <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" name="event_id">
                                    <option value="">{{ trans('global.all') }} {{ trans('cruds.testimonial.fields.event') }}</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" @selected((string) $event->id === (string) request('event_id'))>{{ $event->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" name="country_id">
                                    <option value="">{{ trans('global.all') }} {{ trans('cruds.testimonial.fields.country') }}</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" @selected((string) $country->id === (string) request('country_id'))>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input class="form-control" type="number" min="2000" max="2100" name="year" value="{{ request('year') }}" placeholder="{{ trans('cruds.testimonial.fields.year') }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <input class="form-control" type="date" name="testimonial_date" value="{{ request('testimonial_date') }}" placeholder="{{ trans('cruds.testimonial.fields.testimonial_date') }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <select class="form-control" name="show_on_homepage">
                                    <option value="">{{ trans('global.all') }} {{ trans('cruds.testimonial.fields.show_on_homepage') }}</option>
                                    <option value="1" @selected(request('show_on_homepage') === '1')>{{ trans('global.yes') }}</option>
                                    <option value="0" @selected(request('show_on_homepage') === '0')>{{ trans('global.no') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select class="form-control" name="show_on_scrollbar">
                                    <option value="">{{ trans('global.all') }} {{ trans('cruds.testimonial.fields.show_on_scrollbar') }}</option>
                                    <option value="1" @selected(request('show_on_scrollbar') === '1')>{{ trans('global.yes') }}</option>
                                    <option value="0" @selected(request('show_on_scrollbar') === '0')>{{ trans('global.no') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select class="form-control" name="is_key_figure">
                                    <option value="">{{ trans('global.all') }} {{ trans('cruds.testimonial.fields.is_key_figure') }}</option>
                                    <option value="1" @selected(request('is_key_figure') === '1')>{{ trans('global.yes') }}</option>
                                    <option value="0" @selected(request('is_key_figure') === '0')>{{ trans('global.no') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="filter-panel">
                        <div class="filter-panel__title">Sorting</div>
                        <div class="form-row filter-panel__grid">
                            <div class="col-md-3 mb-2">
                                <select class="form-control" name="sort_by">
                                    <option value="sort_order" @selected(request('sort_by', 'sort_order') === 'sort_order')>{{ trans('cruds.testimonial.fields.sort_order') }}</option>
                                    <option value="name" @selected(request('sort_by') === 'name')>{{ trans('cruds.testimonial.fields.name') }}</option>
                                    <option value="company" @selected(request('sort_by') === 'company')>{{ trans('cruds.testimonial.fields.company') }}</option>
                                    <option value="stars" @selected(request('sort_by') === 'stars')>{{ trans('cruds.testimonial.fields.stars') }}</option>
                                    <option value="is_key_figure" @selected(request('sort_by') === 'is_key_figure')>{{ trans('cruds.testimonial.fields.is_key_figure') }}</option>
                                    <option value="testimonial_date" @selected(request('sort_by') === 'testimonial_date')>{{ trans('cruds.testimonial.fields.testimonial_date') }}</option>
                                    <option value="year" @selected(request('sort_by') === 'year')>{{ trans('cruds.testimonial.fields.year') }}</option>
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
                        <a class="btn btn-outline-secondary" href="{{ route('admin.testimonials.index') }}">{{ trans('global.clear') }}</a>
                    </div>
                </div>
            </form>

            @canany(['testimonial_delete', 'testimonial_edit'])
                <form id="testimonialBulkForm" method="POST" action="{{ route('admin.testimonials.bulkStatus') }}">
                    @csrf
                    <input type="hidden" name="status" id="testimonialBulkStatus" value="">
                </form>
                <form id="testimonialDeleteForm" method="POST" action="{{ route('admin.testimonials.massDestroy') }}">
                    @csrf
                    @method('DELETE')
                </form>
            @endcanany

            <div class="d-md-none">
                @forelse($testimonials as $testimonial)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    @if($testimonial->photo_url)
                                        @php
                                            $thumb = trim((string) $testimonial->photo_url);
                                            $isInvalidThumb = in_array($thumb, ['http://', 'https://', '//'], true);
                                            $thumbSrc = (str_starts_with($thumb, 'http://') || str_starts_with($thumb, 'https://') || str_starts_with($thumb, '//') || str_starts_with($thumb, '/'))
                                                ? ($isInvalidThumb ? null : $thumb)
                                                : asset(ltrim($thumb, '/'));
                                        @endphp
                                        @if($thumbSrc)
                                            <img src="{{ $thumbSrc }}" alt="{{ $testimonial->name }}" class="img-fluid" style="max-width:50px; margin-right: 8px;">
                                        @endif
                                    @endif
                                    <strong>{{ $testimonial->name }}</strong>
                                </div>
                                @canany(['testimonial_delete', 'testimonial_edit'])
                                    <div>
                                        <input type="checkbox" name="ids[]" value="{{ $testimonial->id }}" class="testimonial-row-checkbox" form="testimonialBulkForm">
                                    </div>
                                @endcanany
                            </div>
                            <div>
                                <strong>{{ trans('cruds.testimonial.fields.country') }}:</strong>
                                {{ $testimonial->country?->name ?? '-' }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.testimonial.fields.company') }}:</strong>
                                {{ $testimonial->company ?? '-' }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.testimonial.fields.experience_word') }}:</strong>
                                {{ $testimonial->experience_word ?? '-' }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.testimonial.fields.stars') }}:</strong>
                                {{ $testimonial->stars ?? 5 }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.testimonial.fields.is_key_figure') }}:</strong>
                                {{ $testimonial->is_key_figure ? trans('global.yes') : trans('global.no') }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.testimonial.fields.show_on_homepage') }}:</strong>
                                {{ $testimonial->show_on_homepage ? trans('global.yes') : trans('global.no') }}
                            </div>
                            <div>
                                <strong>{{ trans('cruds.testimonial.fields.show_on_scrollbar') }}:</strong>
                                {{ $testimonial->show_on_scrollbar ? trans('global.yes') : trans('global.no') }}
                            </div>
                            <div>
                                <strong>{{ trans('global.status') }}:</strong>
                                {{ $testimonial->is_active ? 'Published' : 'Unpublished' }}
                            </div>
                            <div class="mt-2">
                                @include('partials.datatablesActions', [
                                    'viewGate' => 'testimonial_show',
                                    'editGate' => 'testimonial_edit',
                                    'deleteGate' => 'testimonial_delete',
                                    'crudRoutePart' => 'testimonials',
                                    'row' => $testimonial,
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
                            @canany(['testimonial_delete', 'testimonial_edit'])
                                <th width="10">
                                    <input type="checkbox" id="select-all-testimonials" aria-label="Select all testimonials">
                                </th>
                            @endcanany
                            <th>{{ trans('cruds.testimonial.fields.id') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.photo_url') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.name') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.country') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.company') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.experience_word') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.stars') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.is_key_figure') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.testimonial_date') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.show_on_homepage') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.show_on_scrollbar') }}</th>
                            <th>{{ trans('global.status') }}</th>
                            <th>{{ trans('cruds.testimonial.fields.sort_order') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($testimonials as $testimonial)
                            <tr>
                                <td>
                                    @include('partials.datatablesActions', [
                                        'viewGate' => 'testimonial_show',
                                        'editGate' => 'testimonial_edit',
                                        'deleteGate' => 'testimonial_delete',
                                        'crudRoutePart' => 'testimonials',
                                        'row' => $testimonial,
                                    ])
                                </td>
                                @canany(['testimonial_delete', 'testimonial_edit'])
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $testimonial->id }}" class="testimonial-row-checkbox" form="testimonialBulkForm">
                                    </td>
                                @endcanany
                                <td>{{ $testimonial->id }}</td>
                                <td>
                                    @if($testimonial->photo_url)
                                        @php
                                            $thumb = trim((string) $testimonial->photo_url);
                                            $isInvalidThumb = in_array($thumb, ['http://', 'https://', '//'], true);
                                            $thumbSrc = (str_starts_with($thumb, 'http://') || str_starts_with($thumb, 'https://') || str_starts_with($thumb, '//') || str_starts_with($thumb, '/'))
                                                ? ($isInvalidThumb ? null : $thumb)
                                                : asset(ltrim($thumb, '/'));
                                        @endphp
                                        @if($thumbSrc)
                                            <img src="{{ $thumbSrc }}" alt="{{ $testimonial->name }}" class="img-fluid" style="max-width:70px;">
                                        @else
                                            <span class="text-muted small">No image</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">No image</span>
                                    @endif
                                </td>
                                <td>{{ $testimonial->name ?? '' }}</td>
                                <td>{{ $testimonial->country?->name ?? '' }}</td>
                                <td>{{ $testimonial->company ?? '' }}</td>
                                <td>{{ $testimonial->experience_word ?? '' }}</td>
                                <td>{{ $testimonial->stars ?? 5 }}</td>
                                <td>{{ $testimonial->is_key_figure ? trans('global.yes') : trans('global.no') }}</td>
                                <td>{{ optional($testimonial->testimonial_date)->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $testimonial->show_on_homepage ? 'Visible' : 'Hidden' }}</td>
                                <td>{{ $testimonial->show_on_scrollbar ? trans('global.yes') : trans('global.no') }}</td>
                                <td>{{ $testimonial->is_active ? 'Published' : 'Unpublished' }}</td>
                                <td>{{ $testimonial->sort_order }}</td>
                            </tr>
                        @empty
                            <tr>
                                @canany(['testimonial_delete', 'testimonial_edit'])
                                    <td colspan="15">{{ trans('global.no_entries_in_table') }}</td>
                                @else
                                    <td colspan="14">{{ trans('global.no_entries_in_table') }}</td>
                                @endcanany
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $testimonials->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const bulkForm = document.getElementById('testimonialBulkForm');
            const deleteForm = document.getElementById('testimonialDeleteForm');
            const selectAll = document.getElementById('select-all-testimonials');
            const statusInput = document.getElementById('testimonialBulkStatus');
            const actionButtons = document.querySelectorAll('[data-bulk-status]');
            const rowCheckboxes = Array.from(document.querySelectorAll('.testimonial-row-checkbox'));

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
                        window.alert('Please select at least one testimonial.');
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
