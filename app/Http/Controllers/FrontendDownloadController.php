<?php

namespace App\Http\Controllers;

use App\Models\DownloadMediaItem;
use App\Support\DownloadPageSettings;

class FrontendDownloadController extends Controller
{
    public function index()
    {
        $settings = DownloadPageSettings::get();

        $items = DownloadMediaItem::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('section_title')
            ->map(function ($sectionItems) {
                return $sectionItems
                    ->sortBy([
                        ['sort_order', 'asc'],
                        ['id', 'asc'],
                    ])
                    ->values();
            });

        return view('frontend.download.index', compact('settings', 'items'));
    }
}
