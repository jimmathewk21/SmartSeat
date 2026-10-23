@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Edit Hotel</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.venue-hotels.update', $venueHotel) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.venueHotels._form')
        </form>
    </div>
</div>
@endsection

