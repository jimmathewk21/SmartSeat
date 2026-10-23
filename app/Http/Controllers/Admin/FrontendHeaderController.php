<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\FrontendHeaderSettings;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FrontendHeaderController extends Controller
{
    public function edit()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $header = FrontendHeaderSettings::get();
        $menuRows = collect($header['menu_items'])
            ->map(fn (array $item) => $this->decomposeLink($item['link']))
            ->map(function (array $linkParts, int $index) use ($header) {
                $item = $header['menu_items'][$index];

                return [
                    'name' => $item['name'],
                    'page' => $linkParts['page'],
                    'custom_url' => $linkParts['custom_url'],
                ];
            })
            ->values()
            ->all();

        $buttonParts = $this->decomposeLink((string) ($header['button_link'] ?? ''));

        return view('admin.frontend.header', [
            'header' => $header,
            'menuRows' => $menuRows,
            'buttonPage' => $buttonParts['page'],
            'buttonCustomUrl' => $buttonParts['custom_url'],
            'pageOptions' => FrontendHeaderSettings::pageOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'logo_url' => ['nullable', 'string', 'max:255'],
            'logo_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
            'menu_items' => ['nullable', 'array'],
            'menu_items.*.name' => ['nullable', 'string', 'max:100'],
            'menu_items.*.page' => ['nullable', 'string', 'max:255'],
            'menu_items.*.custom_url' => ['nullable', 'url', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_page' => ['nullable', 'string', 'max:255'],
            'button_custom_url' => ['nullable', 'url', 'max:255'],
        ]);

        $menuItems = collect($validated['menu_items'] ?? [])
            ->map(function (array $item) {
                $name = trim((string) ($item['name'] ?? ''));
                $link = $this->resolveLink(
                    (string) ($item['page'] ?? ''),
                    (string) ($item['custom_url'] ?? '')
                );

                if ($name === '' || $link === '') {
                    return null;
                }

                return [
                    'name' => $name,
                    'link' => $link,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $buttonLink = $this->resolveLink(
            (string) ($validated['button_page'] ?? ''),
            (string) ($validated['button_custom_url'] ?? '')
        );

        $logoUrl = trim((string) ($validated['logo_url'] ?? ''));
        if ($request->hasFile('logo_image')) {
            $file = $request->file('logo_image');
            $directory = public_path('uploads/frontend-header');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $fileName = 'logo-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $logoUrl = 'uploads/frontend-header/' . $fileName;
        }

        FrontendHeaderSettings::set([
            'logo_url' => $logoUrl,
            'menu_items' => $menuItems,
            'button_text' => (string) ($validated['button_text'] ?? ''),
            'button_link' => $buttonLink,
        ]);

        return redirect()
            ->route('admin.frontend.header.edit')
            ->with('message', 'Frontend header updated successfully.');
    }

    private function resolveLink(string $page, string $customUrl): string
    {
        if ($page === 'custom') {
            return trim($customUrl);
        }

        return trim($page);
    }

    private function decomposeLink(string $link): array
    {
        $link = trim($link);

        if ($link === '') {
            return ['page' => '#section-hero', 'custom_url' => ''];
        }

        $options = FrontendHeaderSettings::pageOptions();

        if (array_key_exists($link, $options) && $link !== 'custom') {
            return ['page' => $link, 'custom_url' => ''];
        }

        return ['page' => 'custom', 'custom_url' => $link];
    }
}
