@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Edit Promo Code</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.promo-codes.update', $promoCode) }}">
            @method('PUT')
            @include('admin.promoCodes._form')
        </form>
    </div>
</div>
@endsection

