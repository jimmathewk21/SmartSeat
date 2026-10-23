<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroySpeakerRequest;
use App\Http\Requests\StoreSpeakerRequest;
use App\Http\Requests\UpdateSpeakerRequest;
use App\Models\Country;
use App\Models\Event;
use App\Models\Speaker;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class SpeakersController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('speaker_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = $this->buildFilteredQuery($request);
        $this->applySorting($request, $query);

        $speakers = $query
            ->paginate(25)
            ->appends($request->query());

        $countries = Speaker::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $categories = Speaker::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.speakers.index', compact('speakers', 'countries', 'categories'));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('speaker_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = $this->buildFilteredQuery($request)
            ->orderBy('sort_order')
            ->orderBy('id');

        $filename = 'speakers-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Event',
                'Name',
                'Designation',
                'Company',
                'Country',
                'Email',
                'Phone',
                'Speaker / Moderator',
                'Topic',
                'Category',
                'Topic Category',
                'Biography Type',
                'Bio URL',
                'Advert URL',
                'Presentation File URL',
                'Show On Home Page',
                'Show On Scroll Bar',
                'Key Figure',
                'Status',
                'Sort Order',
            ]);

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $speaker) {
                    fputcsv($handle, [
                        $speaker->id,
                        optional($speaker->event)->title,
                        $speaker->name,
                        $speaker->designation,
                        $speaker->company_name,
                        $speaker->country,
                        $speaker->email,
                        $speaker->phone,
                        match ((string) ($speaker->speaker_role ?? 'speaker')) {
                            'moderator' => 'Moderator',
                            'both' => 'Speaker & Moderator',
                            default => 'Speaker',
                        },
                        $speaker->topic,
                        $speaker->category,
                        $speaker->topic_category,
                        $speaker->biography_type,
                        $speaker->resource_url,
                        $speaker->advert_url,
                        $speaker->presentation_file_url,
                        $speaker->show_on_homepage ? 'Yes' : 'No',
                        $speaker->show_on_scrollbar ? 'Yes' : 'No',
                        $speaker->is_key_figure ? 'Yes' : 'No',
                        $speaker->is_active ? 'Published' : 'Unpublished',
                        $speaker->sort_order,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function checkEmail(Request $request)
    {
        abort_if(Gate::denies('speaker_create') && Gate::denies('speaker_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'speaker_id' => ['nullable', 'integer', 'exists:speakers,id'],
        ]);

        $normalizedEmail = mb_strtolower(trim((string) $validated['email']));
        $speakerId = (int) ($validated['speaker_id'] ?? 0);

        $exists = Speaker::query()
            ->whereNotNull('email')
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->when($speakerId > 0, function ($query) use ($speakerId) {
                $query->where('id', '!=', $speakerId);
            })
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This email is already used by another speaker.' : 'Email is available.',
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('speaker_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $countryOptions = Country::query()
            ->orderBy('name')
            ->pluck('name', 'name')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.speakers.create', compact('events', 'countryOptions'));
    }

    public function store(StoreSpeakerRequest $request)
    {
        $data = $request->validated();
        $data['show_on_current_conference'] = false;
        $data['show_on_future_conference'] = false;

        if ($request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            $directory = public_path('uploads/speakers');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'speaker-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['photo_url'] = 'uploads/speakers/' . $fileName;
        }

        unset($data['photo_file']);

        if ($request->hasFile('advert_file')) {
            $file = $request->file('advert_file');
            $directory = public_path('uploads/speakers');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'speaker-advert-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['advert_url'] = 'uploads/speakers/' . $fileName;
        }

        if ($request->hasFile('presentation_file')) {
            $file = $request->file('presentation_file');
            $directory = public_path('uploads/speakers');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'speaker-presentation-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['presentation_file_url'] = 'uploads/speakers/' . $fileName;
        }

        if ($request->hasFile('bio_file')) {
            $file = $request->file('bio_file');
            $directory = public_path('uploads/speakers');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'speaker-bio-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['resource_url'] = 'uploads/speakers/' . $fileName;
            $data['biography_type'] = 'pdf';
        }

        unset($data['advert_file'], $data['presentation_file'], $data['bio_file']);

        Speaker::create($data);

        return redirect()->route('admin.speakers.index');
    }

    public function edit(Speaker $speaker)
    {
        abort_if(Gate::denies('speaker_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::query()
            ->where(function ($query) use ($speaker) {
                $query->where('is_active', true)
                    ->orWhere('id', $speaker->event_id);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $countryOptions = Country::query()
            ->orderBy('name')
            ->pluck('name', 'name')
            ->prepend(trans('global.pleaseSelect'), '');

        $speaker->load('event');

        return view('admin.speakers.edit', compact('speaker', 'events', 'countryOptions'));
    }

    public function update(UpdateSpeakerRequest $request, Speaker $speaker)
    {
        $data = $request->validated();
        $data['show_on_current_conference'] = false;
        $data['show_on_future_conference'] = false;

        if ($request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            $directory = public_path('uploads/speakers');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'speaker-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['photo_url'] = 'uploads/speakers/' . $fileName;
        }

        unset($data['photo_file']);

        if ($request->hasFile('advert_file')) {
            $file = $request->file('advert_file');
            $directory = public_path('uploads/speakers');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'speaker-advert-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['advert_url'] = 'uploads/speakers/' . $fileName;
        }

        if ($request->hasFile('presentation_file')) {
            $file = $request->file('presentation_file');
            $directory = public_path('uploads/speakers');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'speaker-presentation-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['presentation_file_url'] = 'uploads/speakers/' . $fileName;
        }

        if ($request->hasFile('bio_file')) {
            $file = $request->file('bio_file');
            $directory = public_path('uploads/speakers');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'speaker-bio-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);
            $data['resource_url'] = 'uploads/speakers/' . $fileName;
            $data['biography_type'] = 'pdf';
        }

        unset($data['advert_file'], $data['presentation_file'], $data['bio_file']);

        $speaker->update($data);

        return redirect()->route('admin.speakers.index');
    }

    public function show(Speaker $speaker)
    {
        abort_if(Gate::denies('speaker_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $speaker->load('event');

        return view('admin.speakers.show', compact('speaker'));
    }

    public function destroy(Speaker $speaker)
    {
        abort_if(Gate::denies('speaker_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $speaker->delete();

        return back();
    }

    public function massDestroy(MassDestroySpeakerRequest $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back();
        }

        Speaker::whereIn('id', $ids)->delete();

        return back();
    }

    public function bulkActivate(Request $request)
    {
        abort_if(Gate::denies('speaker_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return back();
        }

        Speaker::whereIn('id', $ids)->update(['is_active' => true]);

        return back();
    }

    public function bulkDeactivate(Request $request)
    {
        abort_if(Gate::denies('speaker_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return back();
        }

        Speaker::whereIn('id', $ids)->update(['is_active' => false]);

        return back();
    }

    public function toggleStatus(Speaker $speaker): RedirectResponse
    {
        abort_if(Gate::denies('speaker_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $speaker->update([
            'is_active' => ! $speaker->is_active,
        ]);

        return back();
    }

    public function toggleHomepage(Request $request, Speaker $speaker): RedirectResponse
    {
        abort_if(Gate::denies('speaker_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $speaker->update([
            'show_on_homepage' => $request->boolean('show_on_homepage'),
        ]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        abort_if(Gate::denies('speaker_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'sort_orders' => ['required', 'array', 'min:1'],
            'sort_orders.*' => ['required', 'integer', 'min:0'],
        ]);

        $sortOrders = collect($validated['sort_orders'])
            ->mapWithKeys(function ($sortOrder, $id) {
                return [(int) $id => (int) $sortOrder];
            })
            ->filter(fn ($sortOrder, $id) => $id > 0 && $sortOrder >= 0);

        $existingIds = Speaker::query()
            ->whereIn('id', $sortOrders->keys())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($existingIds) !== $sortOrders->count()) {
            return back()->with('message', 'Some speakers could not be reordered.');
        }

        DB::transaction(function () use ($sortOrders) {
            foreach ($sortOrders as $id => $sortOrder) {
                Speaker::query()
                    ->where('id', $id)
                    ->update(['sort_order' => $sortOrder]);
            }
        });

        return back()->with('message', 'Speaker sorting order updated successfully.');
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = Speaker::with(['event'])->select(sprintf('%s.*', (new Speaker)->table));

        $search = trim((string) $request->input('search', ''));
        $legacyTextFilters = [
            'name' => 'name',
            'company' => 'company_name',
            'designation' => 'designation',
            'email' => 'email',
            'topic' => 'topic',
        ];

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchLike = '%' . $search . '%';

                $searchQuery->where('name', 'like', $searchLike)
                    ->orWhere('company_name', 'like', $searchLike)
                    ->orWhere('designation', 'like', $searchLike)
                    ->orWhere('email', 'like', $searchLike)
                    ->orWhere('topic', 'like', $searchLike);
            });
        } else {
            foreach ($legacyTextFilters as $requestKey => $column) {
                $value = trim((string) $request->input($requestKey, ''));

                if ($value === '') {
                    continue;
                }

                $query->where($column, 'like', '%' . $value . '%');
            }
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('is_key_figure')) {
            $query->where('is_key_figure', $request->boolean('is_key_figure'));
        }

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('speaker_type')) {
            $speakerType = (string) $request->input('speaker_type');
            if (in_array($speakerType, ['male', 'female'], true)) {
                $query->where('gender', $speakerType);
            }
        }

        if ($request->filled('speaker_role')) {
            $speakerRole = (string) $request->input('speaker_role');
            if (in_array($speakerRole, ['speaker', 'moderator', 'both'], true)) {
                $query->where('speaker_role', $speakerRole);
            }
        }

        if ($request->filled('topic_allotted')) {
            if ($request->boolean('topic_allotted')) {
                $query->whereNotNull('topic')
                    ->where('topic', '!=', '');
            } else {
                $query->where(function ($topicQuery) {
                    $topicQuery->whereNull('topic')
                        ->orWhere('topic', '');
                });
            }
        }

        return $query;
    }

    private function applySorting(Request $request, $query): void
    {
        $allowedSorts = ['name', 'company_name', 'designation', 'email', 'topic'];
        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, $allowedSorts, true)) {
            $this->applyNaturalSort($query, $sortBy, $sortDir);

            return;
        }

        $query->orderByDesc('id');
    }

    private function applyNaturalSort($query, string $column, string $direction): void
    {
        $direction = strtoupper($direction);

        if ($column === 'email') {
            $localPart = "LOWER(COALESCE(SUBSTRING_INDEX(`{$column}`, '@', 1), ''))";
            $domainPart = "LOWER(COALESCE(SUBSTRING_INDEX(`{$column}`, '@', -1), ''))";

            $query->orderByRaw("REGEXP_REPLACE({$localPart}, '[0-9]+$', '') {$direction}")
                ->orderByRaw("CAST(COALESCE(REGEXP_SUBSTR({$localPart}, '[0-9]+$'), '0') AS UNSIGNED) {$direction}")
                ->orderByRaw("{$domainPart} {$direction}")
                ->orderBy('id', $direction);

            return;
        }

        $prefixExpression = $this->naturalSortPrefixExpression($column);
        $numberExpression = $this->naturalSortNumberExpression($column);

        $query->orderByRaw($prefixExpression . ' ' . $direction)
            ->orderByRaw($numberExpression . ' ' . $direction)
            ->orderByRaw("LOWER(COALESCE(`{$column}`, '')) " . $direction)
            ->orderBy('id', $direction);
    }

    private function naturalSortPrefixExpression(string $column): string
    {
        return "REGEXP_REPLACE(LOWER(COALESCE(`{$column}`, '')), '[0-9]+$', '')";
    }

    private function naturalSortNumberExpression(string $column): string
    {
        return "CAST(COALESCE(REGEXP_SUBSTR(LOWER(COALESCE(`{$column}`, '')), '[0-9]+$'), '0') AS UNSIGNED)";
    }
}
