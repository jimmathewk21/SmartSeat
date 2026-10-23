<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteMetaSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SiteSettingsController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $settings = SiteMetaSettings::get();

        return view('admin.settings.site-meta', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'default_title' => ['required', 'string', 'max:255'],
            'site_icon' => ['nullable', 'string', 'max:255'],
            'site_icon_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg,ico', 'max:2048'],
            'admin_site_icon' => ['nullable', 'string', 'max:255'],
            'admin_site_icon_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg,ico', 'max:2048'],
            'pages' => ['nullable', 'array'],
            'pages.*.path' => ['nullable', 'string', 'max:255'],
            'pages.*.title' => ['nullable', 'string', 'max:255'],
            'restricted_paths' => ['nullable', 'array'],
            'restricted_paths.*' => ['nullable', 'string', 'max:255'],
        ]);

        $directory = public_path('uploads/site-settings');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $siteIcon = trim((string) ($validated['site_icon'] ?? ''));
        if ($request->hasFile('site_icon_file')) {
            $file = $request->file('site_icon_file');
            $fileName = 'site-icon-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $siteIcon = 'uploads/site-settings/' . $fileName;
        }

        $adminSiteIcon = trim((string) ($validated['admin_site_icon'] ?? ''));
        if ($request->hasFile('admin_site_icon_file')) {
            $file = $request->file('admin_site_icon_file');
            $fileName = 'admin-icon-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $adminSiteIcon = 'uploads/site-settings/' . $fileName;
        }

        $pages = collect($validated['pages'] ?? [])
            ->map(function (array $item) {
                $path = SiteMetaSettings::normalizePath((string) ($item['path'] ?? ''));
                $title = trim((string) ($item['title'] ?? ''));

                if ($title === '') {
                    return null;
                }

                return [
                    'path' => $path,
                    'title' => $title,
                ];
            })
            ->filter()
            ->unique('path')
            ->values()
            ->all();

        $restrictedPaths = collect($validated['restricted_paths'] ?? [])
            ->map(fn ($path) => SiteMetaSettings::normalizePath((string) $path))
            ->filter()
            ->unique()
            ->values()
            ->all();

        SiteMetaSettings::set([
            'default_title' => (string) $validated['default_title'],
            'site_icon' => $siteIcon,
            'admin_site_icon' => $adminSiteIcon,
            'pages' => $pages,
            'restricted_paths' => $restrictedPaths,
        ]);

        return redirect()->route('admin.settings.site-meta.edit')
            ->with('message', 'Site settings updated successfully.');
    }
}
