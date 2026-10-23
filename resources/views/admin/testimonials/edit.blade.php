@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.testimonial.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.testimonials.update', [$testimonial->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <h5 class="mb-3">Basic Information</h5>
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label class="required" for="name">{{ trans('cruds.testimonial.fields.name') }}</label>
                        <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $testimonial->name) }}" required>
                        @if($errors->has('name'))<div class="invalid-feedback">{{ $errors->first('name') }}</div>@endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="photo_url">{{ trans('cruds.testimonial.fields.photo_url') }}</label>
                        <input class="form-control {{ $errors->has('photo_url') ? 'is-invalid' : '' }}" type="text" name="photo_url" id="photo_url" value="{{ old('photo_url', $testimonial->photo_url) }}" placeholder="https:// or uploads/testimonials/image.webp">
                        @if($errors->has('photo_url'))<div class="invalid-feedback">{{ $errors->first('photo_url') }}</div>@endif
                        <div class="mt-2">
                            <input class="form-control-file {{ $errors->has('photo_file') ? 'is-invalid' : '' }}" type="file" name="photo_file" id="photo_file" accept="image/*">
                            @if($errors->has('photo_file'))<div class="invalid-feedback d-block">{{ $errors->first('photo_file') }}</div>@endif
                        </div>
<small class="form-text text-muted">Image size 256×256 recommended.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="company">{{ trans('cruds.testimonial.fields.company') }}</label>
                        <input class="form-control {{ $errors->has('company') ? 'is-invalid' : '' }}" type="text" name="company" id="company" value="{{ old('company', $testimonial->company) }}">
                        @if($errors->has('company'))<div class="invalid-feedback">{{ $errors->first('company') }}</div>@endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label for="designation">{{ trans('cruds.testimonial.fields.designation') }}</label>
                        <input class="form-control {{ $errors->has('designation') ? 'is-invalid' : '' }}" type="text" name="designation" id="designation" value="{{ old('designation', $testimonial->designation) }}">
                        @if($errors->has('designation'))<div class="invalid-feedback">{{ $errors->first('designation') }}</div>@endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="event_id">{{ trans('cruds.testimonial.fields.event') }}</label>
                        <select class="form-control {{ $errors->has('event_id') ? 'is-invalid' : '' }}" name="event_id" id="event_id">
                            <option value="">{{ trans('global.pleaseSelect') }}</option>
                            @foreach($events as $id => $entry)
                                <option value="{{ $id }}" @selected(old('event_id', $testimonial->event_id) == $id)>{{ $entry }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('event_id'))<div class="invalid-feedback">{{ $errors->first('event_id') }}</div>@endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="country_id">{{ trans('cruds.testimonial.fields.country') }}</label>
                        <select class="form-control {{ $errors->has('country_id') ? 'is-invalid' : '' }}" name="country_id" id="country_id">
                            @foreach($countries as $id => $entry)
                                <option value="{{ $id }}" @selected(old('country_id', $testimonial->country_id) == $id)>{{ $entry }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('country_id'))<div class="invalid-feedback">{{ $errors->first('country_id') }}</div>@endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label for="year">{{ trans('cruds.testimonial.fields.year') }}</label>
                        <input class="form-control {{ $errors->has('year') ? 'is-invalid' : '' }}" type="number" name="year" id="year" min="2000" max="2100" value="{{ old('year', $testimonial->year) }}">
                        @if($errors->has('year'))<div class="invalid-feedback">{{ $errors->first('year') }}</div>@endif
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="mb-3">Testimonial</h5>
                <div class="form-group">
                    <label class="required" for="message">{{ trans('cruds.testimonial.fields.message') }}</label>
                    <textarea class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}" name="message" id="message" rows="5" required>{{ old('message', $testimonial->message) }}</textarea>
                    @if($errors->has('message'))<div class="invalid-feedback">{{ $errors->first('message') }}</div>@endif
                </div>
                <div class="form-group">
                    <label for="experience_word">{{ trans('cruds.testimonial.fields.experience_word') }}</label>
                    <input class="form-control {{ $errors->has('experience_word') ? 'is-invalid' : '' }}" type="text" name="experience_word" id="experience_word" value="{{ old('experience_word', $testimonial->experience_word) }}">
                    @if($errors->has('experience_word'))<div class="invalid-feedback">{{ $errors->first('experience_word') }}</div>@endif
                </div>
                <div class="form-group mb-0">
                    <label class="required d-block">{{ trans('cruds.testimonial.fields.stars') }}</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="stars" id="stars_average" value="3" @checked((int) old('stars', $testimonial->stars ?? 5) === 3)>
                        <label class="form-check-label" for="stars_average">Average</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="stars" id="stars_recommended" value="4" @checked((int) old('stars', $testimonial->stars ?? 5) === 4)>
                        <label class="form-check-label" for="stars_recommended">Recommended</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="stars" id="stars_highly_recommended" value="5" @checked((int) old('stars', $testimonial->stars ?? 5) === 5)>
                        <label class="form-check-label" for="stars_highly_recommended">Highly Recommended</label>
                    </div>
                    @if($errors->has('stars'))<div class="invalid-feedback d-block">{{ $errors->first('stars') }}</div>@endif
                </div>
            </div>

            <div class="mb-4">
                <h5 class="mb-3">Visibility & Status</h5>
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input type="hidden" name="show_on_homepage" value="0">
                            <input class="form-check-input" type="checkbox" name="show_on_homepage" id="show_on_homepage" value="1" @checked(old('show_on_homepage', $testimonial->show_on_homepage))>
                            <label class="form-check-label" for="show_on_homepage">{{ trans('cruds.testimonial.fields.show_on_homepage') }}</label>
                        </div>
                        @if($errors->has('show_on_homepage'))<div class="invalid-feedback d-block">{{ $errors->first('show_on_homepage') }}</div>@endif
                        <div class="form-check mt-2">
                            <input type="hidden" name="show_on_scrollbar" value="0">
                            <input class="form-check-input" type="checkbox" name="show_on_scrollbar" id="show_on_scrollbar" value="1" @checked(old('show_on_scrollbar', $testimonial->show_on_scrollbar))>
                            <label class="form-check-label" for="show_on_scrollbar">Show On Scroll Bar (Events, Contact Us Pages Only)</label>
                        </div>
                        @if($errors->has('show_on_scrollbar'))<div class="invalid-feedback d-block">{{ $errors->first('show_on_scrollbar') }}</div>@endif
                        <div class="form-check mt-2">
                            <input type="hidden" name="is_key_figure" value="0">
                            <input class="form-check-input" type="checkbox" name="is_key_figure" id="is_key_figure" value="1" @checked(old('is_key_figure', $testimonial->is_key_figure))>
                            <label class="form-check-label" for="is_key_figure">{{ trans('cruds.testimonial.fields.is_key_figure') }}</label>
                        </div>
                        @if($errors->has('is_key_figure'))<div class="invalid-feedback d-block">{{ $errors->first('is_key_figure') }}</div>@endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="required" for="is_active">{{ trans('cruds.testimonial.fields.is_active') }}</label>
                        <select class="form-control" name="is_active" id="is_active">
                            <option value="1" @selected((string)old('is_active', $testimonial->is_active ? '1' : '0') === '1')>{{ trans('global.yes') }} (Publish)</option>
                            <option value="0" @selected((string)old('is_active', $testimonial->is_active ? '1' : '0') === '0')>{{ trans('global.no') }} (Unpublish)</option>
                        </select>
                        @if($errors->has('is_active'))<div class="invalid-feedback">{{ $errors->first('is_active') }}</div>@endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="required" for="sort_order">{{ trans('cruds.testimonial.fields.sort_order') }}</label>
                        <input class="form-control {{ $errors->has('sort_order') ? 'is-invalid' : '' }}" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $testimonial->sort_order) }}" required>
                        @if($errors->has('sort_order'))<div class="invalid-feedback">{{ $errors->first('sort_order') }}</div>@endif
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button class="btn btn-success" type="submit">{{ trans('global.update') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection


