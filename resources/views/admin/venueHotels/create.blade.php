@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Create Hotel</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.venue-hotels.store') }}" enctype="multipart/form-data">
            @include('admin.venueHotels._form')
        </form>
    </div>
</div>
@endsection

