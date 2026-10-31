@extends('layouts.auth')
@section('title', 'Create your account')
@section('form')
<span class="eyebrow">Come aboard</span><h2 id="auth-heading">Your journey starts here.</h2><p class="muted">Create a passenger account. Make your next trip feel a little closer.</p>
<x-form-errors />
<form method="POST" action="{{ route('register.store') }}" class="account-form">
    @csrf
    <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ \App\Support\FormValue::text(old('name')) }}" autocomplete="name" maxlength="255" required autofocus></div>
    <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ \App\Support\FormValue::text(old('email')) }}" autocomplete="email" maxlength="255" required></div>
    <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="72" aria-describedby="password-help" required><small id="password-help">Use 12–72 characters with uppercase, lowercase, a number and a symbol.</small></div>
    <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" maxlength="72" required></div>
    <button class="button button-full" type="submit">Create passenger account <span aria-hidden="true">→</span></button>
</form>
<p class="form-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
