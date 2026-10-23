@extends('layouts.admin')
@section('content')
@php
    $logo = trim((string) ($sponsor->logo_url ?? ''));
    $isAbsoluteLogo = str_starts_with($logo, 'http://')
        || str_starts_with($logo, 'https://')
        || str_starts_with($logo, '//')
        || str_starts_with($logo, '/');
    $logoSrc = $logo === '' ? '' : ($isAbsoluteLogo ? $logo : asset(ltrim($logo, '/')));
@endphp
<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.sponsor.title_singular') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.sponsors.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr><th>{{ trans('cruds.sponsor.fields.id') }}</th><td>{{ $sponsor->id }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.sponsor_category') }}</th><td>{{ $sponsor->sponsorCategory?->name }}</td></tr>
                    <tr><th>All Categories</th><td>{{ $sponsor->sponsorCategories->pluck('name')->implode(', ') ?: '-' }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.name') }}</th><td>{{ $sponsor->name }}</td></tr>
                    <tr><th>Short Name</th><td>{{ $sponsor->sub_category ?: '-' }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.logo_url') }}</th><td>@if($logoSrc !== '')<img src="{{ $logoSrc }}" alt="{{ $sponsor->name }}" class="img-fluid" style="max-width:120px">@endif</td></tr>
                    <tr><th>Logo Size</th><td>{{ strtoupper($sponsor->logo_size ?? 'md') }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.website_url') }}</th><td>@if($sponsor->website_url)<a href="{{ $sponsor->website_url }}" target="_blank" rel="noopener noreferrer">{{ $sponsor->website_url }}</a>@endif</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.show_website_url') }}</th><td>{{ ($sponsor->show_website_url ?? true) ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>LinkedIn URL</th><td>@if($sponsor->linkedin_url)<a href="{{ $sponsor->linkedin_url }}" target="_blank" rel="noopener noreferrer">{{ $sponsor->linkedin_url }}</a>@endif</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.show_linkedin_url') }}</th><td>{{ ($sponsor->show_linkedin_url ?? true) ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.sort_order') }}</th><td>{{ $sponsor->sort_order }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.detail') }}</th><td>{!! nl2br(e($sponsor->detail)) !!}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.show_bio') }}</th><td>{{ ($sponsor->show_bio ?? true) ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.asset_type') }}</th><td>{{ ucfirst($sponsor->asset_type ?? '') }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.resource_url') }}</th><td>@if($sponsor->resource_url)<a href="{{ $sponsor->resource_url }}" target="_blank" rel="noopener noreferrer">Link</a>@endif</td></tr>
                    <tr><th>Contact Name</th><td>{{ $sponsor->contact_name }}</td></tr>
                    <tr><th>Contact Designation</th><td>{{ $sponsor->contact_designation }}</td></tr>
                    <tr><th>Contact Mobile</th><td>{{ $sponsor->contact_mobile }}</td></tr>
                    <tr><th>Contact Email</th><td>{{ $sponsor->contact_email }}</td></tr>
                    <tr><th>Advert</th><td>@if($sponsor->advert_url)<a href="{{ str_starts_with($sponsor->advert_url, 'http') || str_starts_with($sponsor->advert_url, '/') ? $sponsor->advert_url : asset(ltrim($sponsor->advert_url, '/')) }}" target="_blank" rel="noopener noreferrer">Download</a>@endif</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.show_advert') }}</th><td>{{ ($sponsor->show_advert ?? true) ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>Brochure</th><td>@if($sponsor->brochure_url)<a href="{{ str_starts_with($sponsor->brochure_url, 'http') || str_starts_with($sponsor->brochure_url, '/') ? $sponsor->brochure_url : asset(ltrim($sponsor->brochure_url, '/')) }}" target="_blank" rel="noopener noreferrer">Download</a>@endif</td></tr>
                    <tr><th>Sponsor Video</th><td>@if($sponsor->sponsor_video_url)<a href="{{ $sponsor->sponsor_video_url }}" target="_blank" rel="noopener noreferrer">{{ $sponsor->sponsor_video_url }}</a>@endif</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.show_on_homepage') }}</th><td>{{ $sponsor->show_on_homepage ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.show_on_scrollbar') }}</th><td>{{ $sponsor->show_on_scrollbar ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.show_contact_details') }}</th><td>{{ ($sponsor->show_contact_details ?? true) ? trans('global.yes') : trans('global.no') }}</td></tr>
                    <tr><th>{{ trans('cruds.sponsor.fields.is_active') }}</th><td>{{ $sponsor->is_active ? trans('global.yes') : trans('global.no') }}</td></tr>
                </tbody>
            </table>
        </div>
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.sponsors.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
    </div>
</div>
@endsection
