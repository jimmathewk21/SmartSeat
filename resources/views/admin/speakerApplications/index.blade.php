@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        Speaker Applications
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.speaker-applications.index') }}" class="filter-form mb-3">
            <div class="form-row">
                <div class="col-md-5 mb-2">
                    <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, organization, topic">
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="">All Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.speaker-applications.index') }}">Clear</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Official Email</th>
                        <th>Organization</th>
                        <th>Topic</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $application)
                        <tr>
                            <td>
                                <a class="btn btn-xs btn-primary" href="{{ route('admin.speaker-applications.show', $application) }}">View</a>
                                <form action="{{ route('admin.speaker-applications.destroy', $application) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this application?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                            <td>{{ $application->id }}</td>
                            <td>{{ $application->full_name }}</td>
                            <td>{{ $application->official_email }}</td>
                            <td>{{ $application->organization ?: '-' }}</td>
                            <td>{{ $application->proposed_topic_title ?: '-' }}</td>
                            <td>{{ ucfirst($application->status) }}</td>
                            <td>{{ optional($application->created_at)->format('d-M-Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No entries in table</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $applications->links() }}
    </div>
</div>
@endsection
