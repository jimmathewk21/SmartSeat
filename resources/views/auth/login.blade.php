@extends('layouts.auth')
@section('title', 'Welcome back')
@section('form')
<span class="eyebrow">Back on track</span><h2 id="auth-heading">Welcome back.</h2><p class="muted">Log in to your SmartSeat account.</p>
<x-form-errors />
<form method="POST" action="{{ route('login.store') }}" class="account-form">
    @csrf
    <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ \App\Support\FormValue::text(old('email')) }}" autocomplete="username" maxlength="255" required autofocus></div>
    <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
    <label class="checkbox"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Keep me logged in on this device</label>
    <button class="button button-full" type="submit">Log in <span aria-hidden="true">→</span></button>
</form>
<p class="form-switch">New to SmartSeat? <a href="{{ route('register') }}">Create an account</a></p>
@endsection
