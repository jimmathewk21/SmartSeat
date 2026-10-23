<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Testimonial;
use App\Support\EventPageSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FrontendEventController extends Controller
{
    public function index(Request $request)
    {
        $settings = EventPageSettings::get();

        $query = Event::query()
            ->select(['id', 'title', 'slug', 'venue', 'location', 'start_at', 'end_at', 'description', 'image_url', 'gallery_url', 'brochure_url'])
            ->published();

        $now = now();

        $upcomingEvent = (clone $query)
            ->whereNotNull('start_at')
            ->where('start_at', '>=', $now)
            ->orderByDesc('id')
            ->first();

        $pastEvents = (clone $query)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')
                    ->orWhere('start_at', '<', $now);
            })
            ->orderByDesc('id')
            ->get();

        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->where('show_on_scrollbar', true)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('frontend.events.index', compact('settings', 'upcomingEvent', 'pastEvents', 'testimonials'));
    }

    public function show(string $slug): RedirectResponse
    {
        $event = $this->resolveEventBySlug($slug);

        if (! $event->isBookableUpcoming()) {
            return redirect()->route('frontend.events.index');
        }

        return redirect()->route('frontend.events.tickets', ['event' => $event->slug]);
    }

    private function resolveEventBySlug(string $value): Event
    {
        $rawSlug = trim($value);
        $normalizedSlug = Str::slug($rawSlug);
        $canonicalRawSlug = Str::lower(str_replace(['-', '_', ' '], '', $rawSlug));

        return Event::query()
            ->where(function ($query) use ($rawSlug, $normalizedSlug, $canonicalRawSlug) {
                $query->whereRaw('LOWER(slug) = ?', [mb_strtolower($rawSlug)]);

                if ($normalizedSlug !== '' && mb_strtolower($normalizedSlug) !== mb_strtolower($rawSlug)) {
                    $query->orWhereRaw('LOWER(slug) = ?', [mb_strtolower($normalizedSlug)]);
                }

                if ($canonicalRawSlug !== '') {
                    $query->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(LOWER(slug), '-', ''), '_', ''), ' ', '') = ?",
                        [$canonicalRawSlug]
                    );
                }
            })
            ->firstOrFail();
    }
}
