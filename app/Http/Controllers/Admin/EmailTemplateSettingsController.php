<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminEmailTemplateSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailTemplateSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.settings.email-templates', [
            'settings' => AdminEmailTemplateSettings::get(),
            'definitions' => AdminEmailTemplateSettings::definitions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $definitions = AdminEmailTemplateSettings::definitions();
        $rules = [];

        foreach (array_keys($definitions) as $key) {
            $rules["templates.$key.enabled"] = ['nullable', 'boolean'];
            $rules["templates.$key.subject"] = ['required', 'string', 'max:255'];
            $rules["templates.$key.body"] = ['required', 'string', 'max:20000'];
        }

        $validated = $request->validate($rules);
        $templates = [];

        foreach (array_keys($definitions) as $key) {
            $templates[$key] = [
                'enabled' => (bool) data_get($validated, "templates.$key.enabled", false),
                'subject' => (string) data_get($validated, "templates.$key.subject", ''),
                'body' => (string) data_get($validated, "templates.$key.body", ''),
            ];
        }

        AdminEmailTemplateSettings::set(['templates' => $templates]);

        return redirect()
            ->route('admin.settings.email-templates.edit')
            ->with('message', 'Admin email templates updated successfully.');
    }
}
