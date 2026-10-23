<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgendaDayRequest;
use App\Http\Requests\UpdateAgendaDayRequest;
use App\Models\AgendaDay;
use App\Models\Event;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class AgendaDaysController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = $this->buildFilteredQuery($request);
        $agendaDays = $query->orderBy('sort_order')->orderBy('id')->paginate(25)->appends($request->query());
        $events = Event::query()->select(['id', 'title'])->orderByDesc('id')->get();

        return view('admin.agenda.days.index', compact('agendaDays', 'events'));
    }

    public function create()
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::query()->where('is_active', true)->orderByDesc('id')->pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.agenda.days.create', compact('events'));
    }

    public function store(StoreAgendaDayRequest $request)
    {
        AgendaDay::create($request->validated());

        return redirect()->route('admin.agenda.days.index');
    }

    public function edit(AgendaDay $day)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::query()->where('is_active', true)->orWhere('id', $day->event_id)->orderByDesc('id')->pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.agenda.days.edit', compact('day', 'events'));
    }

    public function update(UpdateAgendaDayRequest $request, AgendaDay $day)
    {
        $day->update($request->validated());

        return redirect()->route('admin.agenda.days.index');
    }

    public function destroy(AgendaDay $day)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $day->delete();

        return back();
    }

    public function bulkDelete(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        if (empty($ids)) {
            return back()->with('message', 'Please select at least one agenda day.');
        }

        AgendaDay::query()->whereIn('id', $ids)->delete();

        return back()->with('message', 'Selected agenda day records deleted.');
    }

    public function bulkPublish(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        if (empty($ids)) {
            return back()->with('message', 'Please select at least one agenda day.');
        }

        AgendaDay::query()->whereIn('id', $ids)->update(['is_active' => true]);

        return back()->with('message', 'Selected agenda day records published.');
    }

    public function bulkUnpublish(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        if (empty($ids)) {
            return back()->with('message', 'Please select at least one agenda day.');
        }

        AgendaDay::query()->whereIn('id', $ids)->update(['is_active' => false]);

        return back()->with('message', 'Selected agenda day records unpublished.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = $this->selectedIds($request);
        $query = $this->buildFilteredQuery($request);
        if (! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $filename = 'agenda-days-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Event', 'Day Title', 'Weekday', 'Date', 'Sort Order', 'Active']);

            $query->orderBy('sort_order')->orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $day) {
                    fputcsv($handle, [
                        $day->id,
                        optional($day->event)->title,
                        $day->title,
                        $day->weekday,
                        optional($day->agenda_date)->format('Y-m-d'),
                        $day->sort_order,
                        $day->is_active ? 'Published' : 'Unpublished',
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
        $query = AgendaDay::with('event')->select(sprintf('%s.*', (new AgendaDay)->table));

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('weekday', 'like', "%{$search}%")
                    ->orWhere('agenda_date', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($eventQuery) use ($search) {
                        $eventQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }
}
