<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DownloadMediaItem;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $today = collect();
        $earlier = collect();

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
                'Download Added',
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

    private function downloadDescription(DownloadMediaItem $downloadMediaItem): string
    {
        return trim(sprintf(
            '%s is available in %s.',
            $downloadMediaItem->item_title,
            $downloadMediaItem->section_title
        ));
    }
}
