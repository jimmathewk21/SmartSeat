@extends('layouts.railway-admin')
@section('title', 'Trip scheduling')
@section('management')
<div class="page-heading"><div><span class="eyebrow">{{ $train ? $train->number.' · '.$train->name : 'Journeys on the calendar' }}</span><h1>Trips & inventory.</h1><p class="muted">A trip is one train's journey on its origin departure date.</p></div></div>
@if($train)<section class="panel railway-form"><h2>Schedule a trip</h2><p class="muted">Scheduling freezes this train's route, coaches and seats. Finish configuration first. Initialize inventory separately.</p>
<form method="POST" action="{{ route('administrator.trains.trips.store', $train) }}">@csrf<x-railway-fields type="trip" /><button class="button" type="submit">Schedule trip →</button></form></section>
@else<p><a class="text-link" href="{{ route('administrator.trains.index') }}">Choose a train to schedule a trip →</a></p>@endif
<section class="panel railway-list"><div class="panel-heading"><h2>Scheduled journeys</h2></div>
@if($trips->isEmpty())<div class="empty-state"><h3>No trips scheduled yet</h3><p>Complete a train's route and seating, then schedule its first journey.</p></div>
@else<div class="table-scroll"><table><thead><tr><th>Actions</th><th>Train</th><th>Origin date</th><th>Status</th><th>Inventory seats</th></tr></thead><tbody>
@foreach($trips as $trip)<tr><td><a href="{{ route('administrator.inventory.index', $trip) }}">Inventory</a></td><td>{{ $trip->train->number }} · {{ $trip->train->name }}</td><td>{{ $trip->service_date->format('d-M-Y') }}</td><td>{{ ucfirst($trip->status) }}</td><td>{{ $trip->inventory_count }}</td></tr>@endforeach
</tbody></table></div>{{ $trips->links('components.pagination') }}@endif</section>
@endsection
