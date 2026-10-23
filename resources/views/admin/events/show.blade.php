@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">{{ trans('global.show') }} {{ trans('cruds.event.title_singular') }}</div>
    <div class="card-body">
        <div class="form-group"><a class="btn btn-default" href="{{ route('admin.events.index') }}">{{ trans('global.back_to_list') }}</a></div>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th>{{ trans('cruds.event.fields.id') }}</th><td>{{ $event->id }}</td></tr>
                <tr><th>{{ trans('cruds.event.fields.title') }}</th><td>{{ $event->title }}</td></tr>
                <tr><th>{{ trans('cruds.event.fields.slug') }}</th><td>{{ $event->slug }}</td></tr>
                <tr><th>{{ trans('cruds.event.fields.venue') }}</th><td>{{ $event->venue }}</td></tr>
                <tr><th>{{ trans('cruds.event.fields.location') }}</th><td>{{ $event->location }}</td></tr>
                <tr><th>{{ trans('cruds.event.fields.start_at') }}</th><td>{{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('d-M-Y') : '' }}</td></tr>
                <tr><th>{{ trans('cruds.event.fields.end_at') }}</th><td>{{ $event->end_at ? \Carbon\Carbon::parse($event->end_at)->format('d-M-Y h:i A') : '' }}</td></tr>
                <tr><th>{{ trans('cruds.event.fields.is_published') }}</th><td>{{ $event->is_published ? trans('global.yes') : trans('global.no') }}</td></tr>
                <tr><th>{{ trans('cruds.event.fields.description') }}</th><td>{{ $event->description }}</td></tr>
                <tr><th>Card Image</th><td>@if($event->image_url)<img src="{{ str_starts_with($event->image_url, 'http://') || str_starts_with($event->image_url, 'https://') || str_starts_with($event->image_url, '//') || str_starts_with($event->image_url, '/') ? $event->image_url : asset(ltrim($event->image_url, '/')) }}" alt="{{ $event->title }}" style="max-width:140px">@endif</td></tr>
                <tr><th>Gallery Link</th><td>{{ $event->gallery_url ?: '-' }}</td></tr>
                <tr><th>Brochure Link</th><td>{{ $event->brochure_url ?: '-' }}</td></tr>
                <tr><th>Ticket Page Top Label</th><td>{{ $event->ticket_hero_label ?: 'TICKET BOOKING' }}</td></tr>
                <tr><th>Ticket Page Title Suffix</th><td>{{ $event->ticket_hero_suffix ?: '/ reserve your seat' }}</td></tr>
                <tr><th>Ticket Page Description</th><td>{{ $event->ticket_hero_description ?: 'Choose the best registration package for this event, review the included benefits, and continue to a secure checkout experience.' }}</td></tr>
            </tbody>
        </table>
        <h5>{{ trans('global.relatedData') }}</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Sort</th>
                        <th>Name</th>
                        <th>MRP (INR)</th>
                        <th>Sale (INR)</th>
                        <th>MRP (USD)</th>
                        <th>Sale (USD)</th>
                        <th>Active</th>
                        <th>Valid Till</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($event->ticketPlans as $plan)
                        <tr>
                            <td>{{ $plan->sort_order }}</td>
                            <td>{{ $plan->name }}</td>
                            <td>&#8377;{{ number_format($plan->mrp_inr_paise / 100, 2) }}</td>
                            <td>&#8377;{{ number_format($plan->sale_inr_paise / 100, 2) }}</td>
                            <td>${{ number_format($plan->mrp_usd_cents / 100, 2) }}</td>
                            <td>${{ number_format($plan->sale_usd_cents / 100, 2) }}</td>
                            <td>{{ $plan->is_active ? trans('global.yes') : trans('global.no') }}</td>
                            <td>{{ $plan->valid_till_text }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">{{ trans('global.no_entries_in_table') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead><tr><th>{{ trans('cruds.speaker.fields.id') }}</th><th>{{ trans('cruds.speaker.fields.name') }}</th><th>{{ trans('cruds.speaker.fields.designation') }}</th></tr></thead>
                <tbody>
                    @forelse($event->speakers as $speaker)
                        <tr><td>{{ $speaker->id }}</td><td>{{ $speaker->name }}</td><td>{{ $speaker->designation }}</td></tr>
                    @empty
                        <tr><td colspan="3">{{ trans('global.no_entries_in_table') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="form-group"><a class="btn btn-default" href="{{ route('admin.events.index') }}">{{ trans('global.back_to_list') }}</a></div>
    </div>
</div>
@endsection
