<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Models\Sponsor;
use App\Support\AttractionPageSettings;
use Illuminate\Http\Request;

class FrontendAttractionController extends Controller
{
    public function index(Request $request)
    {
        $settings = AttractionPageSettings::get();

        $query = Attraction::query()
            ->where('is_active', true);

        $selectedCategory = trim((string) $request->query('category', ''));
        if ($selectedCategory !== '') {
            $query->where('category', $selectedCategory);
        }

        $attractions = $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $categories = Attraction::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values();

        $sponsors = Sponsor::query()
            ->where('is_active', true)
            ->where('show_on_scrollbar', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(12)
            ->get(['id', 'name', 'logo_url', 'logo_size']);

        return view('frontend.attractions.index', compact('settings', 'attractions', 'categories', 'selectedCategory', 'sponsors'));
    }
}
