@extends('layouts.admin')

@section('styles')
    @parent
    <style>
        .world-events-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .world-events-toolbar__actions,
        .world-events-filter-row,
        .world-events-bulk-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .world-events-toolbar__search {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1 1 360px;
            max-width: 430px;
            margin-left: auto;
        }
        .world-events-toolbar__search .form-control {
            min-width: 220px;
        }
        .world-events-filter-row {
            width: 100%;
            flex-wrap: wrap;
        }
        .world-events-filter-row .form-group {
            margin-bottom: 0;
            min-width: 0;
        }
        .world-events-filter-row .filter-country,
        .world-events-filter-row .filter-status,
        .world-events-filter-row .filter-sort,
        .world-events-filter-row .filter-direction {
            flex: 1 1 220px;
        }
        .world-events-filter-row .filter-actions {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        .world-events-checkbox-col,
        .world-events-checkbox-cell {
            width: 48px;
            text-align: center;
        }
        .world-events-checkbox-col input,
        .world-events-checkbox-cell input {
            width: 16px;
            height: 16px;
        }
        @media (max-width: 991px) {
            .world-events-toolbar__search {
                max-width: none;
                width: 100%;
                margin-left: 0;
            }
            .world-events-filter-row {
                flex-wrap: wrap;
            }
            .world-events-filter-row .filter-country,
            .world-events-filter-row .filter-status,
            .world-events-filter-row .filter-sort,
            .world-events-filter-row .filter-direction,
            .world-events-filter-row .filter-actions {
                flex: 1 1 100%;
            }
            .world-events-filter-row .filter-actions {
                justify-content: flex-start;
            }
        }
    </style>
@endsection

@section('content')
<div class="world-events-toolbar">
    <div class="world-events-toolbar__actions">
        <a class="btn btn-primary" href="{{ route('admin.world-events.create') }}">Add World Event</a>
        <a class="btn btn-info" href="{{ route('admin.world-events.page-settings.edit') }}">Page Settings</a>
    </div>
    <form method="GET" action="{{ route('admin.world-events.index') }}" class="world-events-toolbar__search">
        @if(request()->filled('event_name'))
            <input type="hidden" name="event_name" value="{{ request('event_name') }}">
        @endif
        @if(request()->filled('country'))
            <input type="hidden" name="country" value="{{ request('country') }}">
        @endif
        @if(request()->filled('year'))
            <input type="hidden" name="year" value="{{ request('year') }}">
        @endif
        @if(request()->filled('is_active'))
            <input type="hidden" name="is_active" value="{{ request('is_active') }}">
        @endif
        @if(request()->filled('sort_by'))
            <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
        @endif
        @if(request()->filled('sort_dir'))
            <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
        @endif
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search world events">
        <button class="btn btn-primary" type="submit">Search</button>
    </form>
    <div class="world-events-toolbar__actions world-events-bulk-actions">
        <a class="btn btn-outline-success" href="{{ route('admin.world-events.export', request()->query()) }}">Export CSV</a>
        <button type="button" class="btn btn-outline-primary" data-world-event-export-selected>Export Selected</button>
        <button type="button" class="btn btn-success" data-world-event-status="publish">Publish Selected</button>
        <button type="button" class="btn btn-warning" data-world-event-status="unpublish">Unpublish Selected</button>
        <button type="submit" class="btn btn-danger" form="worldEventDeleteForm" onclick="return confirm('Delete selected records?');">Delete Selected</button>
    </div>
</div>

<div class="card">
    <div class="card-header">World Events</div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.world-events.index') }}" class="filter-form mb-3">
            <div class="world-events-filter-row">
                <div class="form-group filter-country">
                    <select class="form-control" name="event_name">
                        <option value="">All Events</option>
                        @foreach($eventNames as $eventName)
                            <option value="{{ $eventName }}" @selected(request('event_name') === $eventName)>{{ $eventName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group filter-country">
                    <select class="form-control" name="country">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" @selected(request('country') === $country)>{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group filter-status">
                    <select class="form-control" name="year">
                        <option value="">All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group filter-status">
                    <select class="form-control" name="is_active">
                        <option value="">All Status</option>
                        <option value="1" @selected(request('is_active') === '1')>Published</option>
                        <option value="0" @selected(request('is_active') === '0')>Unpublished</option>
                    </select>
                </div>
                <div class="form-group filter-sort">
                    <select class="form-control" name="sort_by">
                        <option value="id" @selected(request('sort_by', 'id') === 'id')>ID</option>
                        <option value="year" @selected(request('sort_by') === 'year')>Year</option>
                        <option value="start_date" @selected(request('sort_by') === 'start_date')>Start Date</option>
                        <option value="end_date" @selected(request('sort_by') === 'end_date')>End Date</option>
                        <option value="name" @selected(request('sort_by') === 'name')>Name</option>
                        <option value="city" @selected(request('sort_by') === 'city')>City</option>
                        <option value="country" @selected(request('sort_by') === 'country')>Country</option>
                        <option value="theme" @selected(request('sort_by') === 'theme')>Theme</option>
                        <option value="stars" @selected(request('sort_by') === 'stars')>Stars</option>
                        <option value="is_active" @selected(request('sort_by') === 'is_active')>Status</option>
                    </select>
                </div>
                <div class="form-group filter-direction">
                    <select class="form-control" name="sort_dir">
                        <option value="desc" @selected(request('sort_dir', 'desc') === 'desc')>Desc</option>
                        <option value="asc" @selected(request('sort_dir', 'asc') === 'asc')>Asc</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">Apply</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.world-events.index') }}">Clear</a>
                </div>
            </div>
        </form>

        <form id="worldEventBulkForm" method="POST" action="{{ route('admin.world-events.bulkStatus') }}">
            @csrf
            <input type="hidden" name="status" id="worldEventBulkStatusAction" value="">
        </form>
        <form id="worldEventDeleteForm" method="POST" action="{{ route('admin.world-events.massDestroy') }}">
            @csrf
            @method('DELETE')
        </form>
        <form id="worldEventExportSelectedForm" method="GET" action="{{ route('admin.world-events.export') }}"></form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th>Actions</th>
                    <th class="world-events-checkbox-col">
                        <input type="checkbox" id="select-all-world-events" aria-label="Select all world events">
                    </th>
                    <th>ID</th>
                    <th>Name of Event</th>
                    <th>Year</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Theme</th>
                    <th>Stars</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($worldEvents as $worldEvent)
                    <tr>
                        <td>
                            <a class="btn btn-xs btn-primary" href="{{ route('admin.world-events.show', $worldEvent) }}">View</a>
                            <a class="btn btn-xs btn-info" href="{{ route('admin.world-events.edit', $worldEvent) }}">Edit</a>
                            <form action="{{ route('admin.world-events.bulkStatus') }}" method="POST" class="d-inline-block">
                                @csrf
                                <input type="hidden" name="ids[]" value="{{ $worldEvent->id }}">
                                <input type="hidden" name="status" value="{{ $worldEvent->is_active ? 'unpublish' : 'publish' }}">
                                <button class="btn btn-xs btn-secondary" type="submit">{{ $worldEvent->is_active ? 'Unpublish' : 'Publish' }}</button>
                            </form>
                            <form action="{{ route('admin.world-events.destroy', $worldEvent) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this record?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                        <td class="world-events-checkbox-cell">
                            <input type="checkbox" name="ids[]" value="{{ $worldEvent->id }}" class="world-event-row-checkbox" form="worldEventBulkForm">
                        </td>
                        <td>{{ $worldEvent->id }}</td>
                        <td>{{ $worldEvent->name }}</td>
                        <td>{{ optional($worldEvent->start_date)->format('Y') ?: '-' }}</td>
                        <td>{{ optional($worldEvent->start_date)->format('d-M-Y') }}</td>
                        <td>{{ optional($worldEvent->end_date)->format('d-M-Y') }}</td>
                        <td>{{ $worldEvent->days ?: '-' }}</td>
                        <td>{{ $worldEvent->city ?: '-' }}</td>
                        <td>{{ $worldEvent->country ?: '-' }}</td>
                        <td>{{ $worldEvent->theme ?: '-' }}</td>
                        <td>{{ $worldEvent->stars }}</td>
                        <td>{{ $worldEvent->is_active ? 'Published' : 'Unpublished' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="13">{{ trans('global.no_entries_in_table') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $worldEvents->links() }}
    </div>
</div>
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select-all-world-events');
            const rowCheckboxes = Array.from(document.querySelectorAll('.world-event-row-checkbox'));
            const bulkForm = document.getElementById('worldEventBulkForm');
            const deleteForm = document.getElementById('worldEventDeleteForm');
            const exportSelectedForm = document.getElementById('worldEventExportSelectedForm');
            const bulkStatusInput = document.getElementById('worldEventBulkStatusAction');
            const bulkStatusButtons = Array.from(document.querySelectorAll('[data-world-event-status]'));
            const exportSelectedButton = document.querySelector('[data-world-event-export-selected]');

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

            function syncExportForm() {
                if (!exportSelectedForm) {
                    return;
                }

                exportSelectedForm.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
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
                    exportSelectedForm.appendChild(hidden);
                });
            }

            function syncSelectAll() {
                if (!selectAll) {
                    return;
                }

                selectAll.checked = rowCheckboxes.length > 0 && rowCheckboxes.every(function (checkbox) {
                    return checkbox.checked;
                });
            }

            function selectedCount() {
                return rowCheckboxes.filter(function (checkbox) {
                    return checkbox.checked;
                }).length;
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    rowCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });

                    syncDeleteForm();
                    syncExportForm();
                });
            }

            rowCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    syncSelectAll();
                    syncDeleteForm();
                    syncExportForm();
                });
            });

            bulkStatusButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (selectedCount() === 0) {
                        alert('Select at least one world event first.');
                        return;
                    }

                    bulkStatusInput.value = button.getAttribute('data-world-event-status');
                    bulkForm.submit();
                });
            });

            if (exportSelectedButton && exportSelectedForm) {
                exportSelectedButton.addEventListener('click', function () {
                    if (selectedCount() === 0) {
                        alert('Select at least one world event first.');
                        return;
                    }

                    syncExportForm();
                    exportSelectedForm.submit();
                });
            }

            if (deleteForm) {
                deleteForm.addEventListener('submit', function (event) {
                    if (selectedCount() === 0) {
                        event.preventDefault();
                        alert('Select at least one world event first.');
                    }
                });
            }

            syncSelectAll();
            syncDeleteForm();
            syncExportForm();
        });
    </script>
@endsection
