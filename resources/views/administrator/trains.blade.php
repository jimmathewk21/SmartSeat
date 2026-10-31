@extends('layouts.railway-admin')
@section('title', 'Train catalogue')
@section('management')
<div class="page-heading"><div><span class="eyebrow">A network in the making</span><h1>Train catalogue.</h1><p class="muted">Configure each train, then its route, coaches, seats and dated trips.</p></div></div>
<section class="panel railway-form"><h2>Add a train</h2>
<form method="POST" action="{{ route('administrator.trains.store') }}">@csrf<x-railway-fields type="train" /><button class="button" type="submit">Add train →</button></form>
</section>
<section class="panel railway-list"><div class="panel-heading"><h2>Your trains</h2><span>{{ $trains->total() }} trains</span></div>
@if($trains->isEmpty())<div class="empty-state"><h3>No trains configured yet</h3><p>Add a train to start configuring its journey.</p></div>
@else<div class="table-scroll"><table><thead><tr><th>Actions</th><th>Train</th><th>Configuration</th><th>Status</th></tr></thead><tbody>
@foreach($trains as $train)<tr><td class="row-actions">
@if(!$train->trips_count)<a href="{{ route('administrator.trains.edit', $train) }}">Edit</a>@endif
<a href="{{ route('administrator.trains.stops.index', $train) }}">Route</a><a href="{{ route('administrator.trains.coaches.index', $train) }}">Coaches</a><a href="{{ route('administrator.trains.trips.index', $train) }}">Trips</a></td>
<td><strong>{{ $train->number }}</strong><br>{{ $train->name }}</td><td>{{ $train->route_stops_count }} stops · {{ $train->coaches_count }} coaches · {{ $train->seats_count }} seats</td><td>{{ $train->is_active ? 'Active' : 'Inactive' }}{{ $train->trips_count ? ' · Catalogue frozen' : '' }}</td></tr>@endforeach
</tbody></table></div>{{ $trains->links('components.pagination') }}@endif
</section>
@endsection
