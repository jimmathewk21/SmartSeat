<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AgendaDay;
use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\Speaker;
use App\Support\AgendaPageSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $event = $this->resolveEvent($request);

        if (! $event) {
            return response()->json([
                'data' => [
                    'event' => null,
                    'active_day_id' => null,
                    'days' => [],
                    'rooms' => [],
                    'speakers' => [],
                ],
            ]);
        }

        $days = AgendaDay::query()
            ->where('event_id', $event->id)
            ->where('is_active', true)
            ->with([
                'items' => fn ($query) => $query
                    ->with(['room', 'speakers', 'moderatorSpeaker'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $activeDayId = $request->integer('day_id');
        if (! $activeDayId || ! $days->contains('id', $activeDayId)) {
            $activeDayId = $days->first()?->id;
        }

        $rooms = $event->agendaRooms()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $speakers = Speaker::query()
            ->where('event_id', $event->id)
            ->where('is_active', true)
            ->where('show_on_scrollbar', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(12)
            ->get();

        return response()->json([
            'data' => [
                'event' => $this->eventPayload($event),
                'active_day_id' => $activeDayId,
                'days' => $days->map(fn (AgendaDay $day) => $this->dayPayload($day))->values(),
                'rooms' => $rooms->map(fn ($room) => [
                    'id' => $room->id,
                    'name' => $room->name,
                ])->values(),
                'speakers' => $speakers->map(fn (Speaker $speaker) => [
                    'id' => $speaker->id,
                    'name' => $speaker->name,
                    'designation' => $speaker->designation,
                    'company_name' => $speaker->company_name,
                    'display_photo_url' => $speaker->display_photo_url,
                ])->values(),
            ],
        ]);
    }

    public function show(AgendaItem $agendaItem): JsonResponse
    {
        $agendaItem->load([
            'event',
            'day',
            'room',
            'speakers',
            'moderatorSpeaker',
        ]);

        abort_unless(
            $agendaItem->is_active
            && $agendaItem->event?->is_published
            && $agendaItem->day?->is_active
            && $agendaItem->room?->is_active,
            404
        );

        return response()->json([
            'data' => $this->agendaItemPayload($agendaItem),
        ]);
    }

    private function resolveEvent(Request $request): ?Event
    {
        $eventQuery = Event::query()->published();

        $eventId = $request->integer('event_id');
        if ($eventId) {
            $event = (clone $eventQuery)->where('id', $eventId)->first();
            if ($event) {
                return $event;
            }
        }

        $settings = AgendaPageSettings::get();
        if (! empty($settings['event_id'])) {
            $event = (clone $eventQuery)->where('id', (int) $settings['event_id'])->first();
            if ($event) {
                return $event;
            }
        }

        return (clone $eventQuery)->orderBy('sort_order')->orderBy('start_at')->first();
    }

    private function eventPayload(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'venue' => $event->venue,
            'location' => $event->location,
            'start_at' => $event->start_at?->format('Y-m-d H:i:s'),
            'end_at' => $event->end_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function dayPayload(AgendaDay $day): array
    {
        return [
            'id' => $day->id,
            'title' => $day->title,
            'weekday' => $day->weekday,
            'agenda_date' => $day->agenda_date?->format('Y-m-d'),
            'sort_order' => $day->sort_order,
            'items' => $day->items->map(fn (AgendaItem $item) => $this->agendaItemListPayload($item))->values(),
        ];
    }

    private function agendaItemListPayload(AgendaItem $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'topic_description' => $item->topic_description,
            'start_time' => $item->start_time,
            'end_time' => $item->end_time,
            'time_label' => trim((string) $item->start_time . ($item->end_time ? ' - ' . $item->end_time : '')),
            'panel_theme' => $item->panel_theme,
            'moderator_name' => $item->moderator_name,
            'moderator_speaker' => $item->moderatorSpeaker ? [
                'id' => $item->moderatorSpeaker->id,
                'name' => $item->moderatorSpeaker->name,
                'designation' => $item->moderatorSpeaker->designation,
                'company_name' => $item->moderatorSpeaker->company_name,
                'display_photo_url' => $item->moderatorSpeaker->display_photo_url,
            ] : null,
            'room' => $item->room ? [
                'id' => $item->room->id,
                'name' => $item->room->name,
            ] : null,
            'speakers' => $item->speakers->map(fn (Speaker $speaker) => [
                'id' => $speaker->id,
                'name' => $speaker->name,
                'designation' => $speaker->designation,
                'company_name' => $speaker->company_name,
                'display_photo_url' => $speaker->display_photo_url,
            ])->values(),
            'agenda_file_url' => $this->normalizeAssetUrl($item->agenda_file_url),
            'moderator_ppt_url' => $this->normalizeAssetUrl($item->moderator_ppt_url),
        ];
    }

    private function agendaItemPayload(AgendaItem $item): array
    {
        return [
            ...$this->agendaItemListPayload($item),
            'event' => $item->event ? $this->eventPayload($item->event) : null,
            'day' => $item->day ? [
                'id' => $item->day->id,
                'title' => $item->day->title,
                'weekday' => $item->day->weekday,
                'agenda_date' => $item->day->agenda_date?->format('Y-m-d'),
            ] : null,
            'room' => $item->room ? [
                'id' => $item->room->id,
                'name' => $item->room->name,
            ] : null,
            'speakers' => $item->speakers->map(fn (Speaker $speaker) => [
                'id' => $speaker->id,
                'name' => $speaker->name,
                'designation' => $speaker->designation,
                'company_name' => $speaker->company_name,
                'country' => $speaker->country,
                'topic' => $speaker->topic,
                'display_photo_url' => $speaker->display_photo_url,
            ])->values(),
            'moderator_speaker' => $item->moderatorSpeaker ? [
                'id' => $item->moderatorSpeaker->id,
                'name' => $item->moderatorSpeaker->name,
                'designation' => $item->moderatorSpeaker->designation,
                'company_name' => $item->moderatorSpeaker->company_name,
                'display_photo_url' => $item->moderatorSpeaker->display_photo_url,
            ] : null,
            'related_sessions' => $this->relatedSessions($item),
        ];
    }

    private function relatedSessions(AgendaItem $item)
    {
        $speakerIds = $item->speakers->pluck('id');

        if ($speakerIds->isEmpty() && ! $item->moderator_speaker_id) {
            return [];
        }

        return AgendaItem::query()
            ->with(['day', 'room'])
            ->where('event_id', $item->event_id)
            ->where('is_active', true)
            ->where('id', '!=', $item->id)
            ->where(function ($query) use ($speakerIds, $item) {
                if ($speakerIds->isNotEmpty()) {
                    $query->whereHas('speakers', function ($speakerQuery) use ($speakerIds) {
                        $speakerQuery->whereIn('speakers.id', $speakerIds);
                    });
                }

                if ($item->moderator_speaker_id) {
                    $query->orWhere('moderator_speaker_id', $item->moderator_speaker_id);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->map(fn (AgendaItem $session) => [
                'id' => $session->id,
                'title' => $session->title,
                'date' => $session->day?->agenda_date?->format('Y-m-d'),
                'weekday' => $session->day?->weekday,
                'time' => trim((string) $session->start_time . ($session->end_time ? ' - ' . $session->end_time : '')),
                'room' => $session->room?->name,
                'panel_theme' => $session->panel_theme,
            ])->values();
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
