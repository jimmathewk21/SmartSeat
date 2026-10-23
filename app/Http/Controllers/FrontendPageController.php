<?php

namespace App\Http\Controllers;

use App\Models\Page;

class FrontendPageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('frontend.page', compact('page'));
    }
}

