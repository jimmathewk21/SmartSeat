@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Edit My Contact</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.my-contacts.update', $myContact) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.myContacts._form')
        </form>
    </div>
</div>
@endsection

