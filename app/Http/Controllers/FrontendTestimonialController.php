<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Event;
use App\Models\Testimonial;
use App\Services\AdminNotificationMailer;
use App\Support\TestimonialPageSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class FrontendTestimonialController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimonial::query()
            ->select([
                'id',
                'event_id',
                'name',
                'designation',
                'company',
                'message',
                'experience_word',
                'stars',
                'photo_url',
                'testimonial_date',
                'year',
                'is_key_figure',
                'sort_order',
            ])
            ->with(['event:id,title', 'country:id,name'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('company', 'like', '%' . $search . '%')
                    ->orWhere('designation', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%')
                    ->orWhere('experience_word', 'like', '%' . $search . '%')
                    ->orWhereHas('country', function ($countryQuery) use ($search) {
                        $countryQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('event', function ($eventQuery) use ($search) {
                        $eventQuery->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('year')) {
            $query->where('year', (int) $request->input('year'));
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', (int) $request->input('event_id'));
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', (int) $request->input('country_id'));
        }

        $sort = (string) $request->input('sort', 'latest');

        if ($sort === 'key_figures') {
            $sort = 'event_name';
        }

        if ($sort === 'event_name') {
            $query->orderBy(
                Event::query()
                    ->select('title')
                    ->whereColumn('events.id', 'testimonials.event_id')
                    ->limit(1)
            )
                ->orderBy('name')
                ->orderByDesc('testimonial_date')
                ->orderBy('sort_order')
                ->orderByDesc('id');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('name')->orderByDesc('testimonial_date')->orderBy('sort_order')->orderByDesc('id');
        } elseif ($sort === 'name_desc') {
            $query->orderByDesc('name')->orderByDesc('testimonial_date')->orderBy('sort_order')->orderByDesc('id');
        } elseif ($sort === 'oldest') {
            $query->orderBy('year')->orderBy('testimonial_date')->orderBy('sort_order')->orderBy('id');
        } else {
            $query->orderByDesc('id');
            $sort = 'latest';
        }

        $testimonials = $query->get();

        $years = Testimonial::query()
            ->where('is_active', true)
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->values();

        $events = Testimonial::query()
            ->with('event:id,title')
            ->where('is_active', true)
            ->whereNotNull('event_id')
            ->get()
            ->pluck('event')
            ->filter()
            ->unique('id')
            ->sortBy('title')
            ->values();

        $countries = Country::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $pageSettings = TestimonialPageSettings::get();

        $left = random_int(1, 9);
        $right = random_int(1, 9);
        $captchaQuestion = "{$left} + {$right} = ?";
        Session::put('testimonial_captcha_answer', (string) ($left + $right));
        Session::put('testimonial_captcha_question', $captchaQuestion);

        return view('frontend.testimonials.index', compact(
            'testimonials',
            'years',
            'events',
            'countries',
            'sort',
            'pageSettings',
            'captchaQuestion'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:190'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'picture_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'experience_word' => ['required', 'string', 'max:80'],
            'stars' => ['required', 'integer', 'in:3,4,5'],
            'comment' => ['required', 'string', 'max:2000'],
            'captcha' => [
                'required',
                function ($attribute, $value, $fail): void {
                    $expected = (string) session('testimonial_captcha_answer', '');
                    if ($expected === '' || trim((string) $value) !== $expected) {
                        $fail('Invalid captcha answer.');
                    }
                },
            ],
        ]);

        $uploadedPath = null;

        if ($request->hasFile('picture_file')) {
            $image = $request->file('picture_file');
            $directory = public_path('uploads/testimonial-feedback');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $fileName = 'testimonial-feedback-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . Str::lower($image->getClientOriginalExtension());
            $image->move($directory, $fileName);
            $uploadedPath = 'uploads/testimonial-feedback/' . $fileName;
        }

        $validated['country'] = Country::query()
            ->whereKey((int) $validated['country_id'])
            ->value('name') ?? '';

        $this->sendAdminNotification($validated, $uploadedPath);
        Session::forget('testimonial_captcha_answer');
        Session::forget('testimonial_captcha_question');

        return redirect()
            ->to(route('frontend.testimonials.index') . '#testimonial-feedback')
            ->with('message', 'Thank you for sharing your feedback. Our team has been notified.');
    }

    private function sendAdminNotification(array $validated, ?string $uploadedPath): void
    {
        app(AdminNotificationMailer::class)->send('testimonial_feedback', [
            'name' => $validated['name'] ?? '',
            'company_name' => $validated['company_name'] ?? '',
            'email' => $validated['email'] ?? '',
            'country' => $validated['country'] ?? '',
            'experience_word' => $validated['experience_word'] ?? '',
            'stars' => $validated['stars'] ?? '',
            'comment' => $validated['comment'] ?? '',
            'picture_url' => $uploadedPath ?: 'Not provided',
        ]);
    }
}
