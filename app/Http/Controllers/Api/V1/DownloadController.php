<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DownloadMediaItem;
use App\Support\DownloadPageSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class DownloadController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = DownloadMediaItem::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('section_title')
            ->map(fn (Collection $sectionItems, string $sectionTitle) => $this->sectionPayload($sectionTitle, $sectionItems))
            ->values();

        return response()->json([
            'data' => [
                'settings' => DownloadPageSettings::get(),
                'sections' => $sections,
            ],
        ]);
    }

    private function sectionPayload(string $sectionTitle, Collection $sectionItems): array
    {
        $firstItem = $sectionItems->first();
        $isGroupedLogoSection = (bool) ($firstItem?->is_grouped_versions);
        $isLogoSection = $isGroupedLogoSection
            || str_contains($sectionTitle, 'White Background')
            || str_contains($sectionTitle, 'Dark Background')
            || str_contains($sectionTitle, 'Logo');
        $isWideSection = str_contains($sectionTitle, 'In General for PPT') || str_contains($sectionTitle, 'Floor Plan');

        return [
            'title' => $sectionTitle,
            'layout' => $isLogoSection ? 'logo' : ($isWideSection ? 'wide' : 'poster'),
            'is_dark' => str_contains($sectionTitle, 'Dark Background'),
            'items' => $sectionItems->map(fn (DownloadMediaItem $item) => $this->itemPayload($item))->values(),
        ];
    }

    private function itemPayload(DownloadMediaItem $item): array
    {
        return [
            'id' => $item->id,
            'section_title' => $item->section_title,
            'audience_type' => $item->audience_type,
            'item_title' => $item->item_title,
            'item_subtitle' => $item->item_subtitle,
            'image_url' => $this->normalizeAssetUrl($item->image_url),
            'primary_button_text' => $item->primary_button_text,
            'primary_file_url' => $this->normalizeAssetUrl($item->primary_file_url),
            'secondary_button_text' => $item->secondary_button_text,
            'secondary_file_url' => $this->normalizeAssetUrl($item->secondary_file_url),
            'is_grouped_versions' => (bool) $item->is_grouped_versions,
            'versions' => collect([1, 2, 3])->map(function (int $version) use ($item) {
                return [
                    'label' => trim((string) ($item->{"version_{$version}_label"} ?? 'Version ' . $version)),
                    'image_url' => $this->normalizeAssetUrl($item->{"version_{$version}_image_url"} ?? null),
                    'pdf_url' => $this->normalizeAssetUrl($item->{"version_{$version}_pdf_url"} ?? null),
                    'png_url' => $this->normalizeAssetUrl($item->{"version_{$version}_png_url"} ?? null),
                    'jpg_url' => $this->normalizeAssetUrl($item->{"version_{$version}_jpg_url"} ?? null),
                ];
            })->filter(fn (array $version) => $version['image_url'] !== '' || $version['pdf_url'] !== '' || $version['png_url'] !== '' || $version['jpg_url'] !== '')->values(),
        ];
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
