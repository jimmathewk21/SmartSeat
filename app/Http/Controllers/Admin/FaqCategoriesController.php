<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyFaqCategoryRequest;
use App\Http\Requests\StoreFaqCategoryRequest;
use App\Http\Requests\UpdateFaqCategoryRequest;
use App\Models\FaqCategory;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FaqCategoriesController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('faq_category_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = FaqCategory::query()->select(sprintf('%s.*', (new FaqCategory)->table));

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
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'sort_order';
        }
        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $faqCategories = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id')
            ->paginate(25)
            ->appends($request->query());

        return view('admin.faqCategories.index', compact('faqCategories'));
    }

    public function create()
    {
        abort_if(Gate::denies('faq_category_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.faqCategories.create');
    }

    public function store(StoreFaqCategoryRequest $request)
    {
        FaqCategory::create($request->validated());

        return redirect()->route('admin.faq-categories.index');
    }

    public function edit(FaqCategory $faq_category)
    {
        abort_if(Gate::denies('faq_category_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.faqCategories.edit', compact('faq_category'));
    }

    public function update(UpdateFaqCategoryRequest $request, FaqCategory $faq_category)
    {
        $faq_category->update($request->validated());

        return redirect()->route('admin.faq-categories.index');
    }

    public function show(FaqCategory $faq_category)
    {
        abort_if(Gate::denies('faq_category_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $faq_category->load('faqs');

        return view('admin.faqCategories.show', compact('faq_category'));
    }

    public function destroy(FaqCategory $faq_category)
    {
        abort_if(Gate::denies('faq_category_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $faq_category->delete();

        return back();
    }

    public function massDestroy(MassDestroyFaqCategoryRequest $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back();
        }

        FaqCategory::whereIn('id', $ids)->delete();

        return back();
    }
}

