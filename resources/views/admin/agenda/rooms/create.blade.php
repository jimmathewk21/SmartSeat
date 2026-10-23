@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Create Agenda Room</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.agenda.rooms.store') }}">
            @csrf
            @include('admin.agenda.rooms.partials.form', ['room' => null])
            <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
        </form>
    </div>
</div>
@endsection

