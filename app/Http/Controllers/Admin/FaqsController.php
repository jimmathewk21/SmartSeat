<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyFaqRequest;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Models\Faq;
use App\Models\FaqCategory;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class FaqsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('faq_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Faq::with(['category'])->select(sprintf('%s.*', (new Faq)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('faq_category_id')) {
            $query->where('faq_category_id', $request->input('faq_category_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('show_on_homepage')) {
            $query->where('show_on_homepage', $request->boolean('show_on_homepage'));
        }

        $allowedSorts = ['id', 'question', 'sort_order', 'is_active', 'show_on_homepage'];
        $sortBy = $request->input('sort_by', 'sort_order');
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'sort_order';
        }
        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $faqs = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id')
            ->paginate(25)
            ->appends($request->query());

        $faqCategories = FaqCategory::query()->select(['id', 'name'])->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.faqs.index', compact('faqs', 'faqCategories'));
    }

    public function create()
    {
        abort_if(Gate::denies('faq_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $faqCategories = FaqCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.faqs.create', compact('faqCategories'));
    }

    public function store(StoreFaqRequest $request)
    {
        Faq::create(array_merge($request->validated(), ['event_id' => null]));

        return redirect()->route('admin.faqs.index');
    }

    public function edit(Faq $faq)
    {
        abort_if(Gate::denies('faq_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $faqCategories = FaqCategory::query()
            ->where(function ($query) use ($faq) {
                $query->where('is_active', true)
                    ->orWhere('id', $faq->faq_category_id);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $faq->load(['category']);

        return view('admin.faqs.edit', compact('faq', 'faqCategories'));
    }

    public function update(UpdateFaqRequest $request, Faq $faq)
    {
        $faq->update(array_merge($request->validated(), ['event_id' => null]));

        return redirect()->route('admin.faqs.index');
    }

    public function show(Faq $faq)
    {
        abort_if(Gate::denies('faq_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $faq->load(['category']);

        return view('admin.faqs.show', compact('faq'));
    }

    public function destroy(Faq $faq)
    {
        abort_if(Gate::denies('faq_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $faq->delete();

        return back();
    }

    public function massDestroy(MassDestroyFaqRequest $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back();
        }

        Faq::whereIn('id', $ids)->delete();

        return back();
    }

    public function bulkStatus(Request $request)
    {
        abort_if(Gate::denies('faq_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:faqs,id'],
            'status' => ['required', 'in:publish,unpublish'],
        ]);

        $isActive = $validated['status'] === 'publish';
        Faq::query()->whereIn('id', $validated['ids'])->update(['is_active' => $isActive]);

        return back()->with('message', $isActive ? 'Selected FAQs published successfully.' : 'Selected FAQs unpublished successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('faq_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Faq::with(['category'])->select(sprintf('%s.*', (new Faq)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('faq_category_id')) {
            $query->where('faq_category_id', $request->input('faq_category_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('show_on_homepage')) {
            $query->where('show_on_homepage', $request->boolean('show_on_homepage'));
        }

        $filename = 'faqs-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Category',
                'Question',
                'Answer',
                'Show On Home Page',
                'Status',
                'Sort Order',
            ]);

            $query->orderBy('sort_order')->orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $faq) {
                    fputcsv($handle, [
                        $faq->id,
                        $faq->category?->name ?? '',
                        $faq->question,
                        $faq->answer,
                        $faq->show_on_homepage ? 'Yes' : 'No',
                        $faq->is_active ? 'Published' : 'Unpublished',
                        $faq->sort_order,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
