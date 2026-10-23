<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ContactPageSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactPageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = ContactPageSettings::get();

        return view('admin.frontend.contact', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'hero_label' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_title_suffix' => ['nullable', 'string', 'max:120'],
            'hero_description' => ['required', 'string'],
            'organizer_heading' => ['required', 'string', 'max:120'],
            'organizer_logo_url' => ['nullable', 'string', 'max:255'],
            'organizer_logo_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'address_heading' => ['required', 'string', 'max:120'],
            'address_text' => ['required', 'string'],
            'contact_heading' => ['required', 'string', 'max:120'],
            'contact_email' => ['required', 'string', 'max:160'],
            'contact_phone' => ['required', 'string', 'max:120'],
            'hours_heading' => ['required', 'string', 'max:120'],
            'hours_text' => ['required', 'string'],
            'social_heading' => ['required', 'string', 'max:120'],
            'social_linkedin_url' => ['nullable', 'string', 'max:255'],
            'social_facebook_url' => ['nullable', 'string', 'max:255'],
            'social_x_url' => ['nullable', 'string', 'max:255'],
            'social_instagram_url' => ['nullable', 'string', 'max:255'],
            'social_whatsapp_url' => ['nullable', 'string', 'max:255'],
            'form_title' => ['required', 'string', 'max:120'],
            'form_name_placeholder' => ['required', 'string', 'max:120'],
            'form_email_placeholder' => ['required', 'string', 'max:120'],
            'form_subject_placeholder' => ['required', 'string', 'max:120'],
            'form_message_placeholder' => ['required', 'string', 'max:160'],
            'form_button_text' => ['required', 'string', 'max:80'],
            'form_success_message' => ['required', 'string', 'max:500'],
            'testimonial_title' => ['required', 'string', 'max:120'],
        ]);

        if ($request->hasFile('organizer_logo_file')) {
            $image = $request->file('organizer_logo_file');
            $directory = public_path('uploads/contact-page');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $fileName = 'organizer-logo-' . now()->format('YmdHis') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move($directory, $fileName);
            $validated['organizer_logo_url'] = 'uploads/contact-page/' . $fileName;
        }

        unset($validated['organizer_logo_file']);

        ContactPageSettings::set($validated);

        return redirect()
            ->route('admin.frontend.contact.edit')
            ->with('message', 'Contact page settings updated successfully.');
    }
}
