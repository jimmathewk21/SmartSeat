@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Hotel Booking Settings</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.hotel-booking.update') }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Hotel Name</label>
                    <input type="text" class="form-control" name="hotel_name" value="{{ old('hotel_name', $settings['hotel_name']) }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Admin Notification Email</label>
                    <input type="email" class="form-control" name="admin_notification_email" value="{{ old('admin_notification_email', $settings['admin_notification_email']) }}" placeholder="admin@example.com">
                </div>
                <div class="col-md-12 form-group">
                    <label>Hotel Address</label>
                    <textarea class="form-control" name="hotel_address" rows="2">{{ old('hotel_address', $settings['hotel_address']) }}</textarea>
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Allowed Booking Date Range</h6>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>From Date</label>
                    <input type="date" class="form-control" name="available_from_date" value="{{ old('available_from_date', $settings['available_from_date']) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label>To Date</label>
                    <input type="date" class="form-control" name="available_to_date" value="{{ old('available_to_date', $settings['available_to_date']) }}">
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Direction Settings (Google Maps)</h6>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Direction From</label>
                    <input type="text" class="form-control" name="direction_from" value="{{ old('direction_from', $settings['direction_from']) }}" placeholder="Airport, City">
                </div>
                <div class="col-md-6 form-group">
                    <label>Direction To</label>
                    <input type="text" class="form-control" name="direction_to" value="{{ old('direction_to', $settings['direction_to']) }}" placeholder="Hotel name/address">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
@endsection

