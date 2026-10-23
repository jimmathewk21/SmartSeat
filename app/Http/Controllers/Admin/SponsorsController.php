<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroySponsorRequest;
use App\Http\Requests\StoreSponsorRequest;
use App\Http\Requests\UpdateSponsorRequest;
use App\Models\Sponsor;
use App\Models\SponsorCategory;
use Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SponsorsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('sponsor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Sponsor::with(['sponsorCategory', 'sponsorCategories'])
            ->select(sprintf('%s.*', (new Sponsor)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('sub_category', 'like', "%{$search}%")
                    ->orWhere('website_url', 'like', "%{$search}%")
                    ->orWhereHas('sponsorCategories', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('sponsor_category_id')) {
            $categoryId = (int) $request->input('sponsor_category_id');
            $query->whereHas('sponsorCategories', function ($categoryQuery) use ($categoryId) {
                $categoryQuery->where('sponsor_categories.id', $categoryId);
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('show_on_homepage')) {
            $query->where('show_on_homepage', $request->boolean('show_on_homepage'));
        }

        if ($request->filled('show_on_scrollbar')) {
            $query->where('show_on_scrollbar', $request->boolean('show_on_scrollbar'));
        }

        $allowedSorts = ['id', 'name', 'sub_category', 'sort_order', 'is_active', 'show_on_homepage', 'show_on_scrollbar', 'website_url'];
        $sortBy = $request->input('sort_by', 'sort_order');

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'sort_order';
        }

        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $sponsors = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id')
            ->paginate(25)
            ->appends($request->query());

        $sponsorCategories = SponsorCategory::query()
            ->select(['id', 'name'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.sponsors.index', compact('sponsors', 'sponsorCategories'));
    }

    public function create()
    {
        abort_if(Gate::denies('sponsor_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sponsorCategories = SponsorCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.sponsors.create', compact('sponsorCategories'));
    }

    public function store(StoreSponsorRequest $request)
    {
        $data = $request->validated();
        $data['logo_size'] = $data['logo_size'] ?? 'md';

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $directory = public_path('uploads/sponsors');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'sponsor-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['logo_url'] = 'uploads/sponsors/' . $fileName;
        }

        if ($request->hasFile('advert_file')) {
            $data['advert_url'] = $this->storeSponsorAssetFile($request->file('advert_file'), 'advert');
        }

        if ($request->hasFile('brochure_file')) {
            $data['brochure_url'] = $this->storeSponsorAssetFile($request->file('brochure_file'), 'brochure');
        }

        unset($data['logo_file']);
        unset($data['advert_file']);
        unset($data['brochure_file']);

        $sponsor = Sponsor::create($data);
        $this->syncSponsorCategories($sponsor, $request);

        return redirect()->route('admin.sponsors.index');
    }

    public function edit(Sponsor $sponsor)
    {
        abort_if(Gate::denies('sponsor_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sponsorCategories = SponsorCategory::query()
            ->where(function ($query) use ($sponsor) {
                $query->where('is_active', true)
                    ->orWhere('id', $sponsor->sponsor_category_id);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $sponsor->load(['sponsorCategory', 'sponsorCategories']);

        return view('admin.sponsors.edit', compact('sponsor', 'sponsorCategories'));
    }

    public function update(UpdateSponsorRequest $request, Sponsor $sponsor)
    {
        $data = $request->validated();
        $data['logo_size'] = $data['logo_size'] ?? ($sponsor->logo_size ?? 'md');

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $directory = public_path('uploads/sponsors');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'sponsor-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['logo_url'] = 'uploads/sponsors/' . $fileName;
        }

        if ($request->hasFile('advert_file')) {
            $data['advert_url'] = $this->storeSponsorAssetFile($request->file('advert_file'), 'advert');
        }

        if ($request->hasFile('brochure_file')) {
            $data['brochure_url'] = $this->storeSponsorAssetFile($request->file('brochure_file'), 'brochure');
        }

        unset($data['logo_file']);
        unset($data['advert_file']);
        unset($data['brochure_file']);

        $sponsor->update($data);
        $this->syncSponsorCategories($sponsor, $request);

        return redirect()->route('admin.sponsors.index');
    }

    public function show(Sponsor $sponsor)
    {
        abort_if(Gate::denies('sponsor_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sponsor->load(['sponsorCategory', 'sponsorCategories']);

        return view('admin.sponsors.show', compact('sponsor'));
    }

    public function destroy(Sponsor $sponsor)
    {
        abort_if(Gate::denies('sponsor_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sponsor->delete();

        return back();
    }

    public function massDestroy(MassDestroySponsorRequest $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back();
        }

        Sponsor::whereIn('id', $ids)->delete();

        return back();
    }

    public function bulkStatus(Request $request)
    {
        abort_if(Gate::denies('sponsor_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:sponsors,id'],
            'status' => ['required', 'in:publish,unpublish'],
        ]);

        $isActive = $validated['status'] === 'publish';
        Sponsor::query()->whereIn('id', $validated['ids'])->update(['is_active' => $isActive]);

        return back()->with('message', $isActive ? 'Selected sponsors published successfully.' : 'Selected sponsors unpublished successfully.');
    }

    public function reorder(Request $request)
    {
        abort_if(Gate::denies('sponsor_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'sort_orders' => ['required', 'array', 'min:1'],
            'sort_orders.*' => ['required', 'integer', 'min:0'],
        ]);

        $sortOrders = collect($validated['sort_orders'])
            ->mapWithKeys(function ($sortOrder, $id) {
                return [(int) $id => (int) $sortOrder];
            })
            ->filter(fn ($sortOrder, $id) => $id > 0 && $sortOrder >= 0);

        $existingIds = Sponsor::query()
            ->whereIn('id', $sortOrders->keys())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($existingIds) !== $sortOrders->count()) {
            return back()->with('message', 'Some sponsors could not be reordered.');
        }

        DB::transaction(function () use ($sortOrders) {
            foreach ($sortOrders as $id => $sortOrder) {
                Sponsor::query()
                    ->where('id', $id)
                    ->update(['sort_order' => $sortOrder]);
            }
        });

        return back()->with('message', 'Sponsor sorting order updated successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('sponsor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Sponsor::with(['sponsorCategory', 'sponsorCategories'])
            ->select(sprintf('%s.*', (new Sponsor)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('sub_category', 'like', "%{$search}%")
                    ->orWhere('website_url', 'like', "%{$search}%")
                    ->orWhereHas('sponsorCategories', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('sponsor_category_id')) {
            $categoryId = (int) $request->input('sponsor_category_id');
            $query->whereHas('sponsorCategories', function ($categoryQuery) use ($categoryId) {
                $categoryQuery->where('sponsor_categories.id', $categoryId);
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('show_on_homepage')) {
            $query->where('show_on_homepage', $request->boolean('show_on_homepage'));
        }

        if ($request->filled('show_on_scrollbar')) {
            $query->where('show_on_scrollbar', $request->boolean('show_on_scrollbar'));
        }

        $sponsors = $query->orderBy('sort_order')->orderBy('id')->get();
        $filename = 'sponsors-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($sponsors) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Primary Category', 'All Categories', 'Name', 'Sub Category', 'Website', 'LinkedIn', 'Logo URL', 'Logo Size', 'Show On Home', 'Show On Scroll Bar', 'Status']);

            foreach ($sponsors as $sponsor) {
                fputcsv($handle, [
                    $sponsor->id,
                    $sponsor->sponsorCategory?->name ?? '',
                    $sponsor->sponsorCategories->pluck('name')->implode(' | '),
                    $sponsor->name,
                    $sponsor->sub_category ?? '',
                    $sponsor->website_url ?? '',
                    $sponsor->linkedin_url ?? '',
                    $sponsor->logo_url ?? '',
                    $sponsor->logo_size ?? 'md',
                    $sponsor->show_on_homepage ? 'Yes' : 'No',
                    $sponsor->show_on_scrollbar ? 'Yes' : 'No',
                    $sponsor->is_active ? 'Published' : 'Unpublished',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function syncSponsorCategories(Sponsor $sponsor, Request $request): void
    {
        $selectedIds = collect((array) $request->input('sponsor_category_ids', []))
            ->filter(static fn ($value) => $value !== '' && $value !== null)
            ->map(static fn ($value) => (int) $value)
            ->unique()
            ->values();

        $primaryId = (int) ($request->input('sponsor_category_id') ?? 0);
        if ($primaryId > 0 && ! $selectedIds->contains($primaryId)) {
            $selectedIds->prepend($primaryId);
        }

        if ($selectedIds->isEmpty() && $primaryId > 0) {
            $selectedIds = collect([$primaryId]);
        }

        if ($selectedIds->isEmpty()) {
            return;
        }

        $sponsor->sponsorCategories()->sync($selectedIds->all());
        $sponsor->update(['sponsor_category_id' => $selectedIds->first()]);
    }

    private function storeSponsorAssetFile($file, string $prefix): string
    {
        $directory = public_path('uploads/sponsors');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = Str::lower($file->getClientOriginalExtension());
        $fileName = $prefix . '-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
        $file->move($directory, $fileName);

        return 'uploads/sponsors/' . $fileName;
    }
}
