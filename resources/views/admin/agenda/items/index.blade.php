@extends('layouts.admin')
@section('content')
<div class="row page-actions">
    <div class="col-lg-12 d-flex flex-wrap align-items-center" style="gap:8px;">
        <a class="btn btn-primary" href="{{ route('admin.agenda.items.create') }}">Add Agenda Session</a>
        <button class="btn btn-success" type="submit" form="bulk-items-publish-form">Publish</button>
        <button class="btn btn-warning" type="submit" form="bulk-items-unpublish-form">Unpublish</button>
        <button class="btn btn-danger" type="submit" form="bulk-items-delete-form" onclick="return confirm('{{ trans('global.areYouSure') }}');">Delete</button>
        <button class="btn btn-outline-success" type="submit" form="bulk-items-export-form">Export CSV</button>
    </div>
</div>

<div class="card">
    <div class="card-header">Agenda Sessions</div>
    <div class="card-body">
        @if(session('message'))
            <div class="alert alert-info border">{{ session('message') }}</div>
        @endif
        <form method="GET" class="mb-3">
            <div class="form-row">
                <div class="col-md-3">
                    <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="Search speaker, company, session...">
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="event_id">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="agenda_day_id">
                        <option value="">All Days</option>
                        @foreach($days as $day)
                            <option value="{{ $day->id }}" @selected((string) request('agenda_day_id') === (string) $day->id)>{{ $day->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="agenda_room_id">
                        <option value="">All Rooms</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected((string) request('agenda_room_id') === (string) $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input class="form-control" type="text" name="time" value="{{ request('time') }}" placeholder="Time">
                </div>
                <div class="col-md-2 mt-2 mt-md-0 pt-md-1">
                    <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.agenda.items.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </div>
        </form>

        <form id="bulk-items-publish-form" method="POST" action="{{ route('admin.agenda.items.bulkPublish') }}">
            @csrf
        </form>
        <form id="bulk-items-unpublish-form" method="POST" action="{{ route('admin.agenda.items.bulkUnpublish') }}">
            @csrf
        </form>
        <form id="bulk-items-delete-form" method="POST" action="{{ route('admin.agenda.items.bulkDelete') }}">
            @csrf
        </form>
        <form id="bulk-items-export-form" method="POST" action="{{ route('admin.agenda.items.export') }}">
            @csrf
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>{{ trans('global.actions') }}</th>
                    <th width="10"><input type="checkbox" id="select-all-items" aria-label="Select all"></th>
                    <th>ID</th>
                    <th>Event</th>
                    <th>Day</th>
                    <th>Room</th>
                    <th>Title</th>
                    <th>Time</th>
                    <th>Theme</th>
                    <th>Active</th>
                </tr>
                </thead>
                <tbody>
                @forelse($agendaItems as $item)
                    <tr>
                        <td>
                            <a class="btn btn-xs btn-info" href="{{ route('admin.agenda.items.edit', $item->id) }}">{{ trans('global.edit') }}</a>
                            <form action="{{ route('admin.agenda.items.destroy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" type="submit">{{ trans('global.delete') }}</button>
                            </form>
                        </td>
                        <td><input type="checkbox" class="agenda-item-row-checkbox" value="{{ $item->id }}"></td>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->event?->title }}</td>
                        <td>{{ $item->day?->title }}</td>
                        <td>{{ $item->room?->name }}</td>
                        <td>{{ $item->title }}</td>
                        <td>{{ trim(($item->start_time ?? '') . (($item->end_time ?? '') ? ' - ' . $item->end_time : '')) }}</td>
                        <td>{{ $item->panel_theme }}</td>
                        <td>{{ $item->is_active ? trans('global.yes') : trans('global.no') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10">{{ trans('global.no_entries_in_table') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $agendaItems->links() }}
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    (function () {
        const selectAll = document.getElementById('select-all-items');
        if (!selectAll) {
            return;
        }

        const rowCheckboxes = Array.from(document.querySelectorAll('.agenda-item-row-checkbox'));
        const forms = [
            'bulk-items-publish-form',
            'bulk-items-unpublish-form',
            'bulk-items-delete-form',
            'bulk-items-export-form'
        ].map(function (id) { return document.getElementById(id); }).filter(Boolean);

        function syncForms() {
            forms.forEach(function (form) {
                form.querySelectorAll('input[name="ids[]"]').forEach(function (input) { input.remove(); });
                rowCheckboxes.forEach(function (checkbox) {
                    if (!checkbox.checked) return;
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ids[]';
                    hidden.value = checkbox.value;
                    form.appendChild(hidden);
                });
            });
        }

        selectAll.addEventListener('change', function () {
            rowCheckboxes.forEach(function (checkbox) { checkbox.checked = selectAll.checked; });
            syncForms();
        });

        rowCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                selectAll.checked = rowCheckboxes.length > 0 && rowCheckboxes.every(function (item) { return item.checked; });
                syncForms();
            });
        });
    })();
</script>
@endsection
