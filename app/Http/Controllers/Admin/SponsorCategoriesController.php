<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroySponsorCategoryRequest;
use App\Http\Requests\StoreSponsorCategoryRequest;
use App\Http\Requests\UpdateSponsorCategoryRequest;
use App\Models\SponsorCategory;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SponsorCategoriesController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('sponsor_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = SponsorCategory::query()->select(sprintf('%s.*', (new SponsorCategory)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $allowedSorts = ['id', 'name', 'sort_order', 'is_active'];
        $sortBy = $request->input('sort_by', 'sort_order');

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'sort_order';
        }

        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $sponsorCategories = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id')
            ->paginate(25)
            ->appends($request->query());

        return view('admin.sponsorCategories.index', compact('sponsorCategories'));
    }

    public function create()
    {
        abort_if(Gate::denies('sponsor_category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.sponsorCategories.create');
    }

    public function store(StoreSponsorCategoryRequest $request)
    {
        SponsorCategory::create($request->validated());

        return redirect()->route('admin.sponsor-categories.index');
    }

    public function edit(SponsorCategory $sponsor_category)
    {
        abort_if(Gate::denies('sponsor_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.sponsorCategories.edit', compact('sponsor_category'));
    }

    public function update(UpdateSponsorCategoryRequest $request, SponsorCategory $sponsor_category)
    {
        $sponsor_category->update($request->validated());

        return redirect()->route('admin.sponsor-categories.index');
    }

    public function show(SponsorCategory $sponsor_category)
    {
        abort_if(Gate::denies('sponsor_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sponsor_category->load('sponsors');

        return view('admin.sponsorCategories.show', compact('sponsor_category'));
    }

    public function destroy(SponsorCategory $sponsor_category)
    {
        abort_if(Gate::denies('sponsor_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sponsor_category->delete();

        return back();
    }

    public function massDestroy(MassDestroySponsorCategoryRequest $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back();
        }

        SponsorCategory::whereIn('id', $ids)->delete();

        return back();
    }
}
