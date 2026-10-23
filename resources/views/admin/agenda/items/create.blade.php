@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Create Agenda Session</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.agenda.items.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.agenda.items.partials.form', ['item' => null])
            <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
        </form>
    </div>
</div>
@endsection
