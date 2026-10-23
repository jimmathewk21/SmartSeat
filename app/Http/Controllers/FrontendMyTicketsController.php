<?php

namespace App\Http\Controllers;

use App\Models\EventBooking;
use Illuminate\Http\Request;

class FrontendMyTicketsController extends Controller
{
    public function index(Request $request)
    {
        $bookings = EventBooking::query()
            ->with([
                'event:id,title,slug,start_at',
                'plan:id,name,sort_order',
                'tickets:id,event_booking_id,ticket_code,status,issued_at',
            ])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(15);

        $bookingsByEvent = $bookings->getCollection()->groupBy('event_id');

        return view('frontend.my-tickets.index', compact('bookings', 'bookingsByEvent'));
    }
}
