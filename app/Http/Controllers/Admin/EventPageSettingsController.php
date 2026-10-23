<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\EventPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = EventPageSettings::get();

        return view('admin.events.page-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'hero_label' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_title_suffix' => ['nullable', 'string', 'max:120'],
            'hero_description' => ['required', 'string'],
            'upcoming_title' => ['required', 'string', 'max:120'],
            'past_title' => ['required', 'string', 'max:120'],
            'testimonial_title' => ['required', 'string', 'max:120'],
        ]);

        EventPageSettings::set($validated);

        return redirect()
            ->route('admin.events.page-settings.edit')
            ->with('message', 'Event page settings updated successfully.');
    }
}
