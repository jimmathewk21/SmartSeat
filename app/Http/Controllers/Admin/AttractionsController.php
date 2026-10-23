<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAttractionRequest;
use App\Http\Requests\StoreAttractionRequest;
use App\Http\Requests\UpdateAttractionRequest;
use App\Models\Attraction;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class AttractionsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('attraction_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Attraction::query()->select(sprintf('%s.*', (new Attraction)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('detail', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('show_on_homepage')) {
            $query->where('show_on_homepage', $request->boolean('show_on_homepage'));
        }

        $allowedSorts = ['id', 'name', 'sort_order', 'is_active', 'show_on_homepage'];
        $sortBy = $request->input('sort_by', 'sort_order');

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'sort_order';
        }

        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $attractions = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id')
            ->paginate(25)
            ->appends($request->query());

        $categories = Attraction::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.attractions.index', compact('attractions', 'categories'));
    }

    public function create()
    {
        abort_if(Gate::denies('attraction_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.attractions.create');
    }

    public function store(StoreAttractionRequest $request)
    {
        Attraction::create($this->preparePayload($request, $request->validated()));

        return redirect()->route('admin.attractions.index');
    }

    public function edit(Attraction $attraction)
    {
        abort_if(Gate::denies('attraction_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.attractions.edit', compact('attraction'));
    }

    public function update(UpdateAttractionRequest $request, Attraction $attraction)
    {
        $attraction->update($this->preparePayload($request, $request->validated()));

        return redirect()->route('admin.attractions.index');
    }

    public function show(Attraction $attraction)
    {
        abort_if(Gate::denies('attraction_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.attractions.show', compact('attraction'));
    }

    public function destroy(Attraction $attraction)
    {
        abort_if(Gate::denies('attraction_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $attraction->delete();

        return back();
    }

    public function massDestroy(MassDestroyAttractionRequest $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back();
        }

        Attraction::whereIn('id', $ids)->delete();

        return back();
    }

    public function bulkStatus(Request $request)
    {
        abort_if(Gate::denies('attraction_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:attractions,id'],
            'status' => ['required', 'in:publish,unpublish'],
        ]);

        $isActive = $validated['status'] === 'publish';
        Attraction::query()->whereIn('id', $validated['ids'])->update(['is_active' => $isActive]);

        return back()->with('message', $isActive ? 'Selected attractions published successfully.' : 'Selected attractions unpublished successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('attraction_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Attraction::query()->select(sprintf('%s.*', (new Attraction)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('detail', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('show_on_homepage')) {
            $query->where('show_on_homepage', $request->boolean('show_on_homepage'));
        }

        $filename = 'attractions-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Category',
                'Place Name',
                'Detail',
                'Display Order',
                'Show On Home Page',
                'Status',
            ]);

            $query->orderBy('sort_order')->orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $attraction) {
                    fputcsv($handle, [
                        $attraction->id,
                        $attraction->category ?? '',
                        $attraction->name,
                        $attraction->detail,
                        $attraction->sort_order,
                        $attraction->show_on_homepage ? 'Yes' : 'No',
                        $attraction->is_active ? 'Published' : 'Unpublished',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function preparePayload(Request $request, array $validated): array
    {
        if ($request->hasFile('image_file')) {
            $image = $request->file('image_file');
            $directory = public_path('uploads/attractions');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($image->getClientOriginalExtension());
            $fileName = 'attraction-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $image->move($directory, $fileName);

            $validated['image_url'] = 'uploads/attractions/' . $fileName;
        }

        return $validated;
    }
}
