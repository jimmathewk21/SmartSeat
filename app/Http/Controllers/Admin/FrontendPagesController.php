<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyPageRequest;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FrontendPagesController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Page::query()->select(sprintf('%s.*', (new Page)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $pages = $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(25)
            ->appends($request->query());

        return view('admin.frontend.pages.index', compact('pages'));
    }

    public function create()
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.frontend.pages.create');
    }

    public function store(StorePageRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug']);
        Page::create($data);

        return redirect()->route('admin.frontend.pages.index');
    }

    public function edit(Page $page)
    {
        abort_if(Gate::denies('setting_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.frontend.pages.edit', compact('page'));
    }

    public function update(UpdatePageRequest $request, Page $page)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug']);
        $page->update($data);

        return redirect()->route('admin.frontend.pages.index');
    }

    public function show(Page $page)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.frontend.pages.show', compact('page'));
    }

    public function destroy(Page $page)
    {
        abort_if(Gate::denies('setting_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $page->delete();

        return back();
    }

    public function massDestroy(MassDestroyPageRequest $request)
    {
        $ids = $request->input('ids', []);
        if (! empty($ids)) {
            Page::whereIn('id', $ids)->delete();
        }

        return back();
    }
}

