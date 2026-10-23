<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SponsorPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SponsorPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('sponsor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = SponsorPageSettings::get();

        return view('admin.sponsors.page-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('sponsor_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'page_title' => ['required', 'string', 'max:160'],
            'subtitle' => ['required', 'string', 'max:120'],
            'subtitle_suffix' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'button_text' => ['required', 'string', 'max:100'],
            'button_link' => ['required', 'string', 'max:255'],
            'guidelines_button_text' => ['nullable', 'string', 'max:100'],
            'guidelines_content' => ['nullable', 'string'],
            'apply_page_label' => ['nullable', 'string', 'max:120'],
            'apply_page_title' => ['required', 'string', 'max:160'],
            'apply_page_title_suffix' => ['nullable', 'string', 'max:160'],
            'apply_page_description' => ['nullable', 'string'],
        ]);

        SponsorPageSettings::set($validated);

        return redirect()
            ->route('admin.sponsors.page-settings.edit')
            ->with('message', 'Sponsor page settings updated successfully.');
    }
}
