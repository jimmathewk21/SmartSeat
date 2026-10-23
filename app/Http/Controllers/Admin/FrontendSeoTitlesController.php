<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrontendSeoTitle;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class FrontendSeoTitlesController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = FrontendSeoTitle::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('path', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $seoTitles = $query
            ->orderBy('path')
            ->paginate(25)
            ->appends($request->query());

        return view('admin.frontend.seo-titles.index', compact('seoTitles'));
    }

    public function create()
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.frontend.seo-titles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validatedData($request);
        FrontendSeoTitle::create($data);

        return redirect()
            ->route('admin.frontend.seo-titles.index')
            ->with('success', 'SEO title created successfully.');
    }

    public function edit(FrontendSeoTitle $seoTitle)
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.frontend.seo-titles.edit', compact('seoTitle'));
    }

    public function update(Request $request, FrontendSeoTitle $seoTitle): RedirectResponse
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validatedData($request, $seoTitle);
        $seoTitle->update($data);

        return redirect()
            ->route('admin.frontend.seo-titles.index')
            ->with('success', 'SEO title updated successfully.');
    }

    public function destroy(FrontendSeoTitle $seoTitle): RedirectResponse
    {
        abort_if(Gate::denies('setting_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $seoTitle->delete();

        return back()->with('success', 'SEO title deleted successfully.');
    }

    private function validatedData(Request $request, ?FrontendSeoTitle $seoTitle = null): array
    {
        $data = $request->validate([
            'path' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['path'] = FrontendSeoTitle::normalizePath($data['path']);
        $data['is_active'] = $request->boolean('is_active');

        $exists = FrontendSeoTitle::query()
            ->where('path', $data['path'])
            ->when($seoTitle, fn ($query) => $query->whereKeyNot($seoTitle->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'path' => 'This page link already has an SEO title.',
            ]);
        }

        return $data;
    }
}
