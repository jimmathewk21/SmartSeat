<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrainSearchRequest;
use App\Models\Station;
use App\Services\TrainSearchService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FrontendTrainController extends Controller
{
    public function index(TrainSearchRequest $request, TrainSearchService $service)
    {
        $stations = $service->stations();
        $trips = collect();
        $searched = $request->filled(['from_station_id', 'to_station_id', 'date']);

        if ($searched) {
            $validated = $request->validated();
            $activeStationCount = Station::query()->whereIn('id', [
                (int) $validated['from_station_id'],
                (int) $validated['to_station_id'],
            ])->where('is_active', true)->count();

            if ($activeStationCount !== 2) {
                throw ValidationException::withMessages(['stations' => 'Choose active origin and destination stations.']);
            }

            $trips = $service->search(
                (int) $validated['from_station_id'],
                (int) $validated['to_station_id'],
                $validated['date']
            );
        }

        return view('frontend.trains.search', compact('stations', 'trips', 'searched'));
    }

    public function myBookings(Request $request)
    {
        $confirmedBooking = null;
        $confirmationCode = $request->session()->get('train_booking_confirmation_code');

        if (is_string($confirmationCode) && $confirmationCode !== '') {
            $confirmedBooking = $request->user()->trainBookings()
                ->where('booking_code', $confirmationCode)
                ->with([
                    'trip.train.routeStops.station',
                    'passengers',
                    'seatAssignments.passenger',
                    'seatAssignments.inventory.seat.coach',
                ])
                ->first();
        }

        $bookings = $request->user()->trainBookings()
            ->with([
                'trip.train',
                'trip.train.routeStops.station',
                'passengers',
                'seatAssignments.inventory.seat.coach',
            ])
            ->orderByDesc('created_at')
            ->paginate(15);
        $waitlistEntries = $request->user()->trainWaitlistEntries()
            ->with(['trip.train', 'passengers', 'promotedBooking'])
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'waitlist_page');

        return view('frontend.trains.bookings', compact('bookings', 'waitlistEntries', 'confirmedBooking'));
    }
}
