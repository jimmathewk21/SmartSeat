@extends('layouts.railway-admin')
@section('title', 'Route configuration')
@section('management')
<div class="page-heading"><div><span class="eyebrow">{{ $train->number }} · {{ $train->name }}</span><h1>The journey, in order.</h1><p class="muted">Times are minutes after midnight on the origin date. Include each overnight day.</p></div></div>
<section class="panel railway-form"><h2>Add a route stop</h2><p class="muted">Set arrival and departure at every stop, including both endpoints. Use the same time when no dwell is needed.</p>
<form method="POST" action="{{ route('administrator.trains.stops.store', $train) }}">@csrf<x-railway-fields type="stop" /><button class="button" type="submit">Add stop →</button></form></section>
<section class="panel railway-list"><div class="panel-heading"><h2>Ordered route</h2><span>India time</span></div>
@if($stops->isEmpty())<div class="empty-state"><h3>No route stops configured yet</h3><p>Add at least two ordered stops before scheduling this train.</p></div>
@else<div class="table-scroll"><table><thead><tr><th>Actions</th><th>Position</th><th>Station</th><th>Arrival</th><th>Departure</th></tr></thead><tbody>
@foreach($stops as $stop)<tr><td><a href="{{ route('administrator.trains.stops.edit', [$train, $stop]) }}">Edit</a></td><td>{{ $stop->stop_order }}</td><td>{{ $stop->station->code }} · {{ $stop->station->name }}</td>
@foreach([$stop->arrival_offset_minutes, $stop->departure_offset_minutes] as $minutes)<td>Day {{ intdiv($minutes, 1440) }} · {{ sprintf('%02d:%02d', intdiv($minutes % 1440, 60), $minutes % 60) }}</td>@endforeach</tr>@endforeach
</tbody></table></div>{{ $stops->links('components.pagination') }}@endif</section>
@endsection
