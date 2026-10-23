<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyTestimonialRequest;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use App\Models\Country;
use App\Models\Event;
use App\Models\Testimonial;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TestimonialsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('testimonial_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Testimonial::with(['event', 'country'])->select(sprintf('%s.*', (new Testimonial)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($eventQuery) use ($search) {
                        $eventQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->input('country_id'));
        }

        if ($request->filled('year')) {
            $query->where('year', (int) $request->input('year'));
        }

        if ($request->filled('testimonial_date')) {
            $query->whereDate('testimonial_date', $request->input('testimonial_date'));
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

        if ($request->filled('is_key_figure')) {
            $query->where('is_key_figure', $request->boolean('is_key_figure'));
        }

        $allowedSorts = ['id', 'name', 'company', 'year', 'stars', 'sort_order', 'is_active', 'testimonial_date', 'show_on_homepage', 'show_on_scrollbar', 'is_key_figure'];
        $sortBy = $request->input('sort_by', 'sort_order');
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'sort_order';
        }
        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $testimonials = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id')
            ->paginate(25)
            ->appends($request->query());

        $events = Event::query()->select(['id', 'title'])->orderBy('title')->get();
        $countries = Country::query()->select(['id', 'name'])->orderBy('name')->get();

        return view('admin.testimonials.index', compact('testimonials', 'events', 'countries'));
    }

    public function create()
    {
        abort_if(Gate::denies('testimonial_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $countries = Country::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.testimonials.create', compact('events', 'countries'));
    }

    public function store(StoreTestimonialRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            $directory = public_path('uploads/testimonials');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'testimonial-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);

            $data['photo_url'] = 'uploads/testimonials/' . $fileName;
        }

        unset($data['photo_file']);

        Testimonial::create($data);

        return redirect()->route('admin.testimonials.index');
    }

    public function edit(Testimonial $testimonial)
    {
        abort_if(Gate::denies('testimonial_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::query()
            ->where(function ($query) use ($testimonial) {
                $query->where('is_active', true)
                    ->orWhere('id', $testimonial->event_id);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->pluck('title', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $countries = Country::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $testimonial->load(['event', 'country']);

        return view('admin.testimonials.edit', compact('testimonial', 'events', 'countries'));
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial)
    {
        $data = $request->validated();

        if ($request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            $directory = public_path('uploads/testimonials');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $extension = Str::lower($file->getClientOriginalExtension());
            $fileName = 'testimonial-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
            $file->move($directory, $fileName);

            $data['photo_url'] = 'uploads/testimonials/' . $fileName;
        }

        unset($data['photo_file']);

        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index');
    }

    public function show(Testimonial $testimonial)
    {
        abort_if(Gate::denies('testimonial_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $testimonial->load(['event', 'country']);

        return view('admin.testimonials.show', compact('testimonial'));
    }

    public function destroy(Testimonial $testimonial)
    {
        abort_if(Gate::denies('testimonial_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $testimonial->delete();

        return back();
    }

    public function massDestroy(MassDestroyTestimonialRequest $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back();
        }

        Testimonial::whereIn('id', $ids)->delete();

        return back();
    }

    public function bulkStatus(Request $request)
    {
        abort_if(Gate::denies('testimonial_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:testimonials,id'],
            'status' => ['required', 'in:publish,unpublish'],
        ]);

        $isActive = $validated['status'] === 'publish';
        Testimonial::query()->whereIn('id', $validated['ids'])->update(['is_active' => $isActive]);

        return back()->with('message', $isActive ? 'Selected testimonials published successfully.' : 'Selected testimonials unpublished successfully.');
    }
}
