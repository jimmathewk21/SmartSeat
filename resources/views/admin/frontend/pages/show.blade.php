@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Show Page</div>
    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.frontend.pages.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>

        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th>ID</th><td>{{ $page->id }}</td></tr>
                <tr><th>Subtitle</th><td>{{ $page->subtitle }}</td></tr>
                <tr><th>Title</th><td>{{ $page->title }}</td></tr>
                <tr><th>Title Suffix</th><td>{{ $page->title_suffix }}</td></tr>
                <tr><th>Slug</th><td>{{ $page->slug }}</td></tr>
                <tr><th>Active</th><td>{{ $page->is_active ? trans('global.yes') : trans('global.no') }}</td></tr>
                <tr><th>Sort Order</th><td>{{ $page->sort_order }}</td></tr>
                <tr><th>Content</th><td>{!! $page->content !!}</td></tr>
            </tbody>
        </table>

        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.frontend.pages.index') }}">{{ trans('global.back_to_list') }}</a>
        </div>
    </div>
</div>
@endsection
