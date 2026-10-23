@extends('layouts.admin')
@section('styles')
    <style>
        .events-filter-form {
            display: grid;
            grid-template-columns:
                minmax(170px, 1.35fr)
                minmax(140px, 1fr)
                minmax(180px, 1fr)
                minmax(170px, 1fr)
                minmax(145px, .9fr)
                auto;
            gap: 8px;
            align-items: center;
        }

        .events-filter-form .form-control {
            min-width: 0;
            width: 100%;
            background: #fff;
        }

        .events-filter-form .filter-actions {
            justify-content: flex-start;
            flex-wrap: nowrap;
            align-self: center;
            white-space: nowrap;
        }

        @media (max-width: 1199.98px) {
            .events-filter-form {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .events-filter-form {
                grid-template-columns: 1fr;
            }

            .events-filter-form .filter-actions {
                flex-wrap: wrap;
            }
        }
    </style>
@endsection

@section('content')
    @php($eventsIndexRoute = request()->routeIs('admin.past-upcoming-events.index') ? 'admin.past-upcoming-events.index' : 'admin.events.index')
    <div class="row page-actions mb-2">
        <div class="col-lg-12 d-flex align-items-center gap-2 flex-wrap">
            @can('event_create')
                <a class="btn btn-primary" href="{{ route('admin.events.create') }}">{{ trans('global.add') }} {{ trans('cruds.event.title_singular') }}</a>
            @endcan
            <a class="btn btn-outline-success" href="{{ route('admin.events.export', array_merge(request()->query(), ['format' => 'csv'])) }}">Export CSV</a>
            <a class="btn btn-outline-success" href="{{ route('admin.events.export', array_merge(request()->query(), ['format' => 'excel'])) }}">Export Excel</a>
            @can('event_edit')
                <button class="btn btn-success" type="button" data-bulk-status="publish">Publish Selected</button>
                <button class="btn btn-warning" type="button" data-bulk-status="unpublish">Unpublish Selected</button>
            @endcan
            @can('event_delete')
                <button class="btn btn-danger" type="submit" form="eventDeleteForm" onclick="return confirm('{{ trans('global.areYouSure') }}');">{{ trans('global.delete') }}</button>
            @endcan
            <a class="btn btn-secondary" href="{{ route('admin.events.page-settings.edit') }}">Page Settings</a>
        </div>
    </div>
    <div class="card">
        <div class="card-header">{{ trans('cruds.event.title') }} {{ trans('global.list') }}</div>
        <div class="card-body">
            <form method="GET" action="{{ route($eventsIndexRoute) }}" class="events-filter-form mb-3">
                <div>
                    <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="{{ trans('global.search') }}">
                </div>
                <div>
                    <input class="form-control" type="text" name="location" value="{{ request('location') }}" placeholder="Location">
                </div>
                <div>
                    <select class="form-control" name="is_published">
                        <option value="" @selected(request('is_published') === null)>Published/Unpublished</option>
                        <option value="1" @selected(request('is_published') === '1')>Published</option>
                        <option value="0" @selected(request('is_published') === '0')>Unpublished</option>
                    </select>
                </div>
                <div>
                    <select class="form-control" name="sort_by">
                        <option value="id" @selected(request('sort_by', 'id') === 'id')>{{ trans('cruds.event.fields.id') }}</option>
                        <option value="sort_order" @selected(request('sort_by') === 'sort_order')>{{ trans('cruds.event.fields.sort_order') }}</option>
                        <option value="title" @selected(request('sort_by') === 'title')>{{ trans('cruds.event.fields.title') }}</option>
                        <option value="location" @selected(request('sort_by') === 'location')>{{ trans('cruds.event.fields.location') }}</option>
                        <option value="start_at" @selected(request('sort_by') === 'start_at')>{{ trans('cruds.event.fields.start_at') }}</option>
                    </select>
                </div>
                <div>
                    <select class="form-control" name="sort_dir">
                        <option value="desc" @selected(request('sort_dir', 'desc') === 'desc')>Descending</option>
                        <option value="asc" @selected(request('sort_dir') === 'asc')>Ascending</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route($eventsIndexRoute) }}">{{ trans('global.clear') }}</a>
                </div>
            </form>
            @canany(['event_edit', 'event_delete'])
                <form id="eventBulkForm" method="POST" action="{{ route('admin.events.bulkStatus') }}" class="d-none">
                    @csrf
                    <input type="hidden" name="status" id="eventBulkStatus" value="">
                </form>
            @endcanany
            @can('event_delete')
                <form id="eventDeleteForm" method="POST" action="{{ route('admin.events.massDestroy') }}" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endcan
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('global.actions') }}</th>
                            @canany(['event_edit', 'event_delete'])
                                <th width="10">
                                    <input type="checkbox" id="select-all-events" aria-label="Select all events">
                                </th>
                            @endcanany
                            <th>{{ trans('cruds.event.fields.title') }}</th>
                            <th>{{ trans('cruds.event.fields.slug') }}</th>
                            <th>{{ trans('cruds.event.fields.venue') }}</th>
                            <th>{{ trans('cruds.event.fields.location') }}</th>
                            <th>{{ trans('cruds.event.fields.start_at') }}</th>
                            <th>{{ trans('cruds.event.fields.end_at') }}</th>
                            <th>{{ trans('cruds.event.fields.is_published') }}</th>
                            <th>Buy Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>@include('partials.datatablesActions', ['viewGate' => 'event_show', 'editGate' => 'event_edit', 'deleteGate' => 'event_delete', 'crudRoutePart' => 'events', 'row' => $event])</td>
                                @canany(['event_edit', 'event_delete'])<td><input type="checkbox" name="ids[]" value="{{ $event->id }}" class="event-row-checkbox" form="eventBulkForm"></td>@endcanany
                                <td>{{ $event->title }}</td>
                                <td>
                                    <a href="{{ route('frontend.events.show', ['slug' => $event->slug]) }}" target="_blank" rel="noopener">
                                        {{ $event->slug }}
                                    </a>
                                </td>
                                <td>{{ $event->venue }}</td>
                                <td>{{ $event->location ?: '-' }}</td>
                                <td>{{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('d-M-Y') : '' }}</td>
                                <td>{{ $event->end_at ? \Carbon\Carbon::parse($event->end_at)->format('d-M-Y') : '' }}</td>
                                <td>{{ $event->is_published ? trans('global.yes') : trans('global.no') }}</td>
                                <td>
                                    @if($event->isBookableUpcoming() && (int) ($event->active_ticket_plans_count ?? 0) > 0)
                                        <a href="{{ route('frontend.events.tickets', ['event' => $event->slug]) }}" class="btn btn-xs btn-primary" target="_blank" rel="noopener">Open</a>
                                    @else
                                        {{ trans('global.no') }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                @canany(['event_edit', 'event_delete'])<td colspan="11">{{ trans('global.no_entries_in_table') }}</td>@else<td colspan="10">{{ trans('global.no_entries_in_table') }}</td>@endcanany
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $events->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const bulkForm = document.getElementById('eventBulkForm');
            const deleteForm = document.getElementById('eventDeleteForm');
            const selectAll = document.getElementById('select-all-events');
            const statusInput = document.getElementById('eventBulkStatus');
            const actionButtons = document.querySelectorAll('[data-bulk-status]');
            const rowCheckboxes = Array.from(document.querySelectorAll('.event-row-checkbox'));

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
                        window.alert('Please select at least one event.');
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

            syncDeleteForm();
        })();
    </script>
@endsection
