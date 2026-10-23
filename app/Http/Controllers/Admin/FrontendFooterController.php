<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\FrontendFooterSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FrontendFooterController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $footer = FrontendFooterSettings::get();

        return view('admin.frontend.footer', compact('footer'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'logo_url' => ['nullable', 'string', 'max:255'],
            'logo_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],

            'social_links' => ['nullable', 'array'],
            'social_links.*.icon' => ['nullable', 'string', 'max:100'],
            'social_links.*.url' => ['nullable', 'string', 'max:255'],

            'upcoming_events_title' => ['nullable', 'string', 'max:100'],
            'upcoming_events' => ['nullable', 'array'],
            'upcoming_events.*.datetime' => ['nullable', 'string', 'max:255'],
            'upcoming_events.*.name' => ['nullable', 'string', 'max:255'],
            'upcoming_events.*.cta_text' => ['nullable', 'string', 'max:100'],
            'upcoming_events.*.cta_link' => ['nullable', 'string', 'max:255'],

            'quick_links_title' => ['nullable', 'string', 'max:100'],
            'quick_links' => ['nullable', 'array'],
            'quick_links.*.text' => ['nullable', 'string', 'max:100'],
            'quick_links.*.url' => ['nullable', 'string', 'max:255'],

            'company_links_title' => ['nullable', 'string', 'max:100'],
            'company_links' => ['nullable', 'array'],
            'company_links.*.text' => ['nullable', 'string', 'max:100'],
            'company_links.*.url' => ['nullable', 'string', 'max:255'],

            'contact_title' => ['nullable', 'string', 'max:100'],
            'contact_email' => ['nullable', 'string', 'max:255'],
            'contact_email_icon' => ['nullable', 'string', 'max:100'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'contact_address_icon' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_phone_icon' => ['nullable', 'string', 'max:100'],

            'copyright_text' => ['nullable', 'string', 'max:255'],
            'bottom_links' => ['nullable', 'array'],
            'bottom_links.*.text' => ['nullable', 'string', 'max:100'],
            'bottom_links.*.url' => ['nullable', 'string', 'max:255'],
        ]);

        $logoUrl = trim((string) ($validated['logo_url'] ?? ''));
        if ($request->hasFile('logo_image')) {
            $image = $request->file('logo_image');
            $directory = public_path('uploads/frontend-footer');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($image->getClientOriginalExtension());
            $fileName = 'footer-logo-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $image->move($directory, $fileName);
            $logoUrl = 'uploads/frontend-footer/' . $fileName;
        }

        unset($validated['logo_image']);

        $payload = array_merge($validated, [
            'logo_url' => $logoUrl,
            'social_links' => $this->normalizeSocialLinks($validated['social_links'] ?? []),
            'upcoming_events' => $this->normalizeEvents($validated['upcoming_events'] ?? []),
            'quick_links' => $this->normalizeSimpleLinks($validated['quick_links'] ?? []),
            'company_links' => $this->normalizeSimpleLinks($validated['company_links'] ?? []),
            'bottom_links' => $this->normalizeSimpleLinks($validated['bottom_links'] ?? []),
        ]);

        FrontendFooterSettings::set($payload);

        return redirect()
            ->route('admin.frontend.footer.edit')
            ->with('message', 'Frontend footer updated successfully.');
    }

    private function normalizeSocialLinks(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $icon = trim((string) ($row['icon'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));
            if ($icon === '' && $url === '') {
                continue;
            }

            $normalized[] = [
                'icon' => $icon !== '' ? $icon : 'fa-solid fa-link',
                'url' => $url,
            ];
        }

        return $normalized;
    }

    private function normalizeEvents(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $datetime = trim((string) ($row['datetime'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $ctaText = trim((string) ($row['cta_text'] ?? ''));
            $ctaLink = trim((string) ($row['cta_link'] ?? ''));

            if ($datetime === '' && $name === '' && $ctaText === '' && $ctaLink === '') {
                continue;
            }

            $normalized[] = [
                'datetime' => $datetime,
                'name' => $name,
                'cta_text' => $ctaText,
                'cta_link' => $ctaLink,
            ];
        }

        return $normalized;
    }

    private function normalizeSimpleLinks(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $text = trim((string) ($row['text'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));

            if ($text === '' && $url === '') {
                continue;
            }

            $normalized[] = [
                'text' => $text,
                'url' => $url,
            ];
        }

        return $normalized;
    }
}

