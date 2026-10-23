@extends('train-owner.layout')

@section('title', 'Edit train')

@section('content')
    <h1>Edit train information</h1>
    <p><a href="{{ route('train-owner.trains.index') }}">Back to your trains</a></p>
    <form method="POST" action="{{ route('train-owner.trains.update', $train) }}">
        @csrf
        @method('PUT')
        <label for="code">Train code</label>
        <input id="code" name="code" value="{{ old('code', $train->code) }}" required maxlength="20">
        <label for="name">Train name</label>
        <input id="name" name="name" value="{{ old('name', $train->name) }}" required maxlength="255">
        <label for="is_active">Service status</label>
        <select id="is_active" name="is_active" required>
            <option value="1" @selected((string) old('is_active', (int) $train->is_active) === '1')>Active</option>
            <option value="0" @selected((string) old('is_active', (int) $train->is_active) === '0')>Inactive</option>
        </select>
        <p><button type="submit">Save changes</button></p>
    </form>
@endsection
