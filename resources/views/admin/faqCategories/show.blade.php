@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">{{ trans('global.show') }} {{ trans('cruds.faqCategory.title_singular') }}</div>
    <div class="card-body">
        <div class="form-group"><a class="btn btn-default" href="{{ route('admin.faq-categories.index') }}">{{ trans('global.back_to_list') }}</a></div>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th>{{ trans('cruds.faqCategory.fields.id') }}</th><td>{{ $faq_category->id }}</td></tr>
                <tr><th>{{ trans('cruds.faqCategory.fields.name') }}</th><td>{{ $faq_category->name }}</td></tr>
                <tr><th>{{ trans('cruds.faqCategory.fields.sort_order') }}</th><td>{{ $faq_category->sort_order }}</td></tr>
                <tr><th>{{ trans('cruds.faqCategory.fields.is_active') }}</th><td>{{ $faq_category->is_active ? trans('global.yes') : trans('global.no') }}</td></tr>
            </tbody>
        </table>
        <div class="form-group"><a class="btn btn-default" href="{{ route('admin.faq-categories.index') }}">{{ trans('global.back_to_list') }}</a></div>
    </div>
</div>
@endsection

