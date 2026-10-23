@extends('layouts.admin')
@section('content')
    <div class="card">
        <div class="card-header">Ticket Bookings {{ trans('global.list') }}</div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.event-bookings.index') }}" class="filter-form mb-3">
                <div class="filter-panel">
                    <div class="filter-panel__title">Filters</div>
                    <div class="form-row filter-panel__grid">
                        <div class="col-md-3 mb-2">
                            <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="Booking code / order ID / user">
                        </div>
                        <div class="col-md-2 mb-2">
                            <select class="form-control" name="event_id">
                                <option value="">All Events</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select class="form-control" name="status">
                                <option value="">All Status</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                                <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <input class="form-control" type="date" name="from_date" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2 mb-2">
                            <input class="form-control" type="date" name="to_date" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2 mb-2">
                            <input class="form-control" type="text" name="billing_country_code" value="{{ request('billing_country_code') }}" placeholder="Country code">
                        </div>
                        <div class="col-md-2 mb-2">
                            <select class="form-control" name="display_currency">
                                <option value="">Display Currency</option>
                                <option value="INR" @selected(request('display_currency') === 'INR')>INR</option>
                                <option value="USD" @selected(request('display_currency') === 'USD')>USD</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                    <a class="btn btn-outline-success" href="{{ route('admin.event-bookings.export', request()->query()) }}">Export CSV</a>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.event-bookings.index') }}">{{ trans('global.clear') }}</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('global.actions') }}</th>
                            <th>ID</th>
                            <th>Booking Code</th>
                            <th>Event</th>
                            <th>Plan</th>
                            <th>User</th>
                            <th>Qty</th>
                            <th>Total (INR)</th>
                            <th>Display Total</th>
                            <th>Country</th>
                            <th>FX</th>
                            <th>Status</th>
                            <th>Booking Date</th>
                            <th>Paid At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.event-bookings.show', $booking) }}">{{ trans('global.view') }}</a>
                                </td>
                                <td>{{ $booking->id }}</td>
                                <td>{{ $booking->booking_code }}</td>
                                <td>{{ $booking->event?->title }}</td>
                                <td>{{ $booking->plan?->name }}</td>
                                <td>{{ $booking->user?->name }}<br><small>{{ $booking->user?->email }}</small></td>
                                <td>{{ $booking->quantity }}</td>
                                <td>&#8377;{{ number_format($booking->total_amount_paise / 100, 2) }}</td>
                                <td>
                                    @if($booking->display_currency === 'USD')
                                        ${{ number_format(($booking->display_total_amount_minor ?? 0) / 100, 2) }}
                                    @else
                                        &#8377;{{ number_format(($booking->display_total_amount_minor ?? 0) / 100, 2) }}
                                    @endif
                                    ({{ $booking->display_currency ?: 'INR' }})
                                </td>
                                <td>{{ $booking->billing_country_code ?: '-' }}</td>
                                <td>{{ $booking->fx_rate_usd_to_inr ?: '-' }}</td>
                                <td>{{ ucfirst($booking->status) }}</td>
                                <td>{{ optional($booking->created_at)->format('d-M-Y h:i A') }}</td>
                                <td>{{ optional($booking->paid_at)->format('d-M-Y h:i A') ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="14">{{ trans('global.no_entries_in_table') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $bookings->links() }}
        </div>
    </div>
@endsection
