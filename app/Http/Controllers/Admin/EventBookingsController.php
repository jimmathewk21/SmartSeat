<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventBooking;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventBookingsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('ticket_booking_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = EventBooking::query()
            ->with(['event:id,title', 'plan:id,name,sort_order', 'user:id,name,email'])
            ->select([
                'id',
                'booking_code',
                'user_id',
                'event_id',
                'event_ticket_plan_id',
                'quantity',
                'total_amount_paise',
                'currency',
                'billing_country_code',
                'display_currency',
                'display_total_amount_minor',
                'fx_rate_usd_to_inr',
                'status',
                'created_at',
                'paid_at',
                'razorpay_order_id',
                'razorpay_payment_id',
            ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhere('razorpay_order_id', 'like', "%{$search}%")
                    ->orWhere('razorpay_payment_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', (int) $request->input('event_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('billing_country_code')) {
            $query->where('billing_country_code', strtoupper((string) $request->input('billing_country_code')));
        }
        if ($request->filled('display_currency')) {
            $query->where('display_currency', strtoupper((string) $request->input('display_currency')));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $bookings = $query
            ->orderByDesc('id')
            ->paginate(25)
            ->appends($request->query());

        $events = Event::query()
            ->select(['id', 'title'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('admin.eventBookings.index', compact('bookings', 'events'));
    }

    public function show(EventBooking $event_booking)
    {
        abort_if(Gate::denies('ticket_booking_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $event_booking->load(['event', 'plan', 'user', 'participant', 'tickets', 'delegates']);

        return view('admin.eventBookings.show', ['eventBooking' => $event_booking]);
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('ticket_booking_export'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = EventBooking::query()
            ->with(['event:id,title', 'plan:id,name', 'user:id,name,email'])
            ->select([
                'id',
                'booking_code',
                'user_id',
                'event_id',
                'event_ticket_plan_id',
                'quantity',
                'unit_price_paise',
                'total_amount_paise',
                'currency',
                'billing_country_code',
                'display_currency',
                'display_total_amount_minor',
                'fx_rate_usd_to_inr',
                'status',
                'created_at',
                'paid_at',
                'razorpay_order_id',
                'razorpay_payment_id',
            ])
            ->orderByDesc('id');

        $filename = 'event-bookings-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Booking Code',
                'Event',
                'Plan',
                'User Name',
                'User Email',
                'Quantity',
                'Unit Price (INR)',
                'Total (INR)',
                'Currency',
                'Billing Country',
                'Display Currency',
                'Display Total',
                'FX USD->INR',
                'Status',
                'Booking Date',
                'Paid At',
                'Razorpay Order ID',
                'Razorpay Payment ID',
            ]);

            $query->chunk(250, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->booking_code,
                        $row->event?->title,
                        $row->plan?->name,
                        $row->user?->name,
                        $row->user?->email,
                        $row->quantity,
                        number_format($row->unit_price_paise / 100, 2, '.', ''),
                        number_format($row->total_amount_paise / 100, 2, '.', ''),
                        $row->currency,
                        $row->billing_country_code,
                        $row->display_currency,
                        $row->display_currency === 'USD'
                            ? number_format(($row->display_total_amount_minor ?? 0) / 100, 2, '.', '')
                            : number_format((($row->display_total_amount_minor ?? $row->total_amount_paise) ?: 0) / 100, 2, '.', ''),
                        $row->fx_rate_usd_to_inr,
                        ucfirst($row->status),
                        optional($row->created_at)->format('d-M-Y h:i A'),
                        optional($row->paid_at)->format('d-M-Y h:i A'),
                        $row->razorpay_order_id,
                        $row->razorpay_payment_id,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
