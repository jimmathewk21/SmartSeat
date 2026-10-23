@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">World Event #{{ $worldEvent->id }}</div>
    <div class="card-body">
        <div class="mb-3">
            <a class="btn btn-default" href="{{ route('admin.world-events.index') }}">Back to list</a>
        </div>
        <table class="table table-bordered table-striped">
            <tbody>
            <tr><th>ID</th><td>{{ $worldEvent->id }}</td></tr>
            <tr><th>Name</th><td>{{ $worldEvent->name }}</td></tr>
            <tr><th>Start Date</th><td>{{ $worldEvent->start_date }}</td></tr>
            <tr><th>End Date</th><td>{{ $worldEvent->end_date }}</td></tr>
            <tr><th>Days</th><td>{{ $worldEvent->days ?: '-' }}</td></tr>
            <tr><th>City</th><td>{{ $worldEvent->city ?: '-' }}</td></tr>
            <tr><th>Country</th><td>{{ $worldEvent->country ?: '-' }}</td></tr>
            <tr><th>Theme</th><td>{{ $worldEvent->theme ?: '-' }}</td></tr>
            <tr><th>Website</th><td>{{ $worldEvent->website ?: '-' }}</td></tr>
            <tr><th>Stars</th><td>{{ $worldEvent->stars }}</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
