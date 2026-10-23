@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Edit Page Title</div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.frontend.seo-titles.update', $seoTitle) }}">
                @csrf
                @method('PUT')
                @include('admin.frontend.seo-titles._form')

                <button class="btn btn-primary" type="submit">{{ trans('global.save') }}</button>
                <a class="btn btn-default" href="{{ route('admin.frontend.seo-titles.index') }}">{{ trans('global.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
