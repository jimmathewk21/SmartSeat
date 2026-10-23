@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.frontend.download.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">Download Page</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="hero_label">Top Label</label>
                    <input type="text" class="form-control" name="hero_label" id="hero_label" value="{{ old('hero_label', $settings['hero_label'] ?? '') }}" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="hero_title">Main Title</label>
                    <input type="text" class="form-control" name="hero_title" id="hero_title" value="{{ old('hero_title', $settings['hero_title'] ?? '') }}" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="hero_title_suffix">Title Suffix</label>
                    <input type="text" class="form-control" name="hero_title_suffix" id="hero_title_suffix" value="{{ old('hero_title_suffix', $settings['hero_title_suffix'] ?? '') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="hero_description">Hero Description</label>
                <textarea class="form-control" name="hero_description" id="hero_description" rows="4" required>{{ old('hero_description', $settings['hero_description'] ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label for="intro_paragraph_1">Intro Paragraph 1</label>
                <textarea class="form-control" name="intro_paragraph_1" id="intro_paragraph_1" rows="3">{{ old('intro_paragraph_1', $settings['intro_paragraph_1'] ?? '') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="intro_heading">Intro Heading</label>
                    <input type="text" class="form-control" name="intro_heading" id="intro_heading" value="{{ old('intro_heading', $settings['intro_heading'] ?? '') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="left_column_heading">Left Column Heading</label>
                    <input type="text" class="form-control" name="left_column_heading" id="left_column_heading" value="{{ old('left_column_heading', $settings['left_column_heading'] ?? '') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="right_column_heading">Right Column Heading</label>
                    <input type="text" class="form-control" name="right_column_heading" id="right_column_heading" value="{{ old('right_column_heading', $settings['right_column_heading'] ?? '') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="intro_paragraph_2">Intro Paragraph 2</label>
                <textarea class="form-control" name="intro_paragraph_2" id="intro_paragraph_2" rows="3">{{ old('intro_paragraph_2', $settings['intro_paragraph_2'] ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label for="intro_paragraph_3">Intro Paragraph 3</label>
                <textarea class="form-control" name="intro_paragraph_3" id="intro_paragraph_3" rows="3">{{ old('intro_paragraph_3', $settings['intro_paragraph_3'] ?? '') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="left_column_content">Left Column Content</label>
                    <textarea class="form-control ckeditor" name="left_column_content" id="left_column_content" rows="8">{{ old('left_column_content', $settings['left_column_content'] ?? '') }}</textarea>
                </div>
                <div class="col-md-6 form-group">
                    <label for="right_column_content">Right Column Content</label>
                    <textarea class="form-control ckeditor" name="right_column_content" id="right_column_content" rows="8">{{ old('right_column_content', $settings['right_column_content'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="testimonial_title">Footer Testimonial Title</label>
                <input type="text" class="form-control" name="testimonial_title" id="testimonial_title" value="{{ old('testimonial_title', $settings['testimonial_title'] ?? '') }}" required>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </div>
</form>
@endsection
