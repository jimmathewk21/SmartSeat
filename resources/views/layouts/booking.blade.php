@extends('layouts.app')
@section('content')
<div class="shell dashboard booking-workspace">
    @if(session('status'))<div class="admin-notice" role="status">{{ session('status') }}</div>@endif
    <x-form-errors />
    @yield('booking-content')
</div>
@endsection
