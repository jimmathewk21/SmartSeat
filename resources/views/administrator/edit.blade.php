@extends('layouts.railway-admin')
@section('title', 'Edit railway configuration')
@section('management')
<div class="page-heading"><div><span class="eyebrow">Catalogue maintenance</span><h1>Edit {{ $type }}.</h1><p class="muted">Changes are protected once records are used by routes or scheduled trips.</p></div></div>
<section class="panel railway-form">
    <form method="POST" action="{{ $action }}">
        @csrf @method('PUT')
        <x-railway-fields :type="$type" :record="$record" />
        <button class="button" type="submit">Save changes →</button>
    </form>
</section>
@endsection
