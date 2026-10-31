<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingDetailsRequest;
use App\Http\Requests\BookingReviewRequest;
use App\Models\TrainTrip;
use App\Models\TripSeatInventory;
use App\Services\BookingDrafts;
use App\Services\BookingEngine;
use App\Services\SeatRecommendations;
use App\Services\TrainSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingWizardController extends Controller
{
    public function create(Request $request, TrainTrip $trip, BookingEngine $engine, BookingDrafts $drafts): View
    {
        $query = $request->validate([
            'count' => ['sometimes', 'integer', 'between:1,'.config('smartseat.booking.max_passengers')],
            'origin' => ['sometimes', 'string', 'regex:/\\A[A-Z]{1,5}\\z/'],
            'destination' => ['sometimes', 'string', 'regex:/\\A[A-Z]{1,5}\\z/'],
        ]);
        $stops = $trip->train->routeStops()->with('station')->orderBy('stop_order')->limit(100)->get();
        $origin = isset($query['origin']) ? $stops->first(fn ($stop) => $stop->station->code === $query['origin']) : $stops->first();
        $destination = isset($query['destination']) ? $stops->first(fn ($stop) => $stop->station->code === $query['destination']) : $stops->last();
        abort_unless($origin && $destination, 422, 'This train does not serve those stations.');
        $journey = $engine->itinerary($trip, $origin->stop_order, $destination->stop_order);
        $key = $drafts->create($request->user(), $trip);
        $count = $query['count'] ?? 1;
        $classes = $trip->train->coaches()->whereHas('seats')->select('travel_class')->distinct()->limit(7)->pluck('travel_class');

        return view('bookings.passengers', compact('trip', 'journey', 'key', 'count', 'classes'));
    }

    public function details(BookingDetailsRequest $request, TrainTrip $trip, BookingDrafts $drafts, BookingEngine $engine): RedirectResponse
    {
        $data = $request->validated();
        $key = $data['draft_key'];
        $draft = $drafts->get($request->user(), $key);
        abort_unless($draft['trip_id'] === $trip->id, 404);
        unset($data['draft_key']);
        $details = $engine->details($data);
        $engine->itinerary($trip, $details['boarding_order'], $details['alighting_order']);
        $drafts->update($request->user(), $key, ['details' => $details, 'review' => null]);

        return redirect()->route('booking-drafts.seats', $key);
    }

    public function seats(Request $request, string $draft, BookingDrafts $drafts, BookingEngine $engine, TrainSearch $search): View
    {
        $state = $drafts->get($request->user(), $draft);
        abort_unless($state['details'], 422, 'Enter passenger details first.');
        $details = $state['details'];
        $trip = TrainTrip::findOrFail($state['trip_id']);
        $journey = $engine->itinerary($trip, $details['boarding_order'], $details['alighting_order']);
        $coaches = $search->coaches([$trip->id])->get($trip->id, collect())->where('travel_class', $details['travel_class'])->values();
        $request->validate([
            'coach_ids' => ['sometimes', 'array', 'list', 'size:'.count($details['passengers'])],
            'coach_ids.*' => ['required', 'integer', Rule::in($coaches->pluck('id')->all())],
        ]);
        $default = $coaches->first(fn ($coach) => $coach->available_seats > 0) ?? $coaches->first();
        $coachIds = $request->input('coach_ids', array_fill(0, count($details['passengers']), $default?->id));
        $choices = $engine->available($trip, $details['travel_class'])
            ->whereHas('seat', fn ($query) => $query->whereIn('coach_id', array_filter($coachIds)))
            ->with('seat.coach')->orderBy('id')->limit(count($details['passengers']) * 200)->get()->groupBy('seat.coach_id');
        $suggestionInventory = $engine->available($trip, $details['travel_class'])->with('seat.coach')->orderBy('id')->limit(6000)->get()->keyBy('id');
        $recommendations = app(SeatRecommendations::class)->suggest($details['passengers'], $suggestionInventory, $details['keep_group_together']);

        return view('bookings.seats', compact('trip', 'journey', 'details', 'draft', 'coaches', 'coachIds', 'choices', 'recommendations', 'suggestionInventory'));
    }

    public function review(BookingReviewRequest $request, string $draft, BookingDrafts $drafts, BookingEngine $engine): RedirectResponse
    {
        $state = $drafts->get($request->user(), $draft);
        abort_unless($state['details'], 422);
        $trip = TrainTrip::findOrFail($state['trip_id']);
        $selection = $request->validated();
        $review = $engine->preview($trip, $state['details'], $selection['mode'], $selection['seats'] ?? []);
        $review['token'] = (string) Str::uuid();
        $drafts->update($request->user(), $draft, ['review' => $review]);

        return redirect()->route('booking-drafts.review', $draft);
    }

    public function showReview(Request $request, string $draft, BookingDrafts $drafts, BookingEngine $engine): View
    {
        $state = $drafts->get($request->user(), $draft);
        abort_unless($state['review'], 422, 'Review your seat selection first.');
        $review = $state['review'];
        $trip = TrainTrip::findOrFail($state['trip_id']);
        $journey = $engine->itinerary($trip, $review['details']['boarding_order'], $review['details']['alighting_order']);
        $selected = TripSeatInventory::where('trip_id', $trip->id)->whereIn('id', $review['seats'])->with('seat.coach')->limit(count($review['seats']))->get()->keyBy('id');

        return view('bookings.review', compact('trip', 'journey', 'review', 'draft', 'selected'));
    }

    public function confirm(Request $request, string $draft, BookingDrafts $drafts, BookingEngine $engine): RedirectResponse
    {
        $state = $drafts->get($request->user(), $draft);
        abort_unless($state['review'], 422, 'Review the booking before confirmation.');
        $review = $state['review'];
        $confirmation = $request->validate(['review_token' => ['required', 'uuid']]);
        if (! hash_equals($review['token'], $confirmation['review_token'])) {
            throw ValidationException::withMessages(['review_token' => 'The review changed in another tab. Reload and check the current review before confirming.']);
        }
        // All confirmation details come from this owner's server-side reviewed draft.
        $booking = $engine->book($request->user(), TrainTrip::findOrFail($state['trip_id']), $review['details'], $review['mode'], $review['seats'], $draft);

        return redirect()->route('bookings.show', $booking)->with('booking_confirmed', true);
    }
}
