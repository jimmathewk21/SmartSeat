@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Edit Agenda Room</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.agenda.rooms.update', $room->id) }}">
            @csrf
            @method('PUT')
            @include('admin.agenda.rooms.partials.form', ['room' => $room])
            <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
        </form>
    </div>
</div>
@endsection

