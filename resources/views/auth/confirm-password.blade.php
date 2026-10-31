@extends('layouts.auth')
@section('title', 'Confirm your password')
@section('form')
<span class="eyebrow">A quick security check</span><h2 id="auth-heading">It's you. Confirmed.</h2><p class="muted">Enter your current password to confirm your identity.</p>
<x-form-errors />
<form method="POST" action="{{ route('password.confirm.store') }}" class="account-form">
    @csrf
    <div class="field"><label for="password">Current password</label><input id="password" name="password" type="password" autocomplete="current-password" required autofocus></div>
    <button class="button button-full" type="submit">Confirm password <span aria-hidden="true">→</span></button>
</form>
<a class="back-link" href="{{ route('dashboard') }}">← Back to dashboard</a>
@endsection
