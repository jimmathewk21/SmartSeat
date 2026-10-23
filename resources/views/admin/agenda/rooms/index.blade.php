@extends('layouts.admin')
@section('content')
<div class="row page-actions">
    <div class="col-lg-12 d-flex flex-wrap align-items-center" style="gap:8px;">
        <a class="btn btn-primary" href="{{ route('admin.agenda.rooms.create') }}">Add Agenda Room</a>
        <button class="btn btn-success" type="submit" form="bulk-rooms-publish-form">Publish</button>
        <button class="btn btn-warning" type="submit" form="bulk-rooms-unpublish-form">Unpublish</button>
        <button class="btn btn-danger" type="submit" form="bulk-rooms-delete-form" onclick="return confirm('{{ trans('global.areYouSure') }}');">Delete</button>
        <button class="btn btn-outline-success" type="submit" form="bulk-rooms-export-form">Export CSV</button>
    </div>
</div>

<div class="card">
    <div class="card-header">Agenda Rooms</div>
    <div class="card-body">
        @if(session('message'))
            <div class="alert alert-info border">{{ session('message') }}</div>
        @endif
        <form method="GET" class="mb-3">
            <div class="form-row">
                <div class="col-md-4">
                    <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="Search room, event...">
                </div>
                <div class="col-md-4">
                    <select class="form-control" name="event_id">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.agenda.rooms.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </div>
        </form>

        <form id="bulk-rooms-publish-form" method="POST" action="{{ route('admin.agenda.rooms.bulkPublish') }}">
            @csrf
        </form>
        <form id="bulk-rooms-unpublish-form" method="POST" action="{{ route('admin.agenda.rooms.bulkUnpublish') }}">
            @csrf
        </form>
        <form id="bulk-rooms-delete-form" method="POST" action="{{ route('admin.agenda.rooms.bulkDelete') }}">
            @csrf
        </form>
        <form id="bulk-rooms-export-form" method="POST" action="{{ route('admin.agenda.rooms.export') }}">
            @csrf
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>{{ trans('global.actions') }}</th>
                    <th width="10"><input type="checkbox" id="select-all-rooms" aria-label="Select all"></th>
                    <th>ID</th>
                    <th>Event</th>
                    <th>Room</th>
                    <th>Sort</th>
                    <th>Active</th>
                </tr>
                </thead>
                <tbody>
                @forelse($agendaRooms as $room)
                    <tr>
                        <td>
                            <a class="btn btn-xs btn-info" href="{{ route('admin.agenda.rooms.edit', $room->id) }}">{{ trans('global.edit') }}</a>
                            <form action="{{ route('admin.agenda.rooms.destroy', $room->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" type="submit">{{ trans('global.delete') }}</button>
                            </form>
                        </td>
                        <td><input type="checkbox" class="agenda-room-row-checkbox" value="{{ $room->id }}"></td>
                        <td>{{ $room->id }}</td>
                        <td>{{ $room->event?->title }}</td>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->sort_order }}</td>
                        <td>{{ $room->is_active ? trans('global.yes') : trans('global.no') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">{{ trans('global.no_entries_in_table') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $agendaRooms->links() }}
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    (function () {
        const selectAll = document.getElementById('select-all-rooms');
        if (!selectAll) {
            return;
        }

        const rowCheckboxes = Array.from(document.querySelectorAll('.agenda-room-row-checkbox'));
        const forms = [
            'bulk-rooms-publish-form',
            'bulk-rooms-unpublish-form',
            'bulk-rooms-delete-form',
            'bulk-rooms-export-form'
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
