@extends('layouts.railway-admin')
@section('title', 'Stations')
@section('management')
<div class="page-heading"><div><span class="eyebrow">The places we connect</span><h1>Stations.</h1><p class="muted">Create the station catalogue explicitly. No records are added automatically.</p></div></div>
<section class="panel railway-form"><h2>Add a station</h2>
    <form method="POST" action="{{ route('administrator.stations.store') }}">@csrf
        <x-railway-fields type="station" /><button class="button" type="submit">Add station →</button>
    </form>
</section>
<section class="panel railway-list"><div class="panel-heading"><h2>Station catalogue</h2><span>{{ $stations->total() }} stations</span></div>
@if($stations->isEmpty())<div class="empty-state"><h3>No stations configured yet</h3><p>Add a station above to begin building your network.</p></div>
@else<div class="table-scroll"><table><thead><tr><th>Actions</th><th>Code</th><th>Station</th><th>City</th></tr></thead><tbody>
@foreach($stations as $station)<tr><td><a href="{{ route('administrator.stations.edit', $station) }}">Edit</a></td><td><strong>{{ $station->code }}</strong></td><td>{{ $station->name }}</td><td>{{ $station->city }}</td></tr>@endforeach
</tbody></table></div>{{ $stations->links('components.pagination') }}@endif
</section>
@endsection
