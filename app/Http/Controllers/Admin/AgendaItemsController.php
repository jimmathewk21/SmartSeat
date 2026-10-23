<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgendaItemRequest;
use App\Http\Requests\UpdateAgendaItemRequest;
use App\Models\AgendaDay;
use App\Models\AgendaItem;
use App\Models\AgendaRoom;
use App\Models\Event;
use App\Models\Speaker;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class AgendaItemsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = $this->buildFilteredQuery($request);
        $agendaItems = $query
            ->orderByDesc('id')
            ->paginate(25)
            ->appends($request->query());
        $events = Event::query()->select(['id', 'title'])->orderByDesc('id')->get();
        $days = AgendaDay::query()->select(['id', 'title'])->orderBy('sort_order')->orderBy('id')->get();
        $rooms = AgendaRoom::query()->select(['id', 'name'])->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.agenda.items.index', compact('agendaItems', 'events', 'days', 'rooms'));
    }

    public function create()
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        [$events, $days, $rooms, $speakers] = $this->formData();

        return view('admin.agenda.items.create', compact('events', 'days', 'rooms', 'speakers'));
    }

    public function store(StoreAgendaItemRequest $request)
    {
        $payload = $this->preparePayload($request->validated(), $request);
        $speakerIds = $payload['speaker_ids'] ?? [];
        unset($payload['speaker_ids']);

        $item = AgendaItem::create($payload);

        $sync = [];
        foreach (array_values($speakerIds) as $index => $speakerId) {
            $sync[$speakerId] = ['sort_order' => $index];
        }
        $item->speakers()->sync($sync);

        return redirect()->route('admin.agenda.items.index');
    }

    public function edit(AgendaItem $item)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        [$events, $days, $rooms, $speakers] = $this->formData($item->event_id);
        $item->load('speakers');

        return view('admin.agenda.items.edit', compact('item', 'events', 'days', 'rooms', 'speakers'));
    }

    public function update(UpdateAgendaItemRequest $request, AgendaItem $item)
    {
        $payload = $this->preparePayload($request->validated(), $request);
        $speakerIds = $payload['speaker_ids'] ?? [];
        unset($payload['speaker_ids']);

        $item->update($payload);

        $sync = [];
        foreach (array_values($speakerIds) as $index => $speakerId) {
            $sync[$speakerId] = ['sort_order' => $index];
        }
        $item->speakers()->sync($sync);

        return redirect()->route('admin.agenda.items.index');
    }

    public function destroy(AgendaItem $item)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $item->delete();

        return back();
    }

    public function bulkDelete(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        if (empty($ids)) {
            return back()->with('message', 'Please select at least one agenda session.');
        }

        AgendaItem::query()->whereIn('id', $ids)->delete();

        return back()->with('message', 'Selected agenda sessions deleted.');
    }

    public function bulkPublish(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        if (empty($ids)) {
            return back()->with('message', 'Please select at least one agenda session.');
        }

        AgendaItem::query()->whereIn('id', $ids)->update(['is_active' => true]);

        return back()->with('message', 'Selected agenda sessions published.');
    }

    public function bulkUnpublish(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        if (empty($ids)) {
            return back()->with('message', 'Please select at least one agenda session.');
        }

        AgendaItem::query()->whereIn('id', $ids)->update(['is_active' => false]);

        return back()->with('message', 'Selected agenda sessions unpublished.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        $query = $this->buildFilteredQuery($request);
        if (! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $filename = 'agenda-items-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Event', 'Day', 'Room', 'Title', 'Start Time', 'End Time', 'Theme', 'Active']);

            $query
                ->orderByDesc('id')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $item) {
                        fputcsv($handle, [
                            $item->id,
                            optional($item->event)->title,
                            optional($item->day)->title,
                            optional($item->room)->name,
                            $item->title,
                            $item->start_time,
                            $item->end_time,
                            $item->panel_theme,
                            $item->is_active ? 'Published' : 'Unpublished',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function formData(?int $eventId = null): array
    {
        $events = Event::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->pluck('title', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $daysQuery = AgendaDay::query()->where('is_active', true);
        $roomsQuery = AgendaRoom::query()->where('is_active', true);
        $speakersQuery = Speaker::query()->where('is_active', true);

        if ($eventId) {
            $daysQuery->where('event_id', $eventId);
            $roomsQuery->where('event_id', $eventId);
        }

        $days = $daysQuery->orderBy('sort_order')->orderBy('id')->pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');
        $rooms = $roomsQuery->orderBy('sort_order')->orderBy('id')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $speakers = $speakersQuery
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'company_name'])
            ->mapWithKeys(function (Speaker $speaker) {
                $label = $speaker->name;
                if ($speaker->company_name) {
                    $label .= ' - ' . $speaker->company_name;
                }

                return [$speaker->id => $label];
            });

        return [$events, $days, $rooms, $speakers];
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
        $query = AgendaItem::with(['event', 'day', 'room'])->select(sprintf('%s.*', (new AgendaItem)->table));

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        if ($request->filled('agenda_day_id')) {
            $query->where('agenda_day_id', $request->input('agenda_day_id'));
        }

        if ($request->filled('agenda_room_id')) {
            $query->where('agenda_room_id', $request->input('agenda_room_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('panel_theme', 'like', "%{$search}%")
                    ->orWhere('topic_description', 'like', "%{$search}%")
                    ->orWhere('moderator_name', 'like', "%{$search}%")
                    ->orWhere('start_time', 'like', "%{$search}%")
                    ->orWhere('end_time', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($eventQuery) use ($search) {
                        $eventQuery->where('title', 'like', "%{$search}%");
                    })
                    ->orWhereHas('day', function ($dayQuery) use ($search) {
                        $dayQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('weekday', 'like', "%{$search}%");
                    })
                    ->orWhereHas('room', function ($roomQuery) use ($search) {
                        $roomQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('moderatorSpeaker', function ($speakerQuery) use ($search) {
                        $speakerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('designation', 'like', "%{$search}%");
                    })
                    ->orWhereHas('speakers', function ($speakerQuery) use ($search) {
                        $speakerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('designation', 'like', "%{$search}%")
                            ->orWhere('topic', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('time')) {
            $time = trim((string) $request->input('time'));
            $query->where(function ($timeQuery) use ($time) {
                $timeQuery->where('start_time', 'like', "%{$time}%")
                    ->orWhere('end_time', 'like', "%{$time}%");
            });
        }

        return $query;
    }

    private function preparePayload(array $payload, Request $request): array
    {
        if ($request->hasFile('moderator_ppt_file')) {
            $payload['moderator_ppt_url'] = $this->storeAgendaFile(
                $request->file('moderator_ppt_file'),
                'agenda-moderator-file'
            );
        }

        if ($request->hasFile('agenda_file')) {
            $payload['agenda_file_url'] = $this->storeAgendaFile(
                $request->file('agenda_file'),
                'agenda-session-file'
            );
        }

        unset($payload['moderator_ppt_file'], $payload['agenda_file']);

        return $payload;
    }

    private function storeAgendaFile($file, string $prefix): string
    {
        $directory = public_path('uploads/agenda');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = Str::lower($file->getClientOriginalExtension());
        $fileName = $prefix . '-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
        $file->move($directory, $fileName);

        return 'uploads/agenda/' . $fileName;
    }
}
