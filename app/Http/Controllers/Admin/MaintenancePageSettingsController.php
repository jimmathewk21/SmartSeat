<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\FrontendMaintenanceSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MaintenancePageSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = FrontendMaintenanceSettings::get();

        return view('admin.settings.maintenance-page', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'background_image' => ['nullable', 'string', 'max:255'],
            'background_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'badge_icon' => ['nullable', 'string', 'max:255'],
            'badge_icon_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg,ico', 'max:2048'],
            'title' => ['nullable', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:500'],
            'thanks_text' => ['required', 'string', 'max:255'],
            'inquiry_prefix' => ['required', 'string', 'max:255'],
            'inquiry_email' => ['nullable', 'email', 'max:255'],
        ]);

        $directory = public_path('uploads/maintenance-page');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $backgroundImage = trim((string) ($validated['background_image'] ?? ''));
        if ($request->hasFile('background_image_file')) {
            $file = $request->file('background_image_file');
            $fileName = 'maintenance-bg-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $backgroundImage = 'uploads/maintenance-page/' . $fileName;
        }

        $logoUrl = trim((string) ($validated['logo_url'] ?? ''));
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $fileName = 'maintenance-logo-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $logoUrl = 'uploads/maintenance-page/' . $fileName;
        }

        $badgeIcon = trim((string) ($validated['badge_icon'] ?? ''));
        if ($request->hasFile('badge_icon_file')) {
            $file = $request->file('badge_icon_file');
            $fileName = 'maintenance-badge-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $badgeIcon = 'uploads/maintenance-page/' . $fileName;
        }

        FrontendMaintenanceSettings::set([
            'enabled' => (bool) $validated['enabled'],
            'background_image' => $backgroundImage,
            'logo_url' => $logoUrl,
            'badge_icon' => $badgeIcon,
            'title' => trim((string) ($validated['title'] ?? '')),
            'headline' => (string) $validated['headline'],
            'thanks_text' => (string) $validated['thanks_text'],
            'inquiry_prefix' => (string) $validated['inquiry_prefix'],
            'inquiry_email' => trim((string) ($validated['inquiry_email'] ?? '')),
        ]);

        return redirect()
            ->route('admin.settings.maintenance-page.edit')
            ->with('message', 'Maintenance page settings updated successfully.');
    }
}
