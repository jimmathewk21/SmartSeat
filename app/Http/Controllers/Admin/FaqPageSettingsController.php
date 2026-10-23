<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\FaqPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FaqPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = FaqPageSettings::get();

        return view('admin.faqs.page-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'page_title' => ['required', 'string', 'max:120'],
            'subtitle' => ['required', 'string', 'max:180'],
            'subtitle_suffix' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string'],
        ]);

        FaqPageSettings::set($validated);

        return redirect()
            ->route('admin.faqs.page-settings.edit')
            ->with('message', 'FAQ page settings updated successfully.');
    }
}
