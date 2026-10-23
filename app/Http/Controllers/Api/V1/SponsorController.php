<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\SponsorCategory;
use App\Support\SponsorPageSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SponsorController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = SponsorCategory::query()
            ->where('is_active', true)
            ->with([
                'sponsors' => function ($query) {
                    $query->where('is_active', true)
                        ->with(['sponsorCategory', 'sponsorCategories'])
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $titleSponsor = $categories
            ->first(fn (SponsorCategory $category) => $category->sponsors->isNotEmpty())
            ?->sponsors
            ->first();

        $speakers = Speaker::query()
            ->where('is_active', true)
            ->where('show_on_scrollbar', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(12)
            ->get();

        return response()->json([
            'data' => [
                'settings' => SponsorPageSettings::get(),
                'title_sponsor' => $titleSponsor ? $this->sponsorPayload($titleSponsor) : null,
                'categories' => $categories->map(fn (SponsorCategory $category) => $this->categoryPayload($category))->values(),
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

    public function show(Sponsor $sponsor): JsonResponse
    {
        $sponsor->load(['sponsorCategory', 'sponsorCategories']);

        abort_unless(
            $sponsor->is_active && $sponsor->sponsorCategories->where('is_active', true)->isNotEmpty(),
            404
        );

        $speakers = Speaker::query()
            ->where('is_active', true)
            ->where('show_on_scrollbar', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(12)
            ->get();

        return response()->json([
            'data' => [
                ...$this->sponsorPayload($sponsor),
                'show_contact_details' => (bool) ($sponsor->show_contact_details ?? true),
                'show_website_url' => (bool) ($sponsor->show_website_url ?? true),
                'show_linkedin_url' => (bool) ($sponsor->show_linkedin_url ?? true),
                'show_bio' => (bool) ($sponsor->show_bio ?? true),
                'show_advert' => (bool) ($sponsor->show_advert ?? true),
                'asset_type' => $sponsor->asset_type,
                'resource_url' => $this->normalizeAssetUrl($sponsor->resource_url),
                'advert_url' => $this->normalizeAssetUrl($sponsor->advert_url),
                'brochure_url' => $this->normalizeAssetUrl($sponsor->brochure_url),
                'sponsor_video_url' => trim((string) ($sponsor->sponsor_video_url ?? '')),
                'detail' => $sponsor->detail,
                'contact' => [
                    'name' => $sponsor->contact_name,
                    'designation' => $sponsor->contact_designation,
                    'mobile' => $sponsor->contact_mobile,
                    'email' => $sponsor->contact_email,
                ],
                'website_url' => trim((string) ($sponsor->website_url ?? '')),
                'linkedin_url' => trim((string) ($sponsor->linkedin_url ?? '')),
                'related_speakers' => $speakers->map(fn (Speaker $speaker) => [
                    'id' => $speaker->id,
                    'name' => $speaker->name,
                    'designation' => $speaker->designation,
                    'company_name' => $speaker->company_name,
                    'display_photo_url' => $speaker->display_photo_url,
                ])->values(),
            ],
        ]);
    }

    private function categoryPayload(SponsorCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'logo_size' => $this->normalizeLogoSize($category->logo_size ?? null),
            'sponsors' => $category->sponsors->map(fn (Sponsor $sponsor) => $this->sponsorPayload($sponsor, $category->name))->values(),
        ];
    }

    private function sponsorPayload(Sponsor $sponsor, ?string $categoryName = null): array
    {
        $primaryCategory = $categoryName ?: $sponsor->sponsorCategory?->name;
        $categoryNames = $sponsor->sponsorCategories
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        $displayLabel = $this->displayLabel($sponsor);
        $displayDescription = $this->displayDescription($sponsor);

        return [
            'id' => $sponsor->id,
            'name' => $sponsor->name,
            'primary_category_name' => $primaryCategory,
            'category_names' => $categoryNames,
            'sub_category' => $sponsor->sub_category,
            'sub_category_text' => $sponsor->sub_category_text,
            'use_sub_category_text' => (bool) $sponsor->use_sub_category_text,
            'display_label' => $displayLabel,
            'description' => $displayDescription,
            'website_url' => trim((string) ($sponsor->website_url ?? '')),
            'linkedin_url' => trim((string) ($sponsor->linkedin_url ?? '')),
            'logo_url' => trim((string) ($sponsor->logo_url ?? '')),
            'display_logo_url' => $sponsor->display_logo_url,
            'logo_size' => $this->normalizeLogoSize($sponsor->logo_size ?? null),
            'show_on_homepage' => (bool) $sponsor->show_on_homepage,
            'show_on_scrollbar' => (bool) $sponsor->show_on_scrollbar,
        ];
    }

    private function displayLabel(Sponsor $sponsor): string
    {
        if ((bool) $sponsor->use_sub_category_text) {
            $label = trim((string) ($sponsor->sub_category_text ?? ''));
            if ($label !== '') {
                return $label;
            }
        }

        $label = trim((string) ($sponsor->sub_category ?? ''));
        if ($label !== '') {
            return $label;
        }

        return $sponsor->name;
    }

    private function displayDescription(Sponsor $sponsor): string
    {
        $detail = trim((string) ($sponsor->detail ?? ''));

        if ((bool) ($sponsor->show_bio ?? true) && $detail !== '') {
            return Str::limit(strip_tags($detail), 140);
        }

        if ((bool) $sponsor->use_sub_category_text) {
            $text = trim((string) ($sponsor->sub_category_text ?? ''));
            if ($text !== '') {
                return $text;
            }
        }

        return trim((string) ($sponsor->sub_category ?? ''));
    }

    private function normalizeLogoSize(?string $logoSize): string
    {
        $logoSize = strtolower(trim((string) $logoSize));

        return in_array($logoSize, ['sm', 'md', 'lg'], true) ? $logoSize : 'md';
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
