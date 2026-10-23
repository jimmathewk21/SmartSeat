@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">{{ trans('global.show') }} {{ trans('cruds.faq.title_singular') }}</div>
    <div class="card-body">
        <div class="form-group"><a class="btn btn-default" href="{{ route('admin.faqs.index') }}">{{ trans('global.back_to_list') }}</a></div>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th>{{ trans('cruds.faq.fields.id') }}</th><td>{{ $faq->id }}</td></tr>
                <tr><th>{{ trans('cruds.faq.fields.category') }}</th><td>{{ $faq->category?->name }}</td></tr>
                <tr><th>{{ trans('cruds.faq.fields.question') }}</th><td>{{ $faq->question }}</td></tr>
                <tr><th>{{ trans('cruds.faq.fields.answer') }}</th><td>{{ $faq->answer }}</td></tr>
                <tr><th>{{ trans('cruds.faq.fields.show_on_homepage') }}</th><td>{{ $faq->show_on_homepage ? trans('global.yes') : trans('global.no') }}</td></tr>
                <tr><th>{{ trans('cruds.faq.fields.is_active') }}</th><td>{{ $faq->is_active ? trans('global.yes') : trans('global.no') }}</td></tr>
            </tbody>
        </table>
        <div class="form-group"><a class="btn btn-default" href="{{ route('admin.faqs.index') }}">{{ trans('global.back_to_list') }}</a></div>
    </div>
</div>
@endsection
