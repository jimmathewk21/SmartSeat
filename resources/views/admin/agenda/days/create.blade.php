@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Create Agenda Day</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.agenda.days.store') }}">
            @csrf
            @include('admin.agenda.days.partials.form', ['day' => null])
            <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
        </form>
    </div>
</div>
@endsection

