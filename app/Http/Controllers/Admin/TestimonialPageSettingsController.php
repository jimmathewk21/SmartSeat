<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TestimonialPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TestimonialPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = TestimonialPageSettings::get();

        return view('admin.testimonials.page-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'hero_label' => ['nullable', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:160'],
            'hero_suffix' => ['nullable', 'string', 'max:120'],
            'hero_description' => ['nullable', 'string', 'max:1000'],
            'filter_title' => ['nullable', 'string', 'max:80'],
            'search_placeholder' => ['nullable', 'string', 'max:120'],
            'feedback_title' => ['nullable', 'string', 'max:120'],
            'empty_state_text' => ['nullable', 'string', 'max:255'],
        ]);

        TestimonialPageSettings::set($validated);

        return redirect()
            ->route('admin.testimonials.page-settings.edit')
            ->with('message', 'Testimonials page settings updated successfully.');
    }
}
