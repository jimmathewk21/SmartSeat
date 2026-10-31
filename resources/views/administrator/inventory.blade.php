@extends('layouts.railway-admin')
@section('title', 'Trip inventory')
@section('management')
<div class="page-heading"><div><span class="eyebrow">{{ $train->number }} · {{ $train->name }}</span><h1>Ready, seat by seat.</h1><p class="muted">Origin date {{ $trip->service_date->format('d-M-Y') }} · {{ ucfirst($trip->status) }}</p></div></div>
<section class="panel railway-form"><h2>Initialize this trip's inventory</h2><p class="muted">Copies only explicitly configured seats. Repeating this operation adds missing seats without resetting existing availability or assignments.</p>
<form method="POST" action="{{ route('administrator.inventory.store', $trip) }}">@csrf<button class="button" type="submit">Initialize missing inventory →</button></form></section>
<section class="panel railway-list"><div class="panel-heading"><h2>Per-trip seat inventory</h2><span>{{ $inventory->total() }} seats initialized</span></div>
@if($inventory->isEmpty())<div class="empty-state"><h3>No inventory initialized yet</h3><p>Initialize the trip above after configuring the train's seats.</p></div>
@else<div class="table-scroll"><table><thead><tr><th>Coach</th><th>Class</th><th>Seat</th><th>Berth</th><th>Availability</th></tr></thead><tbody>
@foreach($inventory as $item)<tr><td>{{ $item->seat->coach->label }}</td><td>{{ $item->seat->coach->travel_class }}</td><td>{{ $item->seat->number }}</td><td>{{ str_replace('_', ' ', $item->seat->berth_type) }}</td><td>{{ $item->activeAssignment ? 'Assigned' : ucfirst($item->status) }}</td></tr>@endforeach
</tbody></table></div>{{ $inventory->links('components.pagination') }}@endif</section>
@endsection
