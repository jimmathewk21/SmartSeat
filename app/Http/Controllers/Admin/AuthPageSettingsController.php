<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AuthPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = AuthPageSettings::get();

        return view('admin.frontend.auth-page', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'brand_image_url' => ['nullable', 'string', 'max:255'],
            'brand_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'signup_left_content' => ['required', 'string'],
            'login_left_content' => ['required', 'string'],
            'verification_note' => ['required', 'string'],
            'ip_label' => ['required', 'string', 'max:80'],
            'location_label' => ['nullable', 'string', 'max:80'],
            'location_value' => ['nullable', 'string', 'max:120'],
            'signup_form_title' => ['required', 'string', 'max:120'],
            'signup_form_subtitle' => ['required', 'string', 'max:220'],
            'signup_button_text' => ['required', 'string', 'max:80'],
            'login_form_title' => ['required', 'string', 'max:120'],
            'login_form_subtitle' => ['required', 'string', 'max:220'],
            'login_button_text' => ['required', 'string', 'max:80'],
        ]);

        if ($request->hasFile('brand_image_file')) {
            $image = $request->file('brand_image_file');
            $directory = public_path('uploads/auth-page');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $fileName = 'auth-brand-' . now()->format('YmdHis') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move($directory, $fileName);
            $validated['brand_image_url'] = 'uploads/auth-page/' . $fileName;
        }

        unset($validated['brand_image_file']);

        AuthPageSettings::set($validated);

        return redirect()
            ->route('admin.frontend.auth-page.edit')
            ->with('message', 'Auth page settings updated successfully.');
    }
}
