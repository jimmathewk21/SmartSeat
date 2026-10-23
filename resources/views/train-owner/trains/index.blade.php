@extends('train-owner.layout')

@section('title', 'Authorized trains')

@section('content')
    <h1>Trains you manage</h1>
    <p class="muted">Only trains assigned to your account are listed here.</p>
    @if($trains->isEmpty())
        <p>No trains have been assigned to your account. Contact a SmartSeat administrator.</p>
    @else
        <div style="overflow-x:auto">
            <table>
                <thead><tr><th>Train code</th><th>Name</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @foreach($trains as $train)
                    <tr>
                        <td>{{ $train->code }}</td><td>{{ $train->name }}</td><td>{{ $train->is_active ? 'Active' : 'Inactive' }}</td>
                        <td><a href="{{ route('train-owner.trains.edit', $train) }}">Edit train information</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $trains->links() }}
    @endif
@endsection
