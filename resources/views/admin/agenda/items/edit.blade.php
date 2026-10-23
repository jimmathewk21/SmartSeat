@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Edit Agenda Session</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.agenda.items.update', $item->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.agenda.items.partials.form', ['item' => $item])
            <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
        </form>
    </div>
</div>
@endsection
