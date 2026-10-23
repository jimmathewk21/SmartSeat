@extends('layouts.admin')

@section('styles')
    @parent
    <style>
        .hotel-bookings-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .hotel-bookings-bulk-actions,
        .hotel-bookings-filter-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .hotel-bookings-checkbox-col,
        .hotel-bookings-checkbox-cell {
            width: 48px;
            text-align: center;
        }
        .hotel-bookings-checkbox-col input,
        .hotel-bookings-checkbox-cell input {
            width: 16px;
            height: 16px;
        }
        .hotel-bookings-filter-row {
            display: grid;
            grid-template-columns: minmax(220px, 1.2fr) minmax(200px, 1fr) minmax(180px, 1fr) auto;
            gap: 10px;
            align-items: center;
            width: 100%;
        }
        .hotel-bookings-filter-row .form-group {
            margin-bottom: 0;
            min-width: 0;
        }
        .hotel-bookings-filter-row .filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            flex-wrap: nowrap;
        }
        @media (max-width: 991px) {
            .hotel-bookings-filter-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 767px) {
            .hotel-bookings-filter-row {
                grid-template-columns: 1fr;
            }
            .hotel-bookings-filter-row .filter-actions {
                flex-wrap: wrap;
            }
        }
    </style>
@endsection

@section('content')
@php($statusLabels = ['new' => 'Pending', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Ignore', 'ignore' => 'Ignore'])
<div class="hotel-bookings-toolbar">
    <div class="hotel-bookings-bulk-actions">
        <a class="btn btn-outline-success" href="{{ route('admin.hotel-bookings.export', request()->query()) }}">Export CSV</a>
        <button type="button" class="btn btn-outline-primary" data-hotel-booking-export-selected>Export Selected</button>
        <button type="button" class="btn btn-secondary" data-hotel-booking-status="pending">Pending Selected</button>
        <button type="button" class="btn btn-success" data-hotel-booking-status="confirmed">Confirm Selected</button>
        <button type="button" class="btn btn-warning" data-hotel-booking-status="ignore">Ignore Selected</button>
        <button type="submit" class="btn btn-danger" form="hotelBookingDeleteForm" onclick="return confirm('Delete selected bookings?');">Delete Selected</button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Hotel Bookings
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.hotel-bookings.index') }}" class="filter-form mb-3">
            <div class="filter-panel">
                <div class="filter-panel__title">Filters</div>
                <div class="hotel-bookings-filter-row">
                    <div class="form-group">
                        <input class="form-control" type="text" name="guest_name" value="{{ request('guest_name') }}" placeholder="Search guest name">
                    </div>
                    <div class="form-group">
                        <select name="hotel_name" class="form-control">
                            <option value="">All Hotels</option>
                            @foreach($hotelNames as $hotelName)
                                <option value="{{ $hotelName }}" @selected(request('hotel_name') === $hotelName)>{{ $hotelName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                            <option value="ignore" @selected(request('status') === 'ignore')>Ignore</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-primary" type="submit">{{ trans('global.search') }}</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.hotel-bookings.index') }}">{{ trans('global.clear') }}</a>
                    </div>
                </div>
            </div>
        </form>

        <form id="hotelBookingBulkForm" method="POST" action="{{ route('admin.hotel-bookings.bulkStatus') }}">
            @csrf
            <input type="hidden" name="status" id="hotelBookingBulkStatusAction" value="">
        </form>
        <form id="hotelBookingDeleteForm" method="POST" action="{{ route('admin.hotel-bookings.massDestroy') }}">
            @csrf
            @method('DELETE')
        </form>
        <form id="hotelBookingExportSelectedForm" method="GET" action="{{ route('admin.hotel-bookings.export') }}"></form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th class="hotel-bookings-checkbox-col">
                        <input type="checkbox" id="select-all-hotel-bookings" aria-label="Select all hotel bookings">
                    </th>
                    <th>ID</th>
                    <th>Booking Code</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Country</th>
                    <th>Hotel</th>
                    <th>Room Category</th>
                    <th>No. of Rooms</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Occupancy</th>
                    <th>Bed Size</th>
                    <th>Preference</th>
                    <th>Special Requests</th>
                    <th>Booked At</th>
                    <th>INR</th>
                    <th>USD</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($hotelBookings as $booking)
                    <tr>
                        <td class="hotel-bookings-checkbox-cell">
                            <input type="checkbox" name="ids[]" value="{{ $booking->id }}" class="hotel-booking-row-checkbox" form="hotelBookingBulkForm">
                        </td>
                        <td>{{ $booking->id }}</td>
                        <td>{{ $booking->booking_code }}</td>
                        <td>{{ $booking->full_name }}</td>
                        <td>{{ $booking->email }}</td>
                        <td>{{ $booking->phone ?: '-' }}</td>
                        <td>{{ $booking->country ?: '-' }}</td>
                        <td>{{ $booking->hotel_name ?: '-' }}</td>
                        <td>{{ $booking->room_name ?: '-' }}</td>
                        <td>{{ $booking->number_of_rooms }}</td>
                        <td>{{ optional($booking->check_in_date)->format('d-M-Y') }}</td>
                        <td>{{ optional($booking->check_out_date)->format('d-M-Y') }}</td>
                        <td>{{ $booking->occupancy ? ucfirst($booking->occupancy) : '-' }}</td>
                        <td>{{ $booking->bed_size ?: '-' }}</td>
                        <td>{{ $booking->preference ?: '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($booking->special_requests ?: '-', 60) }}</td>
                        <td>{{ optional($booking->created_at)->format('d-M-Y h:i A') }}</td>
                        <td>{{ number_format((float) $booking->rate_inr, 2) }}</td>
                        <td>{{ number_format((float) $booking->rate_usd, 2) }}</td>
                        <td>{{ $statusLabels[$booking->status] ?? 'Pending' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="20">{{ trans('global.no_entries_in_table') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $hotelBookings->links() }}
    </div>
</div>
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var selectAll = document.getElementById('select-all-hotel-bookings');
            var rowCheckboxes = Array.from(document.querySelectorAll('.hotel-booking-row-checkbox'));
            var bulkForm = document.getElementById('hotelBookingBulkForm');
            var deleteForm = document.getElementById('hotelBookingDeleteForm');
            var exportSelectedForm = document.getElementById('hotelBookingExportSelectedForm');
            var bulkStatusInput = document.getElementById('hotelBookingBulkStatusAction');
            var bulkStatusButtons = Array.from(document.querySelectorAll('[data-hotel-booking-status]'));
            var exportSelectedButton = document.querySelector('[data-hotel-booking-export-selected]');

            function syncForm(form) {
                if (!form) {
                    return;
                }

                form.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
                    if (!input.closest('tbody')) {
                        input.remove();
                    }
                });

                rowCheckboxes.forEach(function (checkbox) {
                    if (!checkbox.checked) {
                        return;
                    }

                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ids[]';
                    hidden.value = checkbox.value;
                    form.appendChild(hidden);
                });
            }

            function selectedCount() {
                return rowCheckboxes.filter(function (checkbox) {
                    return checkbox.checked;
                }).length;
            }

            function syncSelectAll() {
                if (!selectAll) {
                    return;
                }

                selectAll.checked = rowCheckboxes.length > 0 && rowCheckboxes.every(function (checkbox) {
                    return checkbox.checked;
                });
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    rowCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                });
            }

            rowCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', syncSelectAll);
            });

            bulkStatusButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!bulkForm || selectedCount() === 0) {
                        window.alert('Select at least one booking first.');
                        return;
                    }

                    syncForm(bulkForm);
                    bulkStatusInput.value = button.getAttribute('data-hotel-booking-status');
                    bulkForm.submit();
                });
            });

            if (exportSelectedButton) {
                exportSelectedButton.addEventListener('click', function () {
                    if (!exportSelectedForm || selectedCount() === 0) {
                        window.alert('Select at least one booking first.');
                        return;
                    }

                    syncForm(exportSelectedForm);
                    exportSelectedForm.submit();
                });
            }

            if (deleteForm) {
                deleteForm.addEventListener('submit', function (event) {
                    if (selectedCount() === 0) {
                        event.preventDefault();
                        window.alert('Select at least one booking first.');
                        return;
                    }

                    syncForm(deleteForm);
                });
            }
        });
    </script>
@endsection
