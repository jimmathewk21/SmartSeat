<?php

namespace App\Http\Controllers;

use App\Models\Speaker;
use App\Models\Sponsor;
use App\Models\SponsorCategory;

class FrontendSponsorController extends Controller
{
    public function index()
    {
        $categories = SponsorCategory::query()
            ->where('is_active', true)
            ->with([
                'sponsors' => function ($query) {
                    $query->where('is_active', true)
                        ->reorder()
                        ->orderByDesc('id');
                },
            ])
            ->orderByDesc('id')
            ->get();

        return view('frontend.sponsors.index', compact('categories'));
    }

    public function show(Sponsor $sponsor)
    {
        $sponsor->load(['sponsorCategory', 'sponsorCategories']);

        abort_unless(
            $sponsor->is_active && $sponsor->sponsorCategories->where('is_active', true)->isNotEmpty(),
            404
        );

        $speakers = Speaker::query()
            ->where('is_active', true)
            ->where('show_on_scrollbar', true)
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('frontend.sponsors.show', compact('sponsor', 'speakers'));
    }
}
