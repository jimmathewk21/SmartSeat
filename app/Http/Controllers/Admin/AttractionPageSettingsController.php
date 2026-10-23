<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AttractionPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttractionPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('attraction_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = AttractionPageSettings::get();

        return view('admin.attractions.page-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('attraction_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'hero_label' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_title_suffix' => ['nullable', 'string', 'max:120'],
            'hero_description' => ['required', 'string'],
            'testimonial_title' => ['required', 'string', 'max:120'],
        ]);

        AttractionPageSettings::set($validated);

        return redirect()
            ->route('admin.attractions.page-settings.edit')
            ->with('message', 'Attraction page settings updated successfully.');
    }
}
