<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WorldEvent;
use App\Support\WorldEventsPageSettings;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorldEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $events = WorldEvent::query()
            ->where('is_active', true)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        $availableYears = $this->availableYears($events);
        $defaultYear = (int) now()->year;
        $selectedYear = (int) $request->integer('year', $defaultYear);
        if ($request->filled('year') && ! $availableYears->contains($selectedYear)) {
            $selectedYear = $defaultYear;
        }

        $selectedMonth = max(1, min(12, (int) $request->integer('month', now()->month)));
        $selectedView = $request->input('view', 'monthly') === 'yearly' ? 'yearly' : 'monthly';
        $selectedCountry = trim((string) $request->input('country', ''));
        $selectedEventName = trim((string) $request->input('event_name', ''));
        $search = trim((string) $request->input('search', ''));

        $filteredEvents = $events->filter(function (WorldEvent $event) use ($selectedCountry, $selectedEventName, $search) {
            if ($selectedCountry !== '' && (string) $event->country !== $selectedCountry) {
                return false;
            }

            if ($selectedEventName !== '' && $event->name !== $selectedEventName) {
                return false;
            }

            if ($search !== '') {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $event->name,
                    $event->city,
                    $event->country,
                    $event->theme,
                ])));

                return str_contains($haystack, mb_strtolower($search));
            }

            return true;
        })->values();

        $monthlyEvents = $this->eventsInRange(
            $filteredEvents,
            Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth(),
            Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth()
        );

        $yearlyEvents = $this->eventsInRange(
            $filteredEvents,
            Carbon::create($selectedYear, 1, 1)->startOfYear(),
            Carbon::create($selectedYear, 1, 1)->endOfYear()
        );

        return response()->json([
            'data' => [
                'settings' => WorldEventsPageSettings::get(),
                'selected_view' => $selectedView,
                'selected_year' => $selectedYear,
                'selected_month' => $selectedMonth,
                'selected_country' => $selectedCountry,
                'selected_event_name' => $selectedEventName,
                'search' => $search,
                'available_years' => $availableYears,
                'countries' => $events->pluck('country')->filter(fn ($country) => trim((string) $country) !== '')->unique()->sort()->values(),
                'event_names' => $events->pluck('name')->filter(fn ($name) => trim((string) $name) !== '')->unique()->sort()->values(),
                'events' => $filteredEvents->map(fn (WorldEvent $event) => $this->eventPayload($event))->values(),
                'monthly_events' => $monthlyEvents->map(fn (WorldEvent $event) => $this->eventPayload($event))->values(),
                'yearly_events' => $yearlyEvents->map(fn (WorldEvent $event) => $this->eventPayload($event))->values(),
                'stats' => [
                    'total_events' => $yearlyEvents->count(),
                    'countries' => $yearlyEvents->pluck('country')->filter()->unique()->count(),
                    'cities' => $yearlyEvents->pluck('city')->filter()->unique()->count(),
                    'themes' => $yearlyEvents->pluck('theme')->filter()->unique()->count(),
                ],
            ],
        ]);
    }

    private function eventPayload(WorldEvent $event): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'start_date' => $event->start_date?->format('Y-m-d'),
            'end_date' => $event->end_date?->format('Y-m-d'),
            'days' => $event->days,
            'city' => $event->city,
            'country' => $event->country,
            'location' => trim(implode(', ', array_filter([$event->city, $event->country]))),
            'theme' => $event->theme,
            'website' => trim((string) ($event->website ?? '')),
            'logo_url' => $this->normalizeAssetUrl($event->logo_path),
            'stars' => $event->stars,
            'month_range' => $event->start_date?->format('M j') . ' – ' . $event->end_date?->format('M j, Y'),
        ];
    }

    private function eventsInRange($events, Carbon $rangeStart, Carbon $rangeEnd)
    {
        return $events->filter(function (WorldEvent $event) use ($rangeStart, $rangeEnd) {
            $start = Carbon::parse($event->start_date)->startOfDay();
            $end = Carbon::parse($event->end_date)->endOfDay();

            return $start->lte($rangeEnd) && $end->gte($rangeStart);
        })->sort(function (WorldEvent $first, WorldEvent $second) {
            $firstStart = Carbon::parse($first->start_date)->timestamp;
            $secondStart = Carbon::parse($second->start_date)->timestamp;

            if ($firstStart !== $secondStart) {
                return $firstStart <=> $secondStart;
            }

            $firstEnd = Carbon::parse($first->end_date)->timestamp;
            $secondEnd = Carbon::parse($second->end_date)->timestamp;

            if ($firstEnd !== $secondEnd) {
                return $firstEnd <=> $secondEnd;
            }

            return strcmp((string) $first->name, (string) $second->name);
        })->values();
    }

    private function availableYears($events)
    {
        $years = collect();

        foreach ($events as $event) {
            $start = Carbon::parse($event->start_date)->year;
            $end = Carbon::parse($event->end_date)->year;

            foreach (range($start, $end) as $year) {
                $years->push($year);
            }
        }

        if ($years->isEmpty()) {
            $years->push(now()->year);
        }

        return $years->unique()->sort()->values();
    }

    private function normalizeAssetUrl(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (
            str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://')
            || str_starts_with($value, '//')
            || str_starts_with($value, '/')
        ) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }
}
