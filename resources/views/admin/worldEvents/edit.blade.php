@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Edit World Event</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.world-events.update', $worldEvent) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.worldEvents._form')
        </form>
    </div>
</div>
@endsection
