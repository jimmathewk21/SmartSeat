@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        Speaker Application #{{ $speakerApplication->id }}
    </div>

    <div class="card-body">
        <div class="mb-3">
            <a class="btn btn-default" href="{{ route('admin.speaker-applications.index') }}">Back to list</a>
        </div>

        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th>Name</th><td>{{ $speakerApplication->full_name }}</td></tr>
                <tr><th>Gender</th><td>{{ $speakerApplication->gender ? str_replace('_', ' ', ucfirst($speakerApplication->gender)) : '-' }}</td></tr>
                <tr><th>Nationality</th><td>{{ $speakerApplication->nationality ?: ($speakerApplication->country ?: '-') }}</td></tr>
                <tr><th>Official Email</th><td>{{ $speakerApplication->official_email }}</td></tr>
                <tr><th>Personal Email</th><td>{{ $speakerApplication->personal_email ?: '-' }}</td></tr>
                <tr><th>Mobile</th><td>{{ $speakerApplication->mobile ?: '-' }}</td></tr>
                <tr><th>Alternate Mobile</th><td>{{ $speakerApplication->alternate_mobile ?: '-' }}</td></tr>
                <tr><th>Designation</th><td>{{ $speakerApplication->designation ?: '-' }}</td></tr>
                <tr><th>Organization</th><td>{{ $speakerApplication->organization ?: '-' }}</td></tr>
                <tr><th>City</th><td>{{ $speakerApplication->city ?: '-' }}</td></tr>
                <tr><th>State</th><td>{{ $speakerApplication->state ?: '-' }}</td></tr>
                <tr><th>Country</th><td>{{ $speakerApplication->country ?: ($speakerApplication->nationality ?: '-') }}</td></tr>
                <tr><th>Topic</th><td>{{ $speakerApplication->proposed_topic_title ?: '-' }}</td></tr>
                <tr><th>Presentation Mode</th><td>{{ $speakerApplication->presentation_mode ?: '-' }}</td></tr>
                <tr><th>Speaking Slot Duration</th><td>{{ $speakerApplication->presentation_duration ?: '-' }}</td></tr>
                <tr><th>AV Support Required</th><td>{{ $speakerApplication->av_requirements ?: '-' }}</td></tr>
                <tr><th>Can Book Own Air Tickets</th><td>{{ $speakerApplication->travel_support_required ? 'Yes' : 'No' }}</td></tr>
                <tr><th>Can Cover Hotel Stay and Logistics</th><td>{{ $speakerApplication->sub_theme ?: '-' }}</td></tr>
                <tr><th>Can Register and Pay Online</th><td>{{ $speakerApplication->session_type ?: '-' }}</td></tr>
                <tr><th>Interested in Sponsorship</th><td>{{ $speakerApplication->previous_engagements ?: '-' }}</td></tr>
                <tr><th>Special Requirements / Remarks</th><td>{{ $speakerApplication->special_requirements ?: '-' }}</td></tr>
                <tr><th>Areas of Expertise</th><td>{{ $speakerApplication->areas_of_expertise ?: '-' }}</td></tr>
                <tr><th>Years of Experience</th><td>{{ $speakerApplication->years_of_experience ?: '-' }}</td></tr>
                <tr><th>Bio</th><td>{{ $speakerApplication->short_bio ?: '-' }}</td></tr>
                <tr><th>Abstract</th><td>{{ $speakerApplication->abstract_summary ?: '-' }}</td></tr>
                <tr><th>Publications / Awards</th><td>{{ $speakerApplication->publications_awards ?: '-' }}</td></tr>
                <tr><th>LinkedIn</th><td>{{ $speakerApplication->linkedin_url ?: '-' }}</td></tr>
                <tr><th>Website</th><td>{{ $speakerApplication->website_url ?: '-' }}</td></tr>
                <tr><th>Status</th><td>{{ ucfirst($speakerApplication->status) }}</td></tr>
                <tr><th>IP Address</th><td>{{ $speakerApplication->ip_address ?: '-' }}</td></tr>
                <tr><th>Submitted At</th><td>{{ optional($speakerApplication->created_at)->format('d-M-Y h:i A') }}</td></tr>
                @if($speakerApplication->photo_url)
                    <tr>
                        <th>Photo</th>
                        <td><img src="{{ asset(ltrim($speakerApplication->photo_url, '/')) }}" alt="{{ $speakerApplication->full_name }}" style="max-width: 180px;"></td>
                    </tr>
                @endif
            </tbody>
        </table>

        <form method="POST" action="{{ route('admin.speaker-applications.update', $speakerApplication) }}">
            @csrf
            @method('PUT')
            <div class="form-row">
                <div class="col-md-3 form-group">
                    <label>Status</label>
                    <select class="form-control" name="status" required>
                        <option value="pending" @selected($speakerApplication->status === 'pending')>Pending</option>
                        <option value="approved" @selected($speakerApplication->status === 'approved')>Approved</option>
                        <option value="rejected" @selected($speakerApplication->status === 'rejected')>Rejected</option>
                    </select>
                </div>
                <div class="col-md-9 form-group">
                    <label>Review Notes</label>
                    <textarea class="form-control" name="review_notes" rows="3">{{ old('review_notes', $speakerApplication->review_notes) }}</textarea>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Update</button>
        </form>
    </div>
</div>
@endsection
