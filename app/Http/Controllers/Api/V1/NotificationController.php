<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AgendaItem;
use App\Models\DownloadMediaItem;
use App\Models\Speaker;
use App\Models\WorldEvent;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $today = collect();
        $earlier = collect();

        $agendaItems = AgendaItem::query()
            ->with(['event', 'day', 'room'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($agendaItems->first()) {
            $today->push($this->notificationPayload(
                'agenda-reminder',
                'Session Reminder',
                $this->agendaDescription($agendaItems->first()),
                '2m ago',
                true,
                '#9B72FB',
                'agenda'
            ));
        }

        if ($agendaItems->skip(1)->first()) {
            $earlier->push($this->notificationPayload(
                'agenda-update',
                'Agenda Updated',
                $this->agendaDescription($agendaItems->skip(1)->first()),
                'Yesterday',
                false,
                '#FFAF45',
                'agenda'
            ));
        }

        $speakers = Speaker::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        if ($speakers->first()) {
            $today->push($this->notificationPayload(
                'speaker-spotlight',
                'Speaker Spotlight',
                $this->speakerDescription($speakers->first()),
                '25m ago',
                true,
                '#34A1FF',
                'speaker'
            ));
        }

        if ($speakers->skip(1)->first()) {
            $earlier->push($this->notificationPayload(
                'speaker-added',
                'New Speaker Added',
                $this->speakerDescription($speakers->skip(1)->first()),
                'Yesterday',
                false,
                '#AAB4FF',
                'speaker'
            ));
        }

        $worldEvents = WorldEvent::query()
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        if ($worldEvents->first()) {
            $today->push($this->notificationPayload(
                'world-event',
                'World Event Added',
                $this->worldEventDescription($worldEvents->first()),
                '1h ago',
                true,
                '#73D66A',
                'world-event'
            ));
        }

        if ($worldEvents->skip(1)->first()) {
            $earlier->push($this->notificationPayload(
                'world-event-followup',
                'Recommended for You',
                $this->worldEventDescription($worldEvents->skip(1)->first()),
                'Yesterday',
                false,
                '#FF6897',
                'world-event'
            ));
        }

        $downloads = DownloadMediaItem::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        if ($downloads->first()) {
            $today->push($this->notificationPayload(
                'download-update',
                'Download Available',
                $this->downloadDescription($downloads->first()),
                '3h ago',
                true,
                '#FFAF45',
                'download'
            ));
        }

        if ($downloads->skip(1)->first()) {
            $earlier->push($this->notificationPayload(
                'download-followup',
                'Session Feedback',
                $this->downloadDescription($downloads->skip(1)->first()),
                'May 22, 2026',
                false,
                '#FF6897',
                'download'
            ));
        }

        return response()->json([
            'data' => [
                'today_items' => $today->values(),
                'earlier_items' => $earlier->values(),
                'unread_count' => $today->count(),
            ],
        ]);
    }

    private function notificationPayload(
        string $id,
        string $title,
        string $description,
        string $timeLabel,
        bool $unread,
        string $accent,
        string $iconKey
    ): array {
        return compact('id', 'title', 'description', 'timeLabel', 'unread', 'accent', 'iconKey');
    }

    private function agendaDescription(AgendaItem $agendaItem): string
    {
        $room = $agendaItem->room?->name ?: 'the main hall';
        $time = trim((string) $agendaItem->start_time . ($agendaItem->end_time ? ' - ' . $agendaItem->end_time : ''));

        return trim(sprintf(
            '%s is scheduled for %s in %s.',
            $agendaItem->title,
            $time !== '' ? $time : 'soon',
            $room
        ));
    }

    private function speakerDescription(Speaker $speaker): string
    {
        return trim(sprintf(
            '%s is now part of the speaker lineup.',
            $speaker->name
        ));
    }

    private function worldEventDescription(WorldEvent $worldEvent): string
    {
        $location = trim(implode(', ', array_filter([$worldEvent->city, $worldEvent->country])));

        return trim(sprintf(
            '%s %s.',
            $worldEvent->name,
            $location !== '' ? 'is listed for ' . $location : 'has been added'
        ));
    }

    private function downloadDescription(DownloadMediaItem $downloadMediaItem): string
    {
        return trim(sprintf(
            '%s is available in %s.',
            $downloadMediaItem->item_title,
            $downloadMediaItem->section_title
        ));
    }
}
