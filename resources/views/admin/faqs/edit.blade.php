@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">{{ trans('global.edit') }} {{ trans('cruds.faq.title_singular') }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.faqs.update', [$faq->id]) }}">
            @method('PUT')
            @csrf
            <div class="form-group"><label class="required" for="faq_category_id">{{ trans('cruds.faq.fields.category') }}</label><select class="form-control select2 {{ $errors->has('faq_category_id') ? 'is-invalid' : '' }}" name="faq_category_id" id="faq_category_id" required>@foreach($faqCategories as $id => $entry)<option value="{{ $id }}" {{ old('faq_category_id', $faq->faq_category_id) == $id ? 'selected' : '' }}>{{ $entry }}</option>@endforeach</select>@if($errors->has('faq_category_id'))<div class="invalid-feedback">{{ $errors->first('faq_category_id') }}</div>@endif</div>
            <div class="form-group"><label class="required" for="question">{{ trans('cruds.faq.fields.question') }}</label><input class="form-control {{ $errors->has('question') ? 'is-invalid' : '' }}" type="text" name="question" id="question" value="{{ old('question', $faq->question) }}" required>@if($errors->has('question'))<div class="invalid-feedback">{{ $errors->first('question') }}</div>@endif</div>
            <div class="form-group"><label class="required" for="answer">{{ trans('cruds.faq.fields.answer') }}</label><textarea class="form-control {{ $errors->has('answer') ? 'is-invalid' : '' }}" name="answer" id="answer" required>{{ old('answer', $faq->answer) }}</textarea>@if($errors->has('answer'))<div class="invalid-feedback">{{ $errors->first('answer') }}</div>@endif</div>
            <div class="form-group"><label class="required" for="sort_order">{{ trans('cruds.faq.fields.sort_order') }}</label><input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $faq->sort_order) }}" required>@if($errors->has('sort_order'))<div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>@endif</div>
            <div class="form-group"><div class="form-check"><input type="hidden" name="show_on_homepage" value="0"><input class="form-check-input" type="checkbox" name="show_on_homepage" id="show_on_homepage" value="1" @checked(old('show_on_homepage', $faq->show_on_homepage))><label class="form-check-label" for="show_on_homepage">{{ trans('cruds.faq.fields.show_on_homepage') }}</label></div>@if($errors->has('show_on_homepage'))<div class="invalid-feedback d-block">{{ $errors->first('show_on_homepage') }}</div>@endif</div>
            <div class="form-group"><label class="required" for="is_active">{{ trans('cruds.faq.fields.is_active') }}</label><select class="form-control" name="is_active" id="is_active"><option value="1" @selected((string)old('is_active', $faq->is_active ? '1' : '0')==='1')>{{ trans('global.yes') }}</option><option value="0" @selected((string)old('is_active', $faq->is_active ? '1' : '0')==='0')>{{ trans('global.no') }}</option></select></div>
            <div class="form-group"><button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button></div>
        </form>
    </div>
</div>
@endsection
