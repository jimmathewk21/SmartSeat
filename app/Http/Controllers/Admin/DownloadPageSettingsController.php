<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DownloadPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = DownloadPageSettings::get();

        return view('admin.frontend.download', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'hero_label' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_title_suffix' => ['nullable', 'string', 'max:120'],
            'hero_description' => ['required', 'string'],
            'intro_paragraph_1' => ['nullable', 'string'],
            'intro_heading' => ['nullable', 'string', 'max:160'],
            'intro_paragraph_2' => ['nullable', 'string'],
            'intro_paragraph_3' => ['nullable', 'string'],
            'left_column_heading' => ['nullable', 'string', 'max:160'],
            'left_column_content' => ['nullable', 'string'],
            'right_column_heading' => ['nullable', 'string', 'max:160'],
            'right_column_content' => ['nullable', 'string'],
            'testimonial_title' => ['required', 'string', 'max:120'],
        ]);

        DownloadPageSettings::set($validated);

        return redirect()
            ->route('admin.frontend.download.edit')
            ->with('message', 'Download page settings updated successfully.');
    }
}
