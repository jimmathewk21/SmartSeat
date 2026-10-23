@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Hotel #{{ $venueHotel->id }}</div>
    <div class="card-body">
        <div class="mb-3">
            <a class="btn btn-default" href="{{ route('admin.venue-hotels.index') }}">Back to list</a>
        </div>
        <table class="table table-bordered table-striped">
            <tbody>
            <tr><th>ID</th><td>{{ $venueHotel->id }}</td></tr>
            <tr><th>Hotel</th><td>{{ $venueHotel->name }}</td></tr>
            <tr><th>City</th><td>{{ $venueHotel->city ?: '-' }}</td></tr>
            <tr><th>Country</th><td>{{ $venueHotel->country ?: '-' }}</td></tr>
            <tr><th>Stars</th><td>{{ $venueHotel->stars }}</td></tr>
            <tr><th>Status</th><td>{{ $venueHotel->is_active ? 'Active' : 'Inactive' }}</td></tr>
            <tr><th>Sort Order</th><td>{{ $venueHotel->sort_order }}</td></tr>
            <tr><th>Cover Image</th><td>{{ $venueHotel->hotel_image ?: '-' }}</td></tr>
            <tr><th>Address</th><td>{{ $venueHotel->hotel_address ?: '-' }}</td></tr>
            <tr><th>Description</th><td>{{ $venueHotel->hotel_description ?: '-' }}</td></tr>
            <tr><th>Room Section Heading</th><td>{{ $venueHotel->room_section_heading ?: '-' }}</td></tr>
            <tr><th>Room Section Text</th><td>{{ $venueHotel->room_section_text ?: '-' }}</td></tr>
            <tr><th>Booking From</th><td>{{ $venueHotel->booking_available_from ?: '-' }}</td></tr>
            <tr><th>Booking To</th><td>{{ $venueHotel->booking_available_to ?: '-' }}</td></tr>
            <tr><th>Direction From</th><td>{{ $venueHotel->direction_from ?: '-' }}</td></tr>
            <tr><th>Direction To</th><td>{{ $venueHotel->direction_to ?: '-' }}</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
