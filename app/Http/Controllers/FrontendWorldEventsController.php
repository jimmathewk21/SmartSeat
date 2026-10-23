<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use App\Models\WorldEvent;
use App\Support\WorldEventsPageSettings;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FrontendWorldEventsController extends Controller
{
    public function index(Request $request)
    {
        $settings = WorldEventsPageSettings::get();
        $allEvents = WorldEvent::query()
            ->select(['id', 'name', 'start_date', 'end_date', 'city', 'country', 'theme', 'stars', 'website', 'logo_path'])
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get();

        $availableYears = $this->availableYears($allEvents);
        $defaultYear = (int) now()->year;
        $selectedYear = (int) $request->integer('year', $defaultYear);
        if ($request->filled('year') && ! $availableYears->contains($selectedYear)) {
            $selectedYear = $defaultYear;
        }

        $selectedMonth = max(1, min(12, (int) $request->integer('month', now()->month)));
        $selectedView = $request->input('view', 'monthly') === 'yearly' ? 'yearly' : 'monthly';
        $selectedEventName = trim((string) $request->input('event_name', ''));
        $selectedCountry = trim((string) $request->input('country', ''));
        $search = trim((string) $request->input('search', ''));

        $filteredEvents = $allEvents->filter(function (WorldEvent $event) use ($selectedEventName, $selectedCountry, $search) {
            if ($selectedEventName !== '' && $event->name !== $selectedEventName) {
                return false;
            }

            if ($selectedCountry !== '' && (string) $event->country !== $selectedCountry) {
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

        $monthStart = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $yearStart = Carbon::create($selectedYear, 1, 1)->startOfYear();
        $yearEnd = $yearStart->copy()->endOfYear();

        $monthlyEvents = $this->eventsInRange($filteredEvents, $monthStart, $monthEnd);
        $yearlyEvents = $this->eventsInRange($filteredEvents, $yearStart, $yearEnd);

        $countries = $allEvents->pluck('country')->filter(fn ($country) => trim((string) $country) !== '')->unique()->sort()->values();
        $eventNames = $allEvents->pluck('name')->filter(fn ($name) => trim((string) $name) !== '')->unique()->sort()->values();

        $sponsorQuery = Sponsor::query()
            ->where('is_active', true)
            ->orderByDesc('id');

        $sponsors = (clone $sponsorQuery)
            ->where('show_on_scrollbar', true)
            ->limit(12)
            ->get(['id', 'name', 'logo_url', 'logo_size']);

        if ($sponsors->isEmpty()) {
            $sponsors = (clone $sponsorQuery)
                ->limit(12)
                ->get(['id', 'name', 'logo_url', 'logo_size']);
        }

        return view('frontend.world-events.index', [
            'settings' => $settings,
            'selectedView' => $selectedView,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'selectedEventName' => $selectedEventName,
            'selectedCountry' => $selectedCountry,
            'search' => $search,
            'availableYears' => $availableYears,
            'countries' => $countries,
            'eventNames' => $eventNames,
            'monthlyEvents' => $monthlyEvents,
            'yearlyEvents' => $yearlyEvents,
            'calendarWeeks' => $this->monthCalendarWeeks($monthStart, $monthlyEvents),
            'yearlyMonths' => $this->yearlyMonths($selectedYear, $yearlyEvents),
            'stats' => [
                'total_events' => $yearlyEvents->count(),
                'countries' => $yearlyEvents->pluck('country')->filter()->unique()->count(),
                'cities' => $yearlyEvents->pluck('city')->filter()->unique()->count(),
                'themes' => $yearlyEvents->pluck('theme')->filter()->unique()->count(),
            ],
            'monthOptions' => collect(range(1, 12))->mapWithKeys(fn ($month) => [$month => Carbon::create()->month($month)->format('F')]),
            'sponsors' => $sponsors,
        ]);
    }

    private function eventsInRange(Collection $events, Carbon $rangeStart, Carbon $rangeEnd): Collection
    {
        return $events->filter(function (WorldEvent $event) use ($rangeStart, $rangeEnd) {
            $start = Carbon::parse($event->start_date)->startOfDay();
            $end = Carbon::parse($event->end_date)->endOfDay();

            return $start->lte($rangeEnd) && $end->gte($rangeStart);
        })->sortByDesc('id')->values();
    }

    private function availableYears(Collection $events): Collection
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

    private function monthCalendarWeeks(Carbon $monthStart, Collection $monthlyEvents): Collection
    {
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $period = CarbonPeriod::create($calendarStart, $calendarEnd);
        $days = collect();

        foreach ($period as $date) {
            $dayEvents = $monthlyEvents->filter(function (WorldEvent $event) use ($date) {
                $start = Carbon::parse($event->start_date)->startOfDay();
                $end = Carbon::parse($event->end_date)->endOfDay();

                return $date->betweenIncluded($start, $end);
            })->values();

            $days->push([
                'date' => $date->copy(),
                'isCurrentMonth' => $date->month === $monthStart->month,
                'events' => $dayEvents,
            ]);
        }

        return $days->chunk(7)->values();
    }

    private function yearlyMonths(int $year, Collection $yearlyEvents): Collection
    {
        return collect(range(1, 12))->map(function (int $month) use ($year, $yearlyEvents) {
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $events = $this->eventsInRange($yearlyEvents, $monthStart, $monthEnd);

            return [
                'name' => $monthStart->format('M Y'),
                'month' => $month,
                'weeks' => $this->monthCalendarWeeks($monthStart, $events),
                'events' => $events,
            ];
        });
    }
}
