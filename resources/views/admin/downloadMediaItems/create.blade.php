@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.download-media-items.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-header">Add Download Item</div>
        <div class="card-body">
            @include('admin.downloadMediaItems._form')
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </div>
</form>
@endsection
