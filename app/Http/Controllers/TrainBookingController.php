<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainBookingRequest;
use App\Http\Requests\StoreTrainWaitlistRequest;
use App\Models\TrainBooking;
use App\Models\TrainTrip;
use App\Models\TrainWaitlistEntry;
use App\Services\TrainBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrainBookingController extends Controller
{
    public function store(TrainTrip $trip, StoreTrainBookingRequest $request, TrainBookingService $service): RedirectResponse
    {
        $booking = $service->book(
            $request->user(),
            $trip->id,
            $request->validated('inventory_ids'),
            $request->validated('passengers')
        );

        return redirect()->route('frontend.trains.bookings')
            ->with('message', 'Your train booking is confirmed.')
            ->with('train_booking_confirmation_code', $booking->booking_code);
    }

    public function joinWaitlist(TrainTrip $trip, StoreTrainWaitlistRequest $request, TrainBookingService $service): RedirectResponse
    {
        $entry = $service->joinWaitlist($request->user(), $trip->id, $request->validated('passengers'));

        return redirect()->route('frontend.trains.search', $request->only(['from_station_id', 'to_station_id', 'date']))
            ->with('message', 'Your request is on the waitlist. No seat has been confirmed yet.')
            ->with('waitlist_entry_id', $entry->id);
    }

    public function cancel(TrainBooking $booking, Request $request, TrainBookingService $service): RedirectResponse
    {
        $service->cancel($request->user(), $booking->id);

        return redirect()->route('frontend.trains.bookings')->with('message', 'Train booking cancelled. Any newly available seat was offered to the waitlist.');
    }

    public function cancelWaitlist(TrainWaitlistEntry $entry, Request $request, TrainBookingService $service): RedirectResponse
    {
        $service->cancelWaitlistEntry($request->user(), $entry->id);

        return redirect()->route('frontend.trains.bookings')->with('message', 'Waitlist request cancelled.');
    }
}
