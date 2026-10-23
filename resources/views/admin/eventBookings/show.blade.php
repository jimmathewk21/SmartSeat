@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">{{ trans('global.show') }} Ticket Booking</div>
    <div class="card-body">
        <div class="form-group mb-3">
            <a class="btn btn-default" href="{{ route('admin.event-bookings.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>

        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th>ID</th><td>{{ $eventBooking->id }}</td></tr>
                <tr><th>Booking Code</th><td>{{ $eventBooking->booking_code }}</td></tr>
                <tr><th>Event</th><td>{{ $eventBooking->event?->title }}</td></tr>
                <tr><th>Plan</th><td>{{ $eventBooking->plan?->name }}</td></tr>
                <tr><th>User</th><td>{{ $eventBooking->user?->name }} ({{ $eventBooking->user?->email }})</td></tr>
                <tr><th>Quantity</th><td>{{ $eventBooking->quantity }}</td></tr>
                <tr><th>Unit Price (INR)</th><td>&#8377;{{ number_format($eventBooking->unit_price_paise / 100, 2) }}</td></tr>
                <tr><th>Total Amount (INR)</th><td>&#8377;{{ number_format($eventBooking->total_amount_paise / 100, 2) }}</td></tr>
                <tr>
                    <th>Display Amount</th>
                    <td>
                        @if($eventBooking->display_currency === 'USD')
                            ${{ number_format(($eventBooking->display_total_amount_minor ?? 0) / 100, 2) }}
                        @else
                            &#8377;{{ number_format(($eventBooking->display_total_amount_minor ?? 0) / 100, 2) }}
                        @endif
                        ({{ $eventBooking->display_currency ?: 'INR' }})
                    </td>
                </tr>
                <tr><th>Billing Country</th><td>{{ $eventBooking->billing_country_code ?: '-' }}</td></tr>
                <tr><th>Billing Company</th><td>{{ $eventBooking->billing_company_name ?: '-' }}</td></tr>
                <tr><th>Billing Address 1</th><td>{{ $eventBooking->billing_address_line_1 ?: '-' }}</td></tr>
                <tr><th>Billing Address 2</th><td>{{ $eventBooking->billing_address_line_2 ?: '-' }}</td></tr>
                <tr><th>GSTIN</th><td>{{ $eventBooking->gstin ?: '-' }}</td></tr>
                <tr><th>Promo Code</th><td>{{ $eventBooking->promo_code ?: '-' }}</td></tr>
                <tr><th>Promo Discount</th><td>{{ ($eventBooking->display_currency === 'USD' ? 'USD ' : 'INR ') . number_format(($eventBooking->promo_discount_minor ?? 0) / 100, 2) }}</td></tr>
                <tr><th>Gateway Charge</th><td>{{ ($eventBooking->display_currency === 'USD' ? 'USD ' : 'INR ') . number_format(($eventBooking->gateway_charge_minor ?? 0) / 100, 2) }}</td></tr>
                <tr><th>Grand Total (Display)</th><td>{{ ($eventBooking->display_currency === 'USD' ? 'USD ' : 'INR ') . number_format((($eventBooking->grand_total_display_minor ?? $eventBooking->display_total_amount_minor) ?? 0) / 100, 2) }}</td></tr>
                <tr><th>FX USD->INR</th><td>{{ $eventBooking->fx_rate_usd_to_inr ?: '-' }}</td></tr>
                <tr><th>Status</th><td>{{ ucfirst($eventBooking->status) }}</td></tr>
                <tr><th>Booking Date</th><td>{{ optional($eventBooking->created_at)->format('d-M-Y h:i A') }}</td></tr>
                <tr><th>Paid At</th><td>{{ optional($eventBooking->paid_at)->format('d-M-Y h:i A') ?: '-' }}</td></tr>
                <tr><th>Razorpay Order ID</th><td>{{ $eventBooking->razorpay_order_id ?: '-' }}</td></tr>
                <tr><th>Razorpay Payment ID</th><td>{{ $eventBooking->razorpay_payment_id ?: '-' }}</td></tr>
            </tbody>
        </table>

        <h5>Generated Tickets</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ticket Code</th>
                        <th>Status</th>
                        <th>Issued At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventBooking->tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->id }}</td>
                            <td>{{ $ticket->ticket_code }}</td>
                            <td>{{ ucfirst($ticket->status) }}</td>
                            <td>{{ optional($ticket->issued_at)->format('d-M-Y h:i A') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ trans('global.no_entries_in_table') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h5 class="mt-4">Delegates</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Company</th>
                        <th>Country</th>
                        <th>Email</th>
                        <th>Mobile</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventBooking->delegates as $delegate)
                        <tr>
                            <td>{{ $delegate->delegate_index }}</td>
                            <td>{{ $delegate->delegate_name }}</td>
                            <td>{{ $delegate->designation ?: '-' }}</td>
                            <td>{{ $delegate->company_name ?: '-' }}</td>
                            <td>{{ $delegate->country ?: '-' }}</td>
                            <td>{{ $delegate->email }}</td>
                            <td>{{ $delegate->mobile ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">{{ trans('global.no_entries_in_table') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
