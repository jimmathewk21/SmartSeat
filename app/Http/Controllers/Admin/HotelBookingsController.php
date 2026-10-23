<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotelBooking;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class HotelBookingsController extends Controller
{
    private const STATUS_MAP = [
        'new' => 'pending',
        'pending' => 'pending',
        'confirmed' => 'confirmed',
        'cancelled' => 'ignore',
        'ignore' => 'ignore',
    ];

    public function index(Request $request)
    {
        abort_if(Gate::denies('hotel_booking_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = $this->filteredQuery($request)->select([
            'id',
            'booking_code',
            'full_name',
            'email',
            'phone',
            'country',
            'occupancy',
            'bed_size',
            'preference',
            'hotel_name',
            'room_name',
            'rate_inr',
            'rate_usd',
            'check_in_date',
            'check_out_date',
            'number_of_guests',
            'number_of_rooms',
            'number_of_nights',
            'subtotal_inr',
            'gst_rate',
            'gst_amount_inr',
            'total_inr',
            'special_requests',
            'status',
            'created_at',
        ]);

        $hotelBookings = $query
            ->orderByDesc('id')
            ->paginate(25)
            ->appends($request->query());

        $hotelNames = HotelBooking::query()
            ->whereNotNull('hotel_name')
            ->where('hotel_name', '!=', '')
            ->distinct()
            ->orderBy('hotel_name')
            ->pluck('hotel_name');

        return view('admin.hotelBookings.index', compact('hotelBookings', 'hotelNames'));
    }

    public function bulkStatus(Request $request)
    {
        abort_if(Gate::denies('hotel_booking_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:hotel_bookings,id'],
            'status' => ['required', 'in:pending,confirmed,ignore'],
        ]);

        HotelBooking::query()
            ->whereIn('id', $validated['ids'])
            ->update(['status' => $validated['status']]);

        return back()->with('message', 'Selected hotel bookings updated successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('hotel_booking_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:hotel_bookings,id'],
        ]);

        HotelBooking::query()->whereIn('id', $validated['ids'])->delete();

        return back()->with('message', 'Selected hotel bookings deleted successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('hotel_booking_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = $request->filled('ids')
            ? HotelBooking::query()->whereIn('id', $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'exists:hotel_bookings,id'],
            ])['ids'])
            : $this->filteredQuery($request);

        $filename = 'hotel-bookings-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Booking Code',
                'Name',
                'Email',
                'Phone',
                'Country',
                'Hotel',
                'Room Category',
                'No. of Rooms',
                'Check-in',
                'Check-out',
                'Occupancy',
                'Bed Size',
                'Preference',
                'Special Requests',
                'Booked At',
                'INR',
                'USD',
                'Status',
            ]);

            $query->orderByDesc('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $booking) {
                    fputcsv($handle, [
                        $booking->id,
                        $booking->booking_code,
                        $booking->full_name,
                        $booking->email,
                        $booking->phone ?? '',
                        $booking->country ?? '',
                        $booking->hotel_name ?? '',
                        $booking->room_name ?? '',
                        $booking->number_of_rooms,
                        optional($booking->check_in_date)->format('Y-m-d'),
                        optional($booking->check_out_date)->format('Y-m-d'),
                        $booking->occupancy ? ucfirst($booking->occupancy) : '',
                        $booking->bed_size ?? '',
                        $booking->preference ?? '',
                        $booking->special_requests ?? '',
                        optional($booking->created_at)->format('Y-m-d H:i:s'),
                        number_format((float) $booking->rate_inr, 2, '.', ''),
                        number_format((float) $booking->rate_usd, 2, '.', ''),
                        $this->displayStatus($booking->status),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = HotelBooking::query();

        if ($request->filled('guest_name')) {
            $guestName = trim((string) $request->input('guest_name'));
            $query->where('full_name', 'like', "%{$guestName}%");
        }

        if ($request->filled('hotel_name')) {
            $query->where('hotel_name', (string) $request->input('hotel_name'));
        }

        if ($request->filled('status')) {
            $requestedStatus = (string) $request->input('status');
            $legacyStatuses = array_keys(array_filter(self::STATUS_MAP, function ($mappedStatus) use ($requestedStatus) {
                return $mappedStatus === $requestedStatus;
            }));

            $query->whereIn('status', $legacyStatuses);
        }

        return $query;
    }

    public function displayStatus(?string $status): string
    {
        return ucfirst(self::STATUS_MAP[$status ?? 'pending'] ?? 'pending');
    }
}
