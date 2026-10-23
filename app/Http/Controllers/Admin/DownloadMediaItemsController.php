<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DownloadMediaItem;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class DownloadMediaItemsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $items = DownloadMediaItem::query()
            ->when($request->filled('audience_type'), function ($query) use ($request) {
                $query->where('audience_type', $request->input('audience_type'));
            })
            ->orderByRaw("CASE audience_type WHEN 'general' THEN 1 WHEN 'speaker' THEN 2 WHEN 'sponsor' THEN 3 WHEN 'delegate' THEN 4 ELSE 5 END")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.downloadMediaItems.index', compact('items'));
    }

    public function create()
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.downloadMediaItems.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DownloadMediaItem::create($this->preparePayload($request, $this->validateRequest($request)));

        return redirect()->route('admin.download-media-items.index');
    }

    public function edit(DownloadMediaItem $downloadMediaItem)
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.downloadMediaItems.edit', compact('downloadMediaItem'));
    }

    public function update(Request $request, DownloadMediaItem $downloadMediaItem): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $downloadMediaItem->update($this->preparePayload($request, $this->validateRequest($request)));

        return redirect()->route('admin.download-media-items.index');
    }

    public function destroy(DownloadMediaItem $downloadMediaItem): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $downloadMediaItem->delete();

        return back();
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:download_media_items,id'],
        ]);

        DownloadMediaItem::query()
            ->whereIn('id', $validated['ids'])
            ->delete();

        return redirect()
            ->route('admin.download-media-items.index')
            ->with('status', 'Selected download items deleted successfully.');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:download_media_items,id'],
            'status' => ['required', Rule::in(['publish', 'unpublish'])],
        ]);

        $isActive = $validated['status'] === 'publish';

        DownloadMediaItem::query()
            ->whereIn('id', $validated['ids'])
            ->update(['is_active' => $isActive]);

        return redirect()
            ->route('admin.download-media-items.index')
            ->with('status', $isActive
                ? 'Selected download items published successfully.'
                : 'Selected download items unpublished successfully.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('setting_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'sort_orders' => ['required', 'array', 'min:1'],
            'sort_orders.*' => ['required', 'integer', 'min:1'],
        ]);

        $sortOrders = collect($validated['sort_orders'])
            ->mapWithKeys(function ($sortOrder, $id) {
                return [(int) $id => (int) $sortOrder];
            })
            ->filter(fn ($sortOrder, $id) => $id > 0 && $sortOrder > 0);

        $existingIds = DownloadMediaItem::query()
            ->whereIn('id', $sortOrders->keys())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($existingIds) !== $sortOrders->count()) {
            return redirect()
                ->route('admin.download-media-items.index')
                ->with('status', 'Some download items could not be reordered.');
        }

        DB::transaction(function () use ($sortOrders) {
            foreach ($sortOrders as $id => $sortOrder) {
                DownloadMediaItem::query()
                    ->where('id', $id)
                    ->update(['sort_order' => $sortOrder]);
            }
        });

        return redirect()
            ->route('admin.download-media-items.index')
            ->with('status', 'Download item order updated.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'section_title' => ['required', 'string', 'max:160'],
            'audience_type' => ['required', Rule::in([
                DownloadMediaItem::AUDIENCE_SPEAKER,
                DownloadMediaItem::AUDIENCE_SPONSOR,
                DownloadMediaItem::AUDIENCE_DELEGATE,
                DownloadMediaItem::AUDIENCE_GENERAL,
            ])],
            'item_title' => ['required', 'string', 'max:160'],
            'item_subtitle' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'primary_button_text' => ['nullable', 'string', 'max:40'],
            'primary_file_url' => ['nullable', 'string', 'max:255'],
            'primary_file_upload' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg,webp,doc,docx,ppt,pptx', 'max:10240'],
            'secondary_button_text' => ['nullable', 'string', 'max:40'],
            'secondary_file_url' => ['nullable', 'string', 'max:255'],
            'secondary_file_upload' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg,webp,doc,docx,ppt,pptx', 'max:10240'],
            'is_grouped_versions' => ['nullable', 'boolean'],
            'version_1_label' => ['nullable', 'string', 'max:80'],
            'version_1_image_url' => ['nullable', 'string', 'max:255'],
            'version_1_image_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'version_1_pdf_url' => ['nullable', 'string', 'max:255'],
            'version_1_pdf_upload' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'version_1_png_url' => ['nullable', 'string', 'max:255'],
            'version_1_png_upload' => ['nullable', 'file', 'mimes:png', 'max:10240'],
            'version_1_jpg_url' => ['nullable', 'string', 'max:255'],
            'version_1_jpg_upload' => ['nullable', 'file', 'mimes:jpg,jpeg', 'max:10240'],
            'version_2_label' => ['nullable', 'string', 'max:80'],
            'version_2_image_url' => ['nullable', 'string', 'max:255'],
            'version_2_image_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'version_2_pdf_url' => ['nullable', 'string', 'max:255'],
            'version_2_pdf_upload' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'version_2_png_url' => ['nullable', 'string', 'max:255'],
            'version_2_png_upload' => ['nullable', 'file', 'mimes:png', 'max:10240'],
            'version_2_jpg_url' => ['nullable', 'string', 'max:255'],
            'version_2_jpg_upload' => ['nullable', 'file', 'mimes:jpg,jpeg', 'max:10240'],
            'version_3_label' => ['nullable', 'string', 'max:80'],
            'version_3_image_url' => ['nullable', 'string', 'max:255'],
            'version_3_image_upload' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'version_3_pdf_url' => ['nullable', 'string', 'max:255'],
            'version_3_pdf_upload' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'version_3_png_url' => ['nullable', 'string', 'max:255'],
            'version_3_png_upload' => ['nullable', 'file', 'mimes:png', 'max:10240'],
            'version_3_jpg_url' => ['nullable', 'string', 'max:255'],
            'version_3_jpg_upload' => ['nullable', 'file', 'mimes:jpg,jpeg', 'max:10240'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function preparePayload(Request $request, array $validated): array
    {
        if ($request->hasFile('image_file')) {
            $validated['image_url'] = $this->moveUploadedFile($request->file('image_file'), 'download-page');
        }

        if ($request->hasFile('primary_file_upload')) {
            $validated['primary_file_url'] = $this->moveUploadedFile($request->file('primary_file_upload'), 'download-page');
        }

        if ($request->hasFile('secondary_file_upload')) {
            $validated['secondary_file_url'] = $this->moveUploadedFile($request->file('secondary_file_upload'), 'download-page');
        }

        foreach ([1, 2, 3] as $version) {
            if ($request->hasFile("version_{$version}_image_upload")) {
                $validated["version_{$version}_image_url"] = $this->moveUploadedFile($request->file("version_{$version}_image_upload"), 'download-page');
            }
            if ($request->hasFile("version_{$version}_pdf_upload")) {
                $validated["version_{$version}_pdf_url"] = $this->moveUploadedFile($request->file("version_{$version}_pdf_upload"), 'download-page');
            }
            if ($request->hasFile("version_{$version}_png_upload")) {
                $validated["version_{$version}_png_url"] = $this->moveUploadedFile($request->file("version_{$version}_png_upload"), 'download-page');
            }
            if ($request->hasFile("version_{$version}_jpg_upload")) {
                $validated["version_{$version}_jpg_url"] = $this->moveUploadedFile($request->file("version_{$version}_jpg_upload"), 'download-page');
            }
        }

        unset(
            $validated['image_file'],
            $validated['primary_file_upload'],
            $validated['secondary_file_upload'],
            $validated['version_1_image_upload'],
            $validated['version_1_pdf_upload'],
            $validated['version_1_png_upload'],
            $validated['version_1_jpg_upload'],
            $validated['version_2_image_upload'],
            $validated['version_2_pdf_upload'],
            $validated['version_2_png_upload'],
            $validated['version_2_jpg_upload'],
            $validated['version_3_image_upload'],
            $validated['version_3_pdf_upload'],
            $validated['version_3_png_upload'],
            $validated['version_3_jpg_upload'],
        );

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_grouped_versions'] = $request->boolean('is_grouped_versions');

        return $validated;
    }

    private function moveUploadedFile($file, string $directoryName): string
    {
        $directory = public_path('uploads/' . $directoryName);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = $directoryName . '-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $fileName);

        return 'uploads/' . $directoryName . '/' . $fileName;
    }
}
