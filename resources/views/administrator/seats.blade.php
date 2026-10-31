@extends('layouts.railway-admin')
@section('title', 'Seat configuration')
@section('management')
<div class="page-heading"><div><span class="eyebrow">{{ $train->number }} · {{ $coach->label }} · {{ $coach->travel_class }}</span><h1>A place for every passenger.</h1><p class="muted">Each seat number must be unique within this coach.</p></div></div>
<section class="panel railway-form"><h2>Add a seat</h2><form method="POST" action="{{ route('administrator.trains.coaches.seats.store', [$train, $coach]) }}">@csrf<x-railway-fields type="seat" /><button class="button" type="submit">Add seat →</button></form></section>
<section class="panel railway-list"><div class="panel-heading"><h2>Seat configuration</h2></div>
@if($seats->isEmpty())<div class="empty-state"><h3>No seats configured yet</h3><p>Add the actual seats and berth types for this coach.</p></div>
@else<div class="table-scroll"><table><thead><tr><th>Actions</th><th>Seat</th><th>Berth type</th><th>Accessibility</th></tr></thead><tbody>
@foreach($seats as $seat)<tr><td><a href="{{ route('administrator.trains.coaches.seats.edit', [$train, $coach, $seat]) }}">Edit</a></td><td>{{ $seat->number }}</td><td>{{ str_replace('_', ' ', ucfirst($seat->berth_type)) }}</td><td>{{ str_replace('_', ' ', ucfirst($seat->accessibility_status)) }}</td></tr>@endforeach
</tbody></table></div>{{ $seats->links('components.pagination') }}@endif</section>
@endsection
