@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.download-media-items.update', $downloadMediaItem) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-header">Edit Download Item</div>
        <div class="card-body">
            @include('admin.downloadMediaItems._form')
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <button class="btn btn-primary" type="submit">Update</button>
        </div>
    </div>
</form>
@endsection
