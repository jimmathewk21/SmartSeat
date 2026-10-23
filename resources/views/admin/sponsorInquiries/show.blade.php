@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        View Sponsor Inquiry
    </div>

    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.sponsor-inquiries.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr><th>ID</th><td>{{ $sponsor_inquiry->id }}</td></tr>
                    <tr><th>Name</th><td>{{ $sponsor_inquiry->name }}</td></tr>
                    <tr><th>Job Title</th><td>{{ $sponsor_inquiry->job_title ?: '-' }}</td></tr>
                    <tr><th>Email</th><td>{{ $sponsor_inquiry->email }}</td></tr>
                    <tr><th>Phone</th><td>{{ $sponsor_inquiry->phone ?: '-' }}</td></tr>
                    <tr><th>Company Name</th><td>{{ $sponsor_inquiry->company_name }}</td></tr>
                    <tr><th>Country</th><td>{{ $sponsor_inquiry->country ?: '-' }}</td></tr>
                    <tr><th>Category</th><td>{{ $sponsor_inquiry->category ?: '-' }}</td></tr>
                    <tr><th>Website</th><td>@if($sponsor_inquiry->website_url)<a href="{{ $sponsor_inquiry->website_url }}" target="_blank" rel="noopener noreferrer">{{ $sponsor_inquiry->website_url }}</a>@else - @endif</td></tr>
                    <tr><th>LinkedIn URL</th><td>@if($sponsor_inquiry->linkedin_url)<a href="{{ $sponsor_inquiry->linkedin_url }}" target="_blank" rel="noopener noreferrer">{{ $sponsor_inquiry->linkedin_url }}</a>@else - @endif</td></tr>
                    <tr><th>Facebook URL</th><td>@if($sponsor_inquiry->facebook_url)<a href="{{ $sponsor_inquiry->facebook_url }}" target="_blank" rel="noopener noreferrer">{{ $sponsor_inquiry->facebook_url }}</a>@else - @endif</td></tr>
                    <tr><th>Company Logo</th><td>@if($sponsor_inquiry->company_logo_url)<a href="{{ asset(ltrim($sponsor_inquiry->company_logo_url, '/')) }}" target="_blank" rel="noopener noreferrer">View Logo</a>@else - @endif</td></tr>
                    <tr><th>Company Profile</th><td>{!! nl2br(e($sponsor_inquiry->company_profile)) !!}</td></tr>
                    <tr><th colspan="2" style="background:#f8fafc;">SPONSORSHIP DETAILS</th></tr>
                    <tr><th>Select Sponsorship Categories</th><td>{{ collect($sponsor_inquiry->sponsorship_categories)->filter()->implode(', ') ?: '-' }}</td></tr>
                    <tr><th>Enter Your Budget</th><td>{{ $sponsor_inquiry->budget_range ?: '-' }}</td></tr>
                    <tr><th>Place company logo on conference website and brochure</th><td>{{ $sponsor_inquiry->benefit_logo_placement ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Place company logo in conference backdrops</th><td>{{ $sponsor_inquiry->benefit_backdrop_logo ?: '-' }}</td></tr>
                    <tr><th>Receive participant list (without contact details) 14 days before event</th><td>{{ $sponsor_inquiry->benefit_participant_list ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Distribute promotional materials during conference</th><td>{{ $sponsor_inquiry->benefit_promotional_materials ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>A4 size advertisement inside conference brochure</th><td>{{ $sponsor_inquiry->benefit_two_a4_advert ? 'Two (2) Page A4 Advert' : ($sponsor_inquiry->benefit_one_a4_advert ? 'One (1) Page A4 Advert' : 'None') }}</td></tr>
                    <tr><th>Required registrations for company participation</th><td>{{ (int) ($sponsor_inquiry->total_registrations_required ?? 0) >= 6 ? 'More than 5 Registrations' : (((int) ($sponsor_inquiry->total_registrations_required ?? 0)) > 0 ? ((int) $sponsor_inquiry->total_registrations_required) . ' Registration' . ((int) $sponsor_inquiry->total_registrations_required > 1 ? 's' : '') : '-') }}</td></tr>
                    <tr><th>Promote company on LinkedIn by sharing posts</th><td>{{ $sponsor_inquiry->benefit_social_media_posts ?: '-' }}</td></tr>
                    <tr><th>Speaking slot in panel discussions</th><td>{{ $sponsor_inquiry->benefit_speaking_slot ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Book exhibition space at the event</th><td>{{ $sponsor_inquiry->benefit_exhibit_space ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Roll-up standees during conference</th><td>{{ $sponsor_inquiry->rollup_standees_option ?: ($sponsor_inquiry->benefit_rollup_standees ? 'Required' : 'Not required') }}</td></tr>
                    <tr><th>Consent: information accurate</th><td>{{ $sponsor_inquiry->consent_accuracy ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Consent: reviewed within 24-72 hours</th><td>{{ $sponsor_inquiry->consent_review_time ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Consent: approval required</th><td>{{ $sponsor_inquiry->consent_approval_required ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Additional Note/Message</th><td>{!! nl2br(e($sponsor_inquiry->message)) !!}</td></tr>
                    <tr><th>Submitted At</th><td>{{ optional($sponsor_inquiry->created_at)->format('d-M-Y h:i A') }}</td></tr>
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.sponsor-inquiries.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
    </div>
</div>
@endsection
