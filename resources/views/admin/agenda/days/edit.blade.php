@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Edit Agenda Day</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.agenda.days.update', $day->id) }}">
            @csrf
            @method('PUT')
            @include('admin.agenda.days.partials.form', ['day' => $day])
            <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
        </form>
    </div>
</div>
@endsection

