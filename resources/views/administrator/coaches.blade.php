@extends('layouts.railway-admin')
@section('title', 'Coaches')
@section('management')
<div class="page-heading"><div><span class="eyebrow">{{ $train->number }} · {{ $train->name }}</span><h1>Room for the journey.</h1><p class="muted">Create coaches, then explicitly configure their seats.</p></div></div>
<section class="panel railway-form"><h2>Add a coach</h2><form method="POST" action="{{ route('administrator.trains.coaches.store', $train) }}">@csrf<x-railway-fields type="coach" /><button class="button" type="submit">Add coach →</button></form></section>
<section class="panel railway-list"><div class="panel-heading"><h2>Coaches & seats</h2></div>
@if($coaches->isEmpty())<div class="empty-state"><h3>No coaches configured yet</h3><p>Add a coach above; seats are never generated automatically.</p></div>
@else<div class="table-scroll"><table><thead><tr><th>Actions</th><th>Coach</th><th>Class</th><th>Configured seats</th></tr></thead><tbody>
@foreach($coaches as $coach)<tr><td class="row-actions"><a href="{{ route('administrator.trains.coaches.edit', [$train, $coach]) }}">Edit</a><a href="{{ route('administrator.trains.coaches.seats.index', [$train, $coach]) }}">Seats</a></td><td>{{ $coach->label }}</td><td>{{ $coach->travel_class }}</td><td>{{ $coach->seats_count }}</td></tr>@endforeach
</tbody></table></div>{{ $coaches->links('components.pagination') }}@endif</section>
@endsection
