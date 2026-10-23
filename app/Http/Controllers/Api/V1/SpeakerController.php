<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Speaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SpeakerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Speaker::query()->where('is_active', true);

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));
            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', "%{$keyword}%")
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

        $perPage = max(1, min(50, (int) $request->integer('per_page', 16)));
        $speakers = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => collect($speakers->items())
                ->map(fn (Speaker $speaker) => $this->speakerListPayload($speaker))
                ->values(),
            'meta' => [
                'current_page' => $speakers->currentPage(),
                'from' => $speakers->firstItem(),
                'last_page' => $speakers->lastPage(),
                'per_page' => $speakers->perPage(),
                'to' => $speakers->lastItem(),
                'total' => $speakers->total(),
            ],
            'links' => [
                'first' => $speakers->url(1),
                'last' => $speakers->url($speakers->lastPage()),
                'next' => $speakers->nextPageUrl(),
                'prev' => $speakers->previousPageUrl(),
            ],
        ]);
    }

    public function show(Speaker $speaker): JsonResponse
    {
        abort_unless($speaker->is_active, 404);

        $speaker->load([
            'event',
            'agendaItems.day',
            'agendaItems.room',
            'moderatedAgendaItems.day',
            'moderatedAgendaItems.room',
        ]);

        return response()->json([
            'data' => $this->speakerDetailPayload($speaker),
        ]);
    }

    private function applySorting(Request $request, $query): void
    {
        $allowedSorts = ['name', 'company_name', 'country', 'topic', 'email'];
        $sortBy = (string) $request->input('sort_by', '');

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy)->orderBy('id');

            return;
        }

        $query->orderByRaw('CASE WHEN sort_order > 0 THEN 0 WHEN is_key_figure = 1 THEN 1 ELSE 2 END ASC')
            ->orderByRaw('CASE WHEN sort_order > 0 THEN sort_order ELSE 999999 END ASC')
            ->orderBy('name');
    }

    private function speakerListPayload(Speaker $speaker): array
    {
        return [
            'id' => $speaker->id,
            'name' => $speaker->name,
            'designation' => $speaker->designation,
            'company_name' => $speaker->company_name,
            'country' => $speaker->country,
            'topic' => $speaker->topic,
            'topic_category' => $speaker->topic_category,
            'category' => $speaker->category,
            'speaker_role' => $speaker->speaker_role,
            'gender' => $speaker->gender,
            'is_key_figure' => (bool) $speaker->is_key_figure,
            'display_photo_url' => $speaker->display_photo_url,
        ];
    }

    private function speakerDetailPayload(Speaker $speaker): array
    {
        $sessions = $speaker->agendaItems
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

        return [
            ...$this->speakerListPayload($speaker),
            'bio' => $speaker->bio,
            'email' => $speaker->email,
            'phone' => $speaker->phone,
            'linkedin_url' => $speaker->linkedin_url,
            'show_contact_details' => (bool) $speaker->show_contact_details,
            'resource_url' => $this->normalizeSpeakerAssetUrl($speaker->resource_url),
            'advert_url' => $this->normalizeSpeakerAssetUrl($speaker->advert_url),
            'presentation_file_url' => $this->normalizeSpeakerAssetUrl($speaker->presentation_file_url),
            'event' => $speaker->event ? [
                'id' => $speaker->event->id,
                'title' => $speaker->event->title,
                'slug' => $speaker->event->slug,
                'venue' => $speaker->event->venue,
            ] : null,
            'related_sessions' => $sessions->map(fn ($session) => [
                'id' => $session->id,
                'title' => $session->title,
                'role' => (int) ($session->moderator_speaker_id ?? 0) === (int) $speaker->id ? 'Moderator' : 'Speaker',
                'date' => $session->day?->agenda_date?->format('Y-m-d'),
                'weekday' => $session->day?->weekday,
                'time' => trim((string) ($session->start_time ?? '') . (($session->end_time ?? '') ? ' - ' . $session->end_time : '')),
                'room' => $session->room?->name,
                'panel_theme' => $session->panel_theme,
                'topic_description' => $session->topic_description,
            ])->values(),
        ];
    }

    private function normalizeSpeakerAssetUrl(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '';
        }

        if (
            str_starts_with($url, 'http://')
            || str_starts_with($url, 'https://')
            || str_starts_with($url, '//')
            || str_starts_with($url, '/')
        ) {
            return $url;
        }

        return asset(ltrim($url, '/'));
    }
}
