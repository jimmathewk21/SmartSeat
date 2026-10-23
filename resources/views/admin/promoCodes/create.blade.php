@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Create Promo Code</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.promo-codes.store') }}">
            @include('admin.promoCodes._form')
        </form>
    </div>
</div>
@endsection

