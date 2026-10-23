@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>My Contact #{{ $myContact->id }}</span>
        <a class="btn btn-default" href="{{ route('admin.my-contacts.index') }}">Back to list</a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-3 mb-4">
                @php
                    $picture = $myContact->picture_url
                        ? asset(ltrim($myContact->picture_url, '/'))
                        : asset(($myContact->gender ?? '') === 'Female' ? 'images/placeholders/contact-female.svg' : 'images/placeholders/contact-male.svg');
                @endphp
                <img src="{{ $picture }}" alt="{{ $myContact->name }}" class="img-fluid rounded border">
            </div>
            <div class="col-lg-9">
                <table class="table table-bordered table-striped">
                    <tbody>
                    <tr><th>Name</th><td>{{ $myContact->name }}</td></tr>
                    <tr><th>Designation</th><td>{{ $myContact->designation ?: '-' }}</td></tr>
                    <tr><th>Company</th><td>{{ $myContact->company_name ?: '-' }}</td></tr>
                    <tr><th>Gender</th><td>{{ $myContact->gender ?: '-' }}</td></tr>
                    <tr><th>Official Email</th><td>{{ $myContact->official_email }}</td></tr>
                    <tr><th>Personal Email</th><td>{{ $myContact->personal_email ?: '-' }}</td></tr>
                    <tr><th>Alternate Email</th><td>{{ $myContact->alternate_email ?: '-' }}</td></tr>
                    <tr><th>Mobile</th><td>{{ $myContact->mobile_number ?: '-' }}</td></tr>
                    <tr><th>WhatsApp</th><td>{{ $myContact->whatsapp_number ?: '-' }}</td></tr>
                    <tr><th>Alternate Mobile</th><td>{{ $myContact->alternate_mobile_number ?: '-' }}</td></tr>
                    <tr><th>Country</th><td>{{ $myContact->country ?: '-' }}</td></tr>
                    <tr><th>Category</th><td>{{ $myContact->category ?: '-' }}</td></tr>
                    <tr><th>Area of Expertise</th><td>{{ $myContact->specialization ?: '-' }}</td></tr>
                    <tr><th>Experience</th><td>{{ $myContact->experience ?: '-' }}</td></tr>
                    <tr><th>Speaker Tag</th><td>{{ $myContact->speaker_tag ?: '-' }}</td></tr>
                    <tr><th>Key Role</th><td>{{ $myContact->role ?: '-' }}</td></tr>
                    <tr><th>Primary Event</th><td>{{ $myContact->event_name ?: '-' }}</td></tr>
                    <tr><th>All Events</th><td>{{ $myContact->event_list ? implode(', ', $myContact->event_list) : '-' }}</td></tr>
                    <tr>
                        <th>Stars</th>
                        <td>
                            @php($starCount = max(0, (int) $myContact->stars))
                            {!! $starCount > 0 ? str_repeat('&#9733;', $starCount) : '-' !!}
                        </td>
                    </tr>
                    <tr><th>Status</th><td>{{ $myContact->status_label }}</td></tr>
                    <tr><th>Published On Frontend</th><td>{{ $myContact->is_published ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>LinkedIn</th><td>{{ $myContact->linkedin_profile ?: '-' }}</td></tr>
                    <tr><th>Website</th><td>{{ $myContact->web_url ?: '-' }}</td></tr>
                    <tr><th>Show Email-id</th><td>{{ $myContact->show_email ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Show Whatsapp</th><td>{{ $myContact->show_whatsapp ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Show Linked-In</th><td>{{ $myContact->show_linkedin ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Show Website</th><td>{{ $myContact->show_website ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Show Category</th><td>{{ $myContact->show_category ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Show Expertise</th><td>{{ $myContact->show_expertise ? 'Yes' : 'No' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
