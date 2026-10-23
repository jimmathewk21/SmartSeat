<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyEventRequest;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventTicketPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class EventsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Event::query()
            ->select(sprintf('%s.*', (new Event)->table))
            ->withCount([
                'ticketPlans as active_ticket_plans_count' => function ($ticketPlanQuery) {
                    $ticketPlanQuery->where('is_active', true);
                },
            ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location')) {
            $location = trim((string) $request->input('location'));
            $query->where('location', 'like', "%{$location}%");
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        $allowedSorts = ['id', 'title', 'location', 'start_at', 'sort_order', 'is_published', 'is_active'];
        $sortBy = $request->input('sort_by', 'id');
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }
        $sortDir = $request->input('sort_dir', 'desc') === 'desc' ? 'desc' : 'asc';

        $events = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(25)
            ->appends($request->query());

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        abort_if(Gate::denies('event_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.events.create', [
            'planDefaults' => $this->eventPlanDefaults(),
        ]);
    }

    public function store(StoreEventRequest $request)
    {
        $payload = $this->preparePayload($request, $request->validated());
        $plans = $payload['plans'] ?? [];
        unset($payload['plans']);

        $this->guardPublishedEventPlanPrices((bool) $payload['is_published'], $plans);

        DB::transaction(function () use ($request, $payload, $plans) {
            $event = Event::create($payload);
            $this->syncPlans($request, $event, $plans);
            $this->syncSinglePublishedCurrentOrUpcomingEvent($event, (bool) $payload['is_published']);
        });

        return redirect()->route('admin.events.index');
    }

    public function edit(Event $event)
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $event->load('ticketPlans');

        return view('admin.events.edit', [
            'event' => $event,
            'planDefaults' => $this->eventPlanDefaults($event),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $payload = $this->preparePayload($request, $request->validated());
        $plans = $payload['plans'] ?? [];
        unset($payload['plans']);

        $this->guardPublishedEventPlanPrices((bool) $payload['is_published'], $plans);

        DB::transaction(function () use ($request, $event, $payload, $plans) {
            $event->update($payload);
            $this->syncPlans($request, $event, $plans);
            $this->syncSinglePublishedCurrentOrUpcomingEvent($event, (bool) $payload['is_published']);
        });

        return redirect()->route('admin.events.index');
    }

    public function show(Event $event)
    {
        abort_if(Gate::denies('event_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $event->load(['speakers', 'ticketPlans']);

        return view('admin.events.show', compact('event'));
    }

    public function destroy(Event $event)
    {
        abort_if(Gate::denies('event_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $event->delete();

        return back();
    }

    public function massDestroy(MassDestroyEventRequest $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back();
        }

        Event::whereIn('id', $ids)->delete();

        return back();
    }

    public function bulkStatus(Request $request)
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:events,id'],
            'status' => ['required', 'in:publish,unpublish'],
        ]);

        $isPublished = $validated['status'] === 'publish';
        Event::query()->whereIn('id', $validated['ids'])->update([
            'is_published' => $isPublished,
            'is_active' => $isPublished,
        ]);

        if ($isPublished) {
            $primaryCurrentOrUpcoming = Event::query()
                ->whereIn('id', $validated['ids'])
                ->currentOrUpcoming()
                ->orderBy('sort_order')
                ->orderBy('start_at')
                ->orderBy('id')
                ->first();

            if ($primaryCurrentOrUpcoming) {
                $this->syncSinglePublishedCurrentOrUpcomingEvent($primaryCurrentOrUpcoming, true);
            }
        }

        return back()->with('message', $isPublished
            ? 'Selected events published successfully.'
            : 'Selected events unpublished successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Event::query()
            ->select(sprintf('%s.*', (new Event)->table))
            ->withCount([
                'ticketPlans as active_ticket_plans_count' => function ($ticketPlanQuery) {
                    $ticketPlanQuery->where('is_active', true);
                },
            ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        if ($request->filled('location')) {
            $location = trim((string) $request->input('location'));
            $query->where('location', 'like', "%{$location}%");
        }

        $format = $request->input('format', 'csv') === 'excel' ? 'excel' : 'csv';
        $extension = $format === 'excel' ? 'xls' : 'csv';
        $contentType = $format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv';
        $filename = 'events-export-' . now()->format('Ymd-His') . '.' . $extension;

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Title',
                'Slug',
                'Venue',
                'Location',
                'Start Date',
                'End Date',
                'Published',
                'Active',
                'Buy Ticket',
            ]);

            $query->orderBy('sort_order')->orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $event) {
                    fputcsv($handle, [
                        $event->id,
                        $event->title,
                        $event->slug,
                        $event->venue ?? '',
                        $event->location ?? '',
                        $event->start_at ? $event->start_at->format('Y-m-d') : '',
                        $event->end_at ? $event->end_at->format('Y-m-d') : '',
                        $event->is_published ? 'Published' : 'Unpublished',
                        $event->is_active ? 'Active' : 'Inactive',
                        ($event->isBookableUpcoming() && (int) ($event->active_ticket_plans_count ?? 0) > 0) ? 'Yes' : 'No',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => $contentType,
        ]);
    }

    private function syncPlans(Request $request, Event $event, array $plans): void
    {
        $keptIds = [];
        $nextPlanIndex = (int) ($event->ticketPlans()->max('plan_index') ?? 0);

        foreach (array_values($plans) as $position => $row) {
            $planId = isset($row['id']) ? (int) $row['id'] : null;

            $payload = [
                'name' => (string) ($row['name'] ?? ''),
                'caption' => trim((string) ($row['caption'] ?? '')),
                'background_image_url' => $this->resolvePlanBackgroundImageUrl($request, $position, $row),
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $position,
                'mrp_inr_paise' => (int) round(((float) ($row['mrp_inr'] ?? 0)) * 100),
                'sale_inr_paise' => (int) round(((float) ($row['sale_inr'] ?? 0)) * 100),
                'mrp_usd_cents' => (int) round(((float) ($row['mrp_usd'] ?? 0)) * 100),
                'sale_usd_cents' => (int) round(((float) ($row['sale_usd'] ?? 0)) * 100),
                'price_paise' => (int) round(((float) ($row['sale_inr'] ?? 0)) * 100),
                'benefits' => (string) ($row['benefits'] ?? ''),
                'valid_till_text' => $row['valid_till_text'] ?? null,
                'is_active' => (bool) ($row['is_active'] ?? false),
            ];

            if ($planId) {
                $plan = EventTicketPlan::query()
                    ->where('event_id', $event->id)
                    ->where('id', $planId)
                    ->first();
                if ($plan) {
                    $plan->update($payload);
                    $keptIds[] = $plan->id;
                    continue;
                }
            }

            $nextPlanIndex++;
            $payload['plan_index'] = $nextPlanIndex;
            $created = $event->ticketPlans()->create($payload);
            $keptIds[] = $created->id;
        }

        if (! empty($keptIds)) {
            $event->ticketPlans()->whereNotIn('id', $keptIds)->delete();
            return;
        }

        $event->ticketPlans()->delete();
    }

    private function eventPlanDefaults(?Event $event = null): array
    {
        if ($event) {
            $rows = $event->ticketPlans
                ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
                ->values()
                ->map(function (EventTicketPlan $plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'caption' => (string) ($plan->caption ?? ''),
                        'background_image_url' => (string) ($plan->background_image_url ?? ''),
                        'mrp_inr' => number_format(((int) $plan->mrp_inr_paise) / 100, 2, '.', ''),
                        'sale_inr' => number_format(((int) $plan->sale_inr_paise) / 100, 2, '.', ''),
                        'mrp_usd' => number_format(((int) $plan->mrp_usd_cents) / 100, 2, '.', ''),
                        'sale_usd' => number_format(((int) $plan->sale_usd_cents) / 100, 2, '.', ''),
                        'benefits' => (string) $plan->benefits,
                        'valid_till_text' => (string) ($plan->valid_till_text ?? ''),
                        'is_active' => (bool) $plan->is_active,
                        'sort_order' => (int) ($plan->sort_order ?? 0),
                    ];
                })
                ->all();

            if (! empty($rows)) {
                return $rows;
            }
        }

        return [];
    }

    private function guardPublishedEventPlanPrices(bool $isPublished, array $plans): void
    {
        if (! $isPublished) {
            return;
        }

        foreach (array_values($plans) as $index => $row) {
            $isActive = (bool) ($row['is_active'] ?? false);
            $saleInr = (float) ($row['sale_inr'] ?? 0);
            $saleUsd = (float) ($row['sale_usd'] ?? 0);
            $mrpInr = (float) ($row['mrp_inr'] ?? 0);
            $mrpUsd = (float) ($row['mrp_usd'] ?? 0);

            if (! $isActive) {
                continue;
            }

            if ($saleInr <= 0 || $saleUsd <= 0) {
                throw ValidationException::withMessages([
                    "plans.{$index}.sale_inr" => ['Published events require positive INR and USD sale prices for every active plan.'],
                ]);
            }
            if ($saleInr > $mrpInr) {
                throw ValidationException::withMessages([
                    "plans.{$index}.sale_inr" => ['INR sale price must be less than or equal to INR MRP.'],
                ]);
            }
            if ($saleUsd > $mrpUsd) {
                throw ValidationException::withMessages([
                    "plans.{$index}.sale_usd" => ['USD sale price must be less than or equal to USD MRP.'],
                ]);
            }
        }

    }

    private function syncSinglePublishedCurrentOrUpcomingEvent(Event $event, bool $isPublished): void
    {
        if (! $isPublished || ! $event->isCurrentOrUpcoming()) {
            return;
        }

        Event::query()
            ->whereKeyNot($event->id)
            ->currentOrUpcoming()
            ->where('is_published', true)
            ->update([
                'is_published' => false,
                'is_active' => false,
            ]);
    }

    private function resolvePlanBackgroundImageUrl(Request $request, int $position, array $row): ?string
    {
        $fileKey = "plan_background_image_file_{$position}";
        if ($request->hasFile($fileKey)) {
            $image = $request->file($fileKey);
            $directory = public_path('uploads/event-plans');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($image->getClientOriginalExtension());
            $fileName = 'event-plan-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $image->move($directory, $fileName);

            return 'uploads/event-plans/' . $fileName;
        }

        return $row['background_image_url'] ?? null;
    }

    private function preparePayload(Request $request, array $validated): array
    {
        $validated['is_active'] = (bool) ($validated['is_published'] ?? false);
        $validated['slug'] = Str::slug((string) ($validated['slug'] ?? ''));

        if ($request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $directory = public_path('uploads/events');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($image->getClientOriginalExtension());
            $fileName = 'event-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $image->move($directory, $fileName);

            $validated['image_url'] = 'uploads/events/' . $fileName;
        }

        unset($validated['image_file']);

        return $validated;
    }
}
