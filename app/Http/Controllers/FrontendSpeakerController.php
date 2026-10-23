<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Sponsor;
use App\Models\Speaker;
use Illuminate\Http\Request;

class FrontendSpeakerController extends Controller
{
    public function index(Request $request)
    {
        $query = Speaker::query()
            ->where('is_active', true);

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('designation', 'like', "%{$keyword}%")
                    ->orWhere('company_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('topic', 'like', "%{$keyword}%")
                    ->orWhere('topic_category', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('speaker_type')) {
            $speakerType = (string) $request->input('speaker_type');
            if (in_array($speakerType, ['male', 'female'], true)) {
                $query->where('gender', $speakerType);
            }
        }

        if ($request->filled('speaker_role')) {
            $speakerRole = (string) $request->input('speaker_role');
            if (in_array($speakerRole, ['speaker', 'moderator', 'both'], true)) {
                $query->where('speaker_role', $speakerRole);
            }
        }

        if ($request->input('is_key_figure') === '1') {
            $query->where('is_key_figure', true);
        }

        $this->applySorting($request, $query);

        $speakers = $query
            ->paginate(60)
            ->appends($request->query());

        $countries = Speaker::query()
            ->where('is_active', true)
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $categories = Speaker::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $countryCodeMap = Country::query()
            ->whereNotNull('short_code')
            ->where('short_code', '!=', '')
            ->pluck('short_code', 'name')
            ->mapWithKeys(function ($code, $name) {
                return [mb_strtolower(trim((string) $name)) => strtoupper(trim((string) $code))];
            });

        return view('frontend.speakers.index', compact('speakers', 'countries', 'categories', 'countryCodeMap'));
    }

    private function applySorting(Request $request, $query): void
    {
        $allowedSorts = ['name', 'company_name', 'country', 'topic', 'email'];
        $sortBy = (string) $request->input('sort_by', '');

        if (in_array($sortBy, $allowedSorts, true)) {
            $this->applyNaturalSort($query, $sortBy);

            return;
        }

        $query->orderByRaw('CASE WHEN sort_order > 0 THEN 0 WHEN is_key_figure = 1 THEN 1 ELSE 2 END ASC')
            ->orderByRaw('CASE WHEN sort_order > 0 THEN sort_order ELSE 999999 END ASC')
            ->orderBy('name');
    }

    private function applyNaturalSort($query, string $column): void
    {
        if ($column === 'email') {
            $localPart = "LOWER(COALESCE(SUBSTRING_INDEX(`{$column}`, '@', 1), ''))";
            $domainPart = "LOWER(COALESCE(SUBSTRING_INDEX(`{$column}`, '@', -1), ''))";

            $query->orderByRaw("REGEXP_REPLACE({$localPart}, '[0-9]+$', '') ASC")
                ->orderByRaw("CAST(COALESCE(REGEXP_SUBSTR({$localPart}, '[0-9]+$'), '0') AS UNSIGNED) ASC")
                ->orderByRaw("{$domainPart} ASC")
                ->orderBy('id');

            return;
        }

        $query->orderByRaw($this->naturalSortPrefixExpression($column) . ' ASC')
            ->orderByRaw($this->naturalSortNumberExpression($column) . ' ASC')
            ->orderByRaw("LOWER(COALESCE(`{$column}`, '')) ASC")
            ->orderBy('id');
    }

    private function naturalSortPrefixExpression(string $column): string
    {
        return "REGEXP_REPLACE(LOWER(COALESCE(`{$column}`, '')), '[0-9]+$', '')";
    }

    private function naturalSortNumberExpression(string $column): string
    {
        return "CAST(COALESCE(REGEXP_SUBSTR(LOWER(COALESCE(`{$column}`, '')), '[0-9]+$'), '0') AS UNSIGNED)";
    }

    public function show(Speaker $speaker)
    {
        abort_unless($speaker->is_active, 404);

        $speaker->load([
            'event',
            'agendaItems.day',
            'agendaItems.room',
            'moderatedAgendaItems.day',
            'moderatedAgendaItems.room',
        ]);

        $sponsors = Sponsor::query()
            ->where('is_active', true)
            ->where('show_on_scrollbar', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(12)
            ->get(['id', 'name', 'logo_url', 'logo_size']);

        $agendaSessions = $speaker->agendaItems
            ->merge($speaker->moderatedAgendaItems)
            ->unique('id')
            ->sortBy(function ($session) {
                return sprintf(
                    '%s|%s|%05d|%05d',
                    optional($session->day?->agenda_date)->format('Ymd') ?? '99999999',
                    (string) ($session->start_time ?? ''),
                    (int) ($session->sort_order ?? 0),
                    (int) $session->id
                );
            })
            ->values();

        return view('frontend.speakers.show', compact('speaker', 'sponsors', 'agendaSessions'));
    }
}
