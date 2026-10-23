<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MyContactPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MyContactPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = MyContactPageSettings::get();

        return view('admin.myContacts.page-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'page_title' => ['required', 'string', 'max:120'],
            'subtitle' => ['required', 'string', 'max:120'],
            'subtitle_suffix' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'disclaimer_button_text' => ['required', 'string', 'max:120'],
            'form_button_text' => ['required', 'string', 'max:120'],
            'form_button_link' => ['required', 'string', 'max:255'],
            'notification_email' => ['nullable', 'email', 'max:255'],
            'guidelines_content' => ['required', 'string'],
            'apply_page_label' => ['required', 'string', 'max:120'],
            'apply_page_title' => ['required', 'string', 'max:120'],
            'apply_page_title_suffix' => ['nullable', 'string', 'max:120'],
            'apply_page_description' => ['required', 'string'],
            'country_options' => ['nullable', 'string'],
            'specialization_options' => ['nullable', 'string'],
            'speaker_tag_options' => ['nullable', 'string'],
            'experience_options' => ['nullable', 'string'],
        ]);

        foreach (['country_options', 'specialization_options', 'speaker_tag_options', 'experience_options'] as $key) {
            $validated[$key] = implode(PHP_EOL, MyContactPageSettings::parseOptionLines($validated[$key] ?? ''));
        }

        MyContactPageSettings::set($validated);

        return redirect()
            ->route('admin.my-contacts.page-settings.edit')
            ->with('message', 'My Contacts page settings updated successfully.');
    }
}
