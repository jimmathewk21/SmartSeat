<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingEngine;
use App\Services\Intelligence\WaitlistPrediction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $request->user()->bookings()->with('trip.train')->withCount('passengers')->orderByDesc('id')->paginate(20);

        return view('bookings.index', compact('bookings'));
    }

    public function show(Booking $booking, BookingEngine $engine, WaitlistPrediction $predictions): View
    {
        Gate::authorize('view', $booking);
        $booking->load(['trip.train', 'passengers.assignments.inventory.seat.coach', 'passengers.waitlistEntries']);
        $events = $booking->events()->orderBy('id')->paginate(10);
        $journey = $engine->itinerary($booking->trip, $booking->boarding_order, $booking->alighting_order, false);
        $prediction = $predictions->forBooking($booking);

        return view('bookings.show', compact('booking', 'journey', 'events', 'prediction'));
    }

    public function cancellation(Booking $booking): View
    {
        Gate::authorize('cancel', $booking);
        $booking->load('trip.train');

        return view('bookings.cancel', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking, BookingEngine $engine): RedirectResponse
    {
        $engine->cancel($request->user(), $booking);

        return redirect()->route('bookings.show', $booking)->with('status', 'Cancellation recorded. Repeated cancellation does not release seats again.');
    }
}
