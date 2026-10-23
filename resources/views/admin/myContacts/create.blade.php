@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Create My Contact</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.my-contacts.store') }}" enctype="multipart/form-data">
            @include('admin.myContacts._form')
        </form>
    </div>
</div>
@endsection

