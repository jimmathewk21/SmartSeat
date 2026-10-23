<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SpeakerPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SpeakerPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('speaker_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = SpeakerPageSettings::get();

        return view('admin.speakers.page-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('speaker_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'page_title' => ['required', 'string', 'max:120'],
            'subtitle' => ['required', 'string', 'max:120'],
            'subtitle_suffix' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'apply_page_label' => ['required', 'string', 'max:120'],
            'apply_page_title' => ['required', 'string', 'max:120'],
            'apply_page_title_suffix' => ['nullable', 'string', 'max:120'],
            'apply_page_description' => ['required', 'string'],
            'guidelines_button_text' => ['required', 'string', 'max:120'],
            'become_button_text' => ['required', 'string', 'max:120'],
            'guidelines_content' => ['required', 'string'],
        ]);

        SpeakerPageSettings::set($validated);

        return redirect()
            ->route('admin.speakers.page-settings.edit')
            ->with('message', 'Speaker page settings updated successfully.');
    }
}
