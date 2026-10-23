@extends('layouts.admin')
@section('content')
<div class="row page-actions">
    <div class="col-lg-12">
        <a class="btn btn-primary" href="{{ route('admin.venue-hotels.create') }}">Add Hotel</a>
    </div>
</div>

<div class="card">
    <div class="card-header">Manage Hotels/Rooms</div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.venue-hotels.index') }}" class="filter-form mb-3">
            <div class="form-row">
                <div class="col-md-5 mb-2">
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search hotels">
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-control" name="is_active">
                        <option value="">All Status</option>
                        <option value="1" @selected(request('is_active') === '1')>Active</option>
                        <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 d-flex">
                    <button class="btn btn-primary mr-1" type="submit">Go</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.venue-hotels.index') }}">X</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th>Actions</th>
                    <th>ID</th>
                    <th>Hotel</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Stars</th>
                    <th>Status</th>
                    <th>Sort</th>
                </tr>
                </thead>
                <tbody>
                @forelse($venueHotels as $venueHotel)
                    <tr>
                        <td>
                            <a class="btn btn-xs btn-primary" href="{{ route('admin.venue-hotels.show', $venueHotel) }}">View</a>
                            <a class="btn btn-xs btn-info" href="{{ route('admin.venue-hotels.edit', $venueHotel) }}">Edit</a>
                            <form action="{{ route('admin.venue-hotels.destroy', $venueHotel) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this hotel?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-xs btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                        <td>{{ $venueHotel->id }}</td>
                        <td>{{ $venueHotel->name }}</td>
                        <td>{{ $venueHotel->city ?: '-' }}</td>
                        <td>{{ $venueHotel->country ?: '-' }}</td>
                        <td>{{ $venueHotel->stars }}</td>
                        <td>{{ $venueHotel->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>{{ $venueHotel->sort_order }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8">{{ trans('global.no_entries_in_table') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $venueHotels->links() }}
    </div>
</div>
@endsection
