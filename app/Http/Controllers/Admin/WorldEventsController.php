<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorldEvent;
use App\Support\WorldEventsPageSettings;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class WorldEventsController extends Controller
{
    public function editPageSettings()
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.worldEvents.page-settings', [
            'settings' => WorldEventsPageSettings::get(),
        ]);
    }

    public function updatePageSettings(Request $request)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'hero_label' => ['nullable', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:160'],
            'hero_title_suffix' => ['nullable', 'string', 'max:160'],
            'hero_description' => ['nullable', 'string', 'max:1000'],
            'overview_title' => ['nullable', 'string', 'max:160'],
            'monthly_tab_text' => ['nullable', 'string', 'max:50'],
            'yearly_tab_text' => ['nullable', 'string', 'max:50'],
            'search_placeholder' => ['nullable', 'string', 'max:120'],
            'event_filter_label' => ['nullable', 'string', 'max:80'],
            'country_filter_label' => ['nullable', 'string', 'max:80'],
            'monthly_timeline_title' => ['nullable', 'string', 'max:80'],
            'yearly_calendar_title' => ['nullable', 'string', 'max:80'],
            'empty_state_text' => ['nullable', 'string', 'max:255'],
        ]);

        WorldEventsPageSettings::set($validated);

        return redirect()
            ->route('admin.world-events.page-settings.edit')
            ->with('message', 'World Events page settings updated successfully.');
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = $this->filteredQuery($request);

        $allowedSorts = ['id', 'name', 'year', 'start_date', 'end_date', 'city', 'country', 'theme', 'stars', 'is_active'];
        $sortBy = $request->input('sort_by', 'id');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }
        $sortDir = $request->input('sort_dir', 'desc') === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'year') {
            $query->orderByRaw("YEAR(start_date) {$sortDir}")
                ->orderBy('id');
        } else {
            $query->orderBy($sortBy, $sortDir);
            if ($sortBy !== 'id') {
                $query->orderBy('id');
            }
        }

        $worldEvents = $query
            ->paginate(20)
            ->appends($request->query());

        $eventNames = WorldEvent::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
        $countries = WorldEvent::query()->whereNotNull('country')->where('country', '!=', '')->distinct()->orderBy('country')->pluck('country');
        $years = WorldEvent::query()
            ->selectRaw('DISTINCT YEAR(start_date) as year')
            ->whereNotNull('start_date')
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values();

        return view('admin.worldEvents.index', compact('worldEvents', 'eventNames', 'countries', 'years'));
    }

    public function create()
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.worldEvents.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $this->validateRequest($request);

        WorldEvent::create($validated);

        return redirect()->route('admin.world-events.index')->with('message', 'World Event created successfully.');
    }

    public function show(WorldEvent $worldEvent)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.worldEvents.show', compact('worldEvent'));
    }

    public function edit(WorldEvent $worldEvent)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.worldEvents.edit', compact('worldEvent'));
    }

    public function update(Request $request, WorldEvent $worldEvent)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $this->validateRequest($request, $worldEvent);

        $worldEvent->update($validated);

        return redirect()->route('admin.world-events.index')->with('message', 'World Event updated successfully.');
    }

    public function destroy(WorldEvent $worldEvent)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $worldEvent->delete();

        return back()->with('message', 'World Event deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:world_events,id'],
        ]);

        WorldEvent::query()->whereIn('id', $validated['ids'])->delete();

        return back()->with('message', 'Selected world events deleted successfully.');
    }

    public function bulkStatus(Request $request)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:world_events,id'],
            'status' => ['required', 'in:publish,unpublish'],
        ]);

        $isActive = $validated['status'] === 'publish';

        WorldEvent::query()
            ->whereIn('id', $validated['ids'])
            ->update(['is_active' => $isActive]);

        return back()->with('message', $isActive
            ? 'Selected world events published successfully.'
            : 'Selected world events unpublished successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->filled('ids')) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'exists:world_events,id'],
            ]);

            $query = WorldEvent::query()->whereIn('id', $validated['ids']);
        } else {
            $query = $this->filteredQuery($request);
        }

        $filename = 'world-events-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Name of Event',
                'Start Date',
                'End Date',
                'Days',
                'City',
                'Country',
                'Theme',
                'Website',
                'Stars',
                'Status',
            ]);

            $query->orderByDesc('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $worldEvent) {
                    fputcsv($handle, [
                        $worldEvent->id,
                        $worldEvent->name,
                        optional($worldEvent->start_date)->format('Y-m-d'),
                        optional($worldEvent->end_date)->format('Y-m-d'),
                        $worldEvent->days ?? '',
                        $worldEvent->city ?? '',
                        $worldEvent->country ?? '',
                        $worldEvent->theme ?? '',
                        $worldEvent->website ?? '',
                        $worldEvent->stars,
                        $worldEvent->is_active ? 'Published' : 'Unpublished',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function validateRequest(Request $request, ?WorldEvent $worldEvent = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('world_events', 'name')
                    ->where(fn ($q) => $q->where('start_date', $request->input('start_date')))
                    ->ignore($worldEvent?->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'theme' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:width=280,height=150'],
            'stars' => ['nullable', 'integer', 'min:0', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $logoPath = $validated['logo_path'] ?? $worldEvent?->logo_path;
        if ($request->hasFile('logo_file')) {
            $logoPath = $this->uploadLogo($request);
        }

        return [
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $validated['days'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'theme' => $validated['theme'] ?? null,
            'website' => $validated['website'] ?? null,
            'logo_path' => $logoPath ?: null,
            'stars' => (int) ($request->input('stars', 0)),
            'is_active' => (bool) ($request->input('is_active', true)),
        ];
    }

    private function uploadLogo(Request $request): string
    {
        $file = $request->file('logo_file');
        $directory = public_path('uploads/world-events/logos');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = 'world-event-logo-' . now()->format('YmdHis') . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $fileName);

        return 'uploads/world-events/logos/' . $fileName;
    }

    private function filteredQuery(Request $request)
    {
        $query = WorldEvent::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('theme', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event_name')) {
            $query->where('name', $request->input('event_name'));
        }

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        if ($request->filled('year')) {
            $query->whereYear('start_date', (int) $request->input('year'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $query;
    }
}
