<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgendaRoomRequest;
use App\Http\Requests\UpdateAgendaRoomRequest;
use App\Models\AgendaRoom;
use App\Models\Event;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class AgendaRoomsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = $this->buildFilteredQuery($request);
        $agendaRooms = $query->orderBy('sort_order')->orderBy('id')->paginate(25)->appends($request->query());
        $events = Event::query()->select(['id', 'title'])->orderByDesc('id')->get();

        return view('admin.agenda.rooms.index', compact('agendaRooms', 'events'));
    }

    public function create()
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::query()->where('is_active', true)->orderByDesc('id')->pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.agenda.rooms.create', compact('events'));
    }

    public function store(StoreAgendaRoomRequest $request)
    {
        AgendaRoom::create($request->validated());

        return redirect()->route('admin.agenda.rooms.index');
    }

    public function edit(AgendaRoom $room)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::query()->where('is_active', true)->orWhere('id', $room->event_id)->orderByDesc('id')->pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.agenda.rooms.edit', compact('room', 'events'));
    }

    public function update(UpdateAgendaRoomRequest $request, AgendaRoom $room)
    {
        $room->update($request->validated());

        return redirect()->route('admin.agenda.rooms.index');
    }

    public function destroy(AgendaRoom $room)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $room->delete();

        return back();
    }

    public function bulkDelete(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        if (empty($ids)) {
            return back()->with('message', 'Please select at least one agenda room.');
        }

        AgendaRoom::query()->whereIn('id', $ids)->delete();

        return back()->with('message', 'Selected agenda room records deleted.');
    }

    public function bulkPublish(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        if (empty($ids)) {
            return back()->with('message', 'Please select at least one agenda room.');
        }

        AgendaRoom::query()->whereIn('id', $ids)->update(['is_active' => true]);

        return back()->with('message', 'Selected agenda room records published.');
    }

    public function bulkUnpublish(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        if (empty($ids)) {
            return back()->with('message', 'Please select at least one agenda room.');
        }

        AgendaRoom::query()->whereIn('id', $ids)->update(['is_active' => false]);

        return back()->with('message', 'Selected agenda room records unpublished.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        $query = $this->buildFilteredQuery($request);
        if (! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $filename = 'agenda-rooms-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Event', 'Room Name', 'Sort Order', 'Active']);

            $query->orderBy('sort_order')->orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $room) {
                    fputcsv($handle, [
                        $room->id,
                        optional($room->event)->title,
                        $room->name,
                        $room->sort_order,
                        $room->is_active ? 'Published' : 'Unpublished',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function selectedIds(Request $request): array
    {
        return collect((array) $request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = AgendaRoom::with('event')->select(sprintf('%s.*', (new AgendaRoom)->table));

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($eventQuery) use ($search) {
                        $eventQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }
}
