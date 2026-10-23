@extends('layouts.admin')

@section('content')
<form method="POST" action="{{ route('admin.frontend.home.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-header">
            Banner Section
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="video_src">Background Video Source (`data-video-src`)</label>
                <input class="form-control" type="text" name="video_src" id="video_src" value="{{ old('video_src', $banner['video_src']) }}" placeholder="mp4:video/2.mp4" required>
            </div>
            <div class="form-group">
                <label for="video_file">Choose Local Video</label>
                <input class="form-control-file" type="file" name="video_file" id="video_file" accept="video/mp4,video/webm,video/ogg">
                <small class="form-text text-muted">If selected, uploaded file will be used for banner background video.</small>
            </div>

            <div class="form-group">
                <label for="subtitle">Banner Subtitle Text</label>
                <input class="form-control" type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $banner['subtitle']) }}" required>
            </div>

            <div class="form-group">
                <label for="title_line_1">Banner Title Text Line 1</label>
                <input class="form-control" type="text" name="title_line_1" id="title_line_1" value="{{ old('title_line_1', $banner['title_line_1'] ?? $banner['title']) }}" required>
            </div>

            <div class="form-group">
                <label for="title_line_2">Banner Title Text Line 2</label>
                <input class="form-control" type="text" name="title_line_2" id="title_line_2" value="{{ old('title_line_2', $banner['title_line_2'] ?? '') }}">
            </div>

            <div class="form-group">
                <label for="title_line_3">Banner Title Text Line 3</label>
                <input class="form-control" type="text" name="title_line_3" id="title_line_3" value="{{ old('title_line_3', $banner['title_line_3'] ?? '') }}">
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="event_date">Event Date Text</label>
                    <input class="form-control" type="text" name="event_date" id="event_date" value="{{ old('event_date', $banner['event_date'] ?? 'October 1-5, 2026') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="event_location">Event Location Text</label>
                    <input class="form-control" type="text" name="event_location" id="event_location" value="{{ old('event_location', $banner['event_location'] ?? 'San Francisco, CA') }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="event_date_icon">Event Date Icon Class</label>
                    <input class="form-control" type="text" name="event_date_icon" id="event_date_icon" value="{{ old('event_date_icon', $banner['event_date_icon'] ?? 'fa fa-calendar') }}" required>
                    <small class="text-muted">Example: <code>fa fa-calendar</code></small>
                </div>
                <div class="form-group col-md-6">
                    <label for="event_location_icon">Event Location Icon Class</label>
                    <input class="form-control" type="text" name="event_location_icon" id="event_location_icon" value="{{ old('event_location_icon', $banner['event_location_icon'] ?? 'fa fa-location-pin') }}" required>
                    <small class="text-muted">Example: <code>fa fa-location-pin</code></small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="primary_button_text">Primary Button Text</label>
                    <input class="form-control" type="text" name="primary_button_text" id="primary_button_text" value="{{ old('primary_button_text', $banner['primary_button_text']) }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="primary_button_link">Primary Button Link</label>
                    <input class="form-control" type="text" name="primary_button_link" id="primary_button_link" value="{{ old('primary_button_link', $banner['primary_button_link']) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="secondary_button_text">Secondary Button Text</label>
                    <input class="form-control" type="text" name="secondary_button_text" id="secondary_button_text" value="{{ old('secondary_button_text', $banner['secondary_button_text']) }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="secondary_button_link">Secondary Button Link</label>
                    <input class="form-control" type="text" name="secondary_button_link" id="secondary_button_link" value="{{ old('secondary_button_link', $banner['secondary_button_link']) }}" required>
                </div>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_banner_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_banner_section" id="hide_banner_section" value="1" {{ old('hide_banner_section', $banner['hide_banner_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_banner_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Countdown Strip
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="countdown_title">Countdown Title</label>
                    <input class="form-control" type="text" name="countdown_title" id="countdown_title" value="{{ old('countdown_title', $banner['countdown_title'] ?? 'Hurry Up!') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="countdown_subtitle">Countdown Subtitle</label>
                    <input class="form-control" type="text" name="countdown_subtitle" id="countdown_subtitle" value="{{ old('countdown_subtitle', $banner['countdown_subtitle'] ?? 'Book Your Seat Now') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="countdown_until">Countdown Target Date/Time</label>
                    <input class="form-control" type="datetime-local" name="countdown_until" id="countdown_until" value="{{ old('countdown_until', $banner['countdown_until'] ?? '2026-10-01T08:00') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="countdown_location_line_1">Location Line 1</label>
                    <input class="form-control" type="text" name="countdown_location_line_1" id="countdown_location_line_1" value="{{ old('countdown_location_line_1', $banner['countdown_location_line_1'] ?? '121 AI Blvd,') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="countdown_location_line_2">Location Line 2</label>
                <input class="form-control" type="text" name="countdown_location_line_2" id="countdown_location_line_2" value="{{ old('countdown_location_line_2', $banner['countdown_location_line_2'] ?? 'San Francisco BCA 94107') }}" required>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_countdown_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_countdown_section" id="hide_countdown_section" value="1" {{ old('hide_countdown_section', $banner['hide_countdown_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_countdown_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            About Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="about_subtitle">About Subtitle</label>
                    <input class="form-control" type="text" name="about_subtitle" id="about_subtitle" value="{{ old('about_subtitle', $banner['about_subtitle'] ?? 'About the Event') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="about_title">About Title</label>
                    <input class="form-control" type="text" name="about_title" id="about_title" value="{{ old('about_title', $banner['about_title'] ?? 'A Global Gathering of AI Innovators') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="about_description">About Description</label>
                <textarea class="form-control" name="about_description" id="about_description" rows="3" required>{{ old('about_description', $banner['about_description'] ?? '') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="about_point_1">Bullet Point 1</label>
                    <input class="form-control" type="text" name="about_point_1" id="about_point_1" value="{{ old('about_point_1', $banner['about_point_1'] ?? '') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="about_point_2">Bullet Point 2</label>
                    <input class="form-control" type="text" name="about_point_2" id="about_point_2" value="{{ old('about_point_2', $banner['about_point_2'] ?? '') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="about_point_3">Bullet Point 3</label>
                    <input class="form-control" type="text" name="about_point_3" id="about_point_3" value="{{ old('about_point_3', $banner['about_point_3'] ?? '') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="about_image">About Image URL/Path</label>
                <input class="form-control" type="text" name="about_image" id="about_image" value="{{ old('about_image', $banner['about_image'] ?? 'images/misc/c1.webp') }}" required>
            </div>
            <div class="form-group">
                <label for="about_image_file">Choose About Image</label>
                <input class="form-control-file" type="file" name="about_image_file" id="about_image_file" accept="image/*">
                <small class="form-text text-muted">If selected, uploaded image will be used for About section image.</small>
                <small class="form-text text-muted">Recommended image size: 1000x1000px.</small>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_about_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_about_section" id="hide_about_section" value="1" {{ old('hide_about_section', $banner['hide_about_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_about_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Why Us Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="why_us_section_name">Section Name</label>
                    <input class="form-control" type="text" name="why_us_section_name" id="why_us_section_name" value="{{ old('why_us_section_name', $banner['why_us_section_name'] ?? 'Why Us') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="why_us_title">Main Title</label>
                    <input class="form-control" type="text" name="why_us_title" id="why_us_title" value="{{ old('why_us_title', $banner['why_us_title'] ?? 'WHAT ABOUT EVENT') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="why_us_description">Section Description</label>
                <textarea class="form-control" name="why_us_description" id="why_us_description" rows="3" required>{{ old('why_us_description', $banner['why_us_description'] ?? '') }}</textarea>
            </div>

            <hr>
            <h6>Card 1</h6>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="why_us_card_1_image">Image URL/Path</label>
                    <input class="form-control" type="text" name="why_us_card_1_image" id="why_us_card_1_image" value="{{ old('why_us_card_1_image', $banner['why_us_card_1_image'] ?? 'images/misc/s3.webp') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="why_us_card_1_title">Title</label>
                    <input class="form-control" type="text" name="why_us_card_1_title" id="why_us_card_1_title" value="{{ old('why_us_card_1_title', $banner['why_us_card_1_title'] ?? 'WHO WE ARE?') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="why_us_card_1_image_file">Choose Image</label>
                    <input class="form-control-file" type="file" name="why_us_card_1_image_file" id="why_us_card_1_image_file" accept="image/*">
                    <small class="form-text text-muted">Recommended image size: 1000x1000px.</small>
                </div>
            </div>
            <div class="form-group">
                <label for="why_us_card_1_text">Description</label>
                <textarea class="form-control" name="why_us_card_1_text" id="why_us_card_1_text" rows="2" required>{{ old('why_us_card_1_text', $banner['why_us_card_1_text'] ?? '') }}</textarea>
            </div>

            <hr>
            <h6>Card 2</h6>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="why_us_card_2_image">Image URL/Path</label>
                    <input class="form-control" type="text" name="why_us_card_2_image" id="why_us_card_2_image" value="{{ old('why_us_card_2_image', $banner['why_us_card_2_image'] ?? 'images/misc/s4.webp') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="why_us_card_2_title">Title</label>
                    <input class="form-control" type="text" name="why_us_card_2_title" id="why_us_card_2_title" value="{{ old('why_us_card_2_title', $banner['why_us_card_2_title'] ?? 'WHAT WE DO?') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="why_us_card_2_image_file">Choose Image</label>
                    <input class="form-control-file" type="file" name="why_us_card_2_image_file" id="why_us_card_2_image_file" accept="image/*">
                    <small class="form-text text-muted">Recommended image size: 1000x1000px.</small>
                </div>
            </div>
            <div class="form-group">
                <label for="why_us_card_2_text">Description</label>
                <textarea class="form-control" name="why_us_card_2_text" id="why_us_card_2_text" rows="2" required>{{ old('why_us_card_2_text', $banner['why_us_card_2_text'] ?? '') }}</textarea>
            </div>

            <hr>
            <h6>Card 3</h6>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="why_us_card_3_image">Image URL/Path</label>
                    <input class="form-control" type="text" name="why_us_card_3_image" id="why_us_card_3_image" value="{{ old('why_us_card_3_image', $banner['why_us_card_3_image'] ?? 'images/misc/s5.webp') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="why_us_card_3_title">Title</label>
                    <input class="form-control" type="text" name="why_us_card_3_title" id="why_us_card_3_title" value="{{ old('why_us_card_3_title', $banner['why_us_card_3_title'] ?? 'WHY US?') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="why_us_card_3_image_file">Choose Image</label>
                    <input class="form-control-file" type="file" name="why_us_card_3_image_file" id="why_us_card_3_image_file" accept="image/*">
                    <small class="form-text text-muted">Recommended image size: 1000x1000px.</small>
                </div>
            </div>
            <div class="form-group">
                <label for="why_us_card_3_text">Description</label>
                <textarea class="form-control" name="why_us_card_3_text" id="why_us_card_3_text" rows="2" required>{{ old('why_us_card_3_text', $banner['why_us_card_3_text'] ?? '') }}</textarea>
            </div>

            <hr>
            <h6>Card 4</h6>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="why_us_card_4_image">Image URL/Path</label>
                    <input class="form-control" type="text" name="why_us_card_4_image" id="why_us_card_4_image" value="{{ old('why_us_card_4_image', $banner['why_us_card_4_image'] ?? 'images/misc/s6.webp') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="why_us_card_4_title">Title</label>
                    <input class="form-control" type="text" name="why_us_card_4_title" id="why_us_card_4_title" value="{{ old('why_us_card_4_title', $banner['why_us_card_4_title'] ?? 'OUR IMPACT?') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="why_us_card_4_image_file">Choose Image</label>
                    <input class="form-control-file" type="file" name="why_us_card_4_image_file" id="why_us_card_4_image_file" accept="image/*">
                    <small class="form-text text-muted">Recommended image size: 1000x1000px.</small>
                </div>
            </div>
            <div class="form-group">
                <label for="why_us_card_4_text">Description</label>
                <textarea class="form-control" name="why_us_card_4_text" id="why_us_card_4_text" rows="2" required>{{ old('why_us_card_4_text', $banner['why_us_card_4_text'] ?? '') }}</textarea>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_why_us_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_why_us_section" id="hide_why_us_section" value="1" {{ old('hide_why_us_section', $banner['hide_why_us_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_why_us_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Testimonials Section
        </div>
        <div class="card-body">
            <small class="form-text text-muted mb-2 d-block">Testimonials content is managed in Content -> Testimonials.</small>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="testimonials_subtitle">Section Name</label>
                    <input class="form-control" type="text" name="testimonials_subtitle" id="testimonials_subtitle" value="{{ old('testimonials_subtitle', $banner['testimonials_subtitle'] ?? 'Testimonials') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="testimonials_title">Main Title</label>
                    <input class="form-control" type="text" name="testimonials_title" id="testimonials_title" value="{{ old('testimonials_title', $banner['testimonials_title'] ?? 'What People Say') }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="testimonials_button_text">Button Text</label>
                    <input class="form-control" type="text" name="testimonials_button_text" id="testimonials_button_text" value="{{ old('testimonials_button_text', $banner['testimonials_button_text'] ?? 'View More') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="testimonials_button_link">Button Link/Page</label>
                    <input class="form-control" type="text" name="testimonials_button_link" id="testimonials_button_link" value="{{ old('testimonials_button_link', $banner['testimonials_button_link'] ?? '/testimonials') }}">
                </div>
            </div>
            <div class="form-group mt-2 mb-0">
                <input type="hidden" name="hide_testimonials_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_testimonials_section" id="hide_testimonials_section" value="1" {{ old('hide_testimonials_section', $banner['hide_testimonials_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_testimonials_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Speakers Section
        </div>
        <div class="card-body">
            <small class="form-text text-muted mb-2 d-block">Speakers content is managed in Event Management -> Speakers.</small>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="speakers_subtitle">Section Name</label>
                    <input class="form-control" type="text" name="speakers_subtitle" id="speakers_subtitle" value="{{ old('speakers_subtitle', $banner['speakers_subtitle'] ?? 'Event Speakers') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="speakers_title">Main Title</label>
                    <input class="form-control" type="text" name="speakers_title" id="speakers_title" value="{{ old('speakers_title', $banner['speakers_title'] ?? 'Meet Our Speakers') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label for="speakers_description">Section Description</label>
                <textarea class="form-control" name="speakers_description" id="speakers_description" rows="2">{{ old('speakers_description', $banner['speakers_description'] ?? '') }}</textarea>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="speakers_view_all_text">View All Speakers Button Text</label>
                    <input class="form-control" type="text" name="speakers_view_all_text" id="speakers_view_all_text" value="{{ old('speakers_view_all_text', $banner['speakers_view_all_text'] ?? 'View All Speakers') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="speakers_view_all_link">View All Speakers Button Link</label>
                    <input class="form-control" type="text" name="speakers_view_all_link" id="speakers_view_all_link" value="{{ old('speakers_view_all_link', $banner['speakers_view_all_link'] ?? '/speakers') }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="speakers_become_text">Become A Speaker Button Text</label>
                    <input class="form-control" type="text" name="speakers_become_text" id="speakers_become_text" value="{{ old('speakers_become_text', $banner['speakers_become_text'] ?? 'Become a Speaker') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="speakers_become_link">Become A Speaker Button Link</label>
                    <input class="form-control" type="text" name="speakers_become_link" id="speakers_become_link" value="{{ old('speakers_become_link', $banner['speakers_become_link'] ?? '/become-speaker') }}">
                </div>
            </div>
            <div class="form-group mt-2 mb-0">
                <input type="hidden" name="hide_speakers_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_speakers_section" id="hide_speakers_section" value="1" {{ old('hide_speakers_section', $banner['hide_speakers_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_speakers_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Counter Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="counter_1_icon">Counter 1 Icon Class</label>
                    <input class="form-control" type="text" name="counter_1_icon" id="counter_1_icon" value="{{ old('counter_1_icon', $banner['counter_1_icon'] ?? 'fa fa-users') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="counter_1_number">Counter 1 Number</label>
                    <input class="form-control" type="text" name="counter_1_number" id="counter_1_number" value="{{ old('counter_1_number', $banner['counter_1_number'] ?? '1200') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="counter_1_text">Counter 1 Text</label>
                    <input class="form-control" type="text" name="counter_1_text" id="counter_1_text" value="{{ old('counter_1_text', $banner['counter_1_text'] ?? 'Participants') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="counter_2_icon">Counter 2 Icon Class</label>
                    <input class="form-control" type="text" name="counter_2_icon" id="counter_2_icon" value="{{ old('counter_2_icon', $banner['counter_2_icon'] ?? 'fa fa-cubes') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="counter_2_number">Counter 2 Number</label>
                    <input class="form-control" type="text" name="counter_2_number" id="counter_2_number" value="{{ old('counter_2_number', $banner['counter_2_number'] ?? '1500') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="counter_2_text">Counter 2 Text</label>
                    <input class="form-control" type="text" name="counter_2_text" id="counter_2_text" value="{{ old('counter_2_text', $banner['counter_2_text'] ?? 'Topics') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="counter_3_icon">Counter 3 Icon Class</label>
                    <input class="form-control" type="text" name="counter_3_icon" id="counter_3_icon" value="{{ old('counter_3_icon', $banner['counter_3_icon'] ?? 'fa fa-power-off') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="counter_3_number">Counter 3 Number</label>
                    <input class="form-control" type="text" name="counter_3_number" id="counter_3_number" value="{{ old('counter_3_number', $banner['counter_3_number'] ?? '1700') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="counter_3_text">Counter 3 Text</label>
                    <input class="form-control" type="text" name="counter_3_text" id="counter_3_text" value="{{ old('counter_3_text', $banner['counter_3_text'] ?? 'Sessions') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="counter_4_icon">Counter 4 Icon Class</label>
                    <input class="form-control" type="text" name="counter_4_icon" id="counter_4_icon" value="{{ old('counter_4_icon', $banner['counter_4_icon'] ?? 'fa fa-microphone') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="counter_4_number">Counter 4 Number</label>
                    <input class="form-control" type="text" name="counter_4_number" id="counter_4_number" value="{{ old('counter_4_number', $banner['counter_4_number'] ?? '1800') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="counter_4_text">Counter 4 Text</label>
                    <input class="form-control" type="text" name="counter_4_text" id="counter_4_text" value="{{ old('counter_4_text', $banner['counter_4_text'] ?? 'Speakers') }}" required>
                </div>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_counter_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_counter_section" id="hide_counter_section" value="1" {{ old('hide_counter_section', $banner['hide_counter_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_counter_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Event Agenda Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="agenda_subtitle">Agenda Subtitle</label>
                    <input class="form-control" type="text" name="agenda_subtitle" id="agenda_subtitle" value="{{ old('agenda_subtitle', $banner['agenda_subtitle'] ?? 'Content Marketing Institute:') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="agenda_title">Agenda Title</label>
                    <input class="form-control" type="text" name="agenda_title" id="agenda_title" value="{{ old('agenda_title', $banner['agenda_title'] ?? 'Executive Forum') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="agenda_meta">Agenda Meta (Date/Location)</label>
                <input class="form-control" type="text" name="agenda_meta" id="agenda_meta" value="{{ old('agenda_meta', $banner['agenda_meta'] ?? 'March 10-11, 2016 - Las Vegas, NV') }}" required>
            </div>

            <div class="form-group">
                <label for="agenda_description_1">Agenda Description 1</label>
                <textarea class="form-control" name="agenda_description_1" id="agenda_description_1" rows="3" required>{{ old('agenda_description_1', $banner['agenda_description_1'] ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label for="agenda_description_2">Agenda Description 2</label>
                <textarea class="form-control" name="agenda_description_2" id="agenda_description_2" rows="3" required>{{ old('agenda_description_2', $banner['agenda_description_2'] ?? '') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="agenda_button_text">Agenda Button Text</label>
                    <input class="form-control" type="text" name="agenda_button_text" id="agenda_button_text" value="{{ old('agenda_button_text', $banner['agenda_button_text'] ?? 'Download Brochure') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="agenda_button_link">Agenda Button Link</label>
                    <input class="form-control" type="text" name="agenda_button_link" id="agenda_button_link" value="{{ old('agenda_button_link', $banner['agenda_button_link'] ?? '#') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="agenda_background_image">Agenda Background Image URL/Path</label>
                <input class="form-control" type="text" name="agenda_background_image" id="agenda_background_image" value="{{ old('agenda_background_image', $banner['agenda_background_image'] ?? 'images/background/4.webp') }}" required>
            </div>

            <div class="form-group">
                <label for="agenda_background_image_file">Choose Agenda Background Image</label>
                <input class="form-control-file" type="file" name="agenda_background_image_file" id="agenda_background_image_file" accept="image/*">
                <small class="form-text text-muted">If selected, uploaded image will be used as agenda background image.</small>
                <small class="form-text text-muted">Recommended image size: 1600x900px.</small>
            </div>

            <div class="form-group">
                <label for="agenda_video_url">Agenda Video YouTube URL</label>
                <input class="form-control" type="url" name="agenda_video_url" id="agenda_video_url" value="{{ old('agenda_video_url', $banner['agenda_video_url'] ?? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ') }}" required>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_agenda_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_agenda_section" id="hide_agenda_section" value="1" {{ old('hide_agenda_section', $banner['hide_agenda_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_agenda_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Brands Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="brands_subtitle">Brands Subtitle</label>
                    <input class="form-control" type="text" name="brands_subtitle" id="brands_subtitle" value="{{ old('brands_subtitle', $banner['brands_subtitle'] ?? 'Our Official Sponsors') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="brands_title">Brands Title</label>
                    <input class="form-control" type="text" name="brands_title" id="brands_title" value="{{ old('brands_title', $banner['brands_title'] ?? 'Trusted By Over 150 Organizations In More Than 50 Countries') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="brands_button_text">Button Text</label>
                    <input class="form-control" type="text" name="brands_button_text" id="brands_button_text" value="{{ old('brands_button_text', $banner['brands_button_text'] ?? 'Become a Sponsor') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="brands_button_link">Button Link</label>
                    <input class="form-control" type="text" name="brands_button_link" id="brands_button_link" value="{{ old('brands_button_link', $banner['brands_button_link'] ?? '#') }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="brands_view_all_text">View All Sponsors Button Text</label>
                    <input class="form-control" type="text" name="brands_view_all_text" id="brands_view_all_text" value="{{ old('brands_view_all_text', $banner['brands_view_all_text'] ?? 'View All Sponsors') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="brands_view_all_link">View All Sponsors Button Link</label>
                    <input class="form-control" type="text" name="brands_view_all_link" id="brands_view_all_link" value="{{ old('brands_view_all_link', $banner['brands_view_all_link'] ?? '/sponsors') }}">
                </div>
            </div>

            @for($i = 1; $i <= 8; $i++)
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="brand_logo_{{ $i }}">Brand Logo {{ $i }} URL/Path</label>
                        <input class="form-control" type="text" name="brand_logo_{{ $i }}" id="brand_logo_{{ $i }}" value="{{ old('brand_logo_' . $i, $banner['brand_logo_' . $i] ?? ('images/logo-light/' . $i . '.webp')) }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="brand_logo_{{ $i }}_file">Choose Brand Logo {{ $i }}</label>
                        <input class="form-control-file" type="file" name="brand_logo_{{ $i }}_file" id="brand_logo_{{ $i }}_file" accept="image/*">
                        <small class="form-text text-muted">Recommended image size: 400x160px.</small>
                    </div>
                </div>
            @endfor

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_brands_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_brands_section" id="hide_brands_section" value="1" {{ old('hide_brands_section', $banner['hide_brands_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_brands_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Gallery Section
        </div>
        <div class="card-body">
            @php
                $galleryImagesForAdmin = old('gallery_images_list');
                if ($galleryImagesForAdmin === null) {
                    $storedGallery = $banner['gallery_images'] ?? [];
                    if (! is_array($storedGallery) || empty($storedGallery)) {
                        $storedGallery = [];
                        for ($gi = 1; $gi <= 6; $gi++) {
                            $storedGallery[] = $banner['gallery_image_' . $gi] ?? ('images/gallery/' . $gi . '.webp');
                        }
                    }
                    $galleryImagesForAdmin = implode("\n", array_filter(array_map(static fn ($value) => trim((string) $value), $storedGallery)));
                }
            @endphp
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="gallery_section_name">Section Name</label>
                    <input class="form-control" type="text" name="gallery_section_name" id="gallery_section_name" value="{{ old('gallery_section_name', $banner['gallery_section_name'] ?? 'Image Gallery') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="gallery_main_title">Main Title</label>
                    <input class="form-control" type="text" name="gallery_main_title" id="gallery_main_title" value="{{ old('gallery_main_title', $banner['gallery_main_title'] ?? 'Moments From Our Events') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label for="gallery_description">Section Description</label>
                <textarea class="form-control" name="gallery_description" id="gallery_description" rows="2" required>{{ old('gallery_description', $banner['gallery_description'] ?? 'Explore highlights from past conferences, sessions, and networking experiences.') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="gallery_images_list">Gallery Image URLs/Paths (one per line)</label>
                    <textarea class="form-control" name="gallery_images_list" id="gallery_images_list" rows="6" placeholder="images/gallery/1.webp&#10;https://example.com/gallery-2.jpg">{{ $galleryImagesForAdmin }}</textarea>
                    <small class="form-text text-muted">Add internal paths or external URLs, one per line.</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="gallery_images_files">Choose Gallery Images (Multiple)</label>
                    <input class="form-control-file" type="file" name="gallery_images_files[]" id="gallery_images_files" accept="image/*" multiple>
                    <small class="form-text text-muted">You can select and upload multiple images at once.</small>
                    <small class="form-text text-muted">Recommended image size: 1200x800px.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="gallery_view_more_text">View More Button Text</label>
                    <input class="form-control" type="text" name="gallery_view_more_text" id="gallery_view_more_text" value="{{ old('gallery_view_more_text', $banner['gallery_view_more_text'] ?? 'View More') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="gallery_view_more_link">View More Button Link (internal/external)</label>
                    <input class="form-control" type="text" name="gallery_view_more_link" id="gallery_view_more_link" value="{{ old('gallery_view_more_link', $banner['gallery_view_more_link'] ?? '#') }}">
                </div>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_gallery_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_gallery_section" id="hide_gallery_section" value="1" {{ old('hide_gallery_section', $banner['hide_gallery_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_gallery_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            FAQ Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="faq_subtitle">FAQ Subtitle</label>
                    <input class="form-control" type="text" name="faq_subtitle" id="faq_subtitle" value="{{ old('faq_subtitle', $banner['faq_subtitle'] ?? 'Everything You Need to Know') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="faq_title">FAQ Title</label>
                    <input class="form-control" type="text" name="faq_title" id="faq_title" value="{{ old('faq_title', $banner['faq_title'] ?? 'Frequently Asked Questions') }}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="faq_view_more_text">FAQ View More Button Text</label>
                    <input class="form-control" type="text" name="faq_view_more_text" id="faq_view_more_text" value="{{ old('faq_view_more_text', $banner['faq_view_more_text'] ?? 'View More') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="faq_view_more_link">FAQ View More Button Link</label>
                    <input class="form-control" type="text" name="faq_view_more_link" id="faq_view_more_link" value="{{ old('faq_view_more_link', $banner['faq_view_more_link'] ?? '#') }}">
                </div>
            </div>
            <small class="form-text text-muted">FAQ items are loaded dynamically from the FAQs module (published/active only).</small>
            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_faq_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_faq_section" id="hide_faq_section" value="1" {{ old('hide_faq_section', $banner['hide_faq_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_faq_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Ticket Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="ticket_subtitle">Ticket Subtitle</label>
                    <input class="form-control" type="text" name="ticket_subtitle" id="ticket_subtitle" value="{{ old('ticket_subtitle', $banner['ticket_subtitle'] ?? 'Ticket Options') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="ticket_title">Ticket Title</label>
                    <input class="form-control" type="text" name="ticket_title" id="ticket_title" value="{{ old('ticket_title', $banner['ticket_title'] ?? 'Choose Your Pass') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="ticket_description">Ticket Section Description</label>
                <textarea class="form-control" name="ticket_description" id="ticket_description" rows="2" required>{{ old('ticket_description', $banner['ticket_description'] ?? '') }}</textarea>
            </div>
            <small class="form-text text-muted mb-2 d-block">Ticket cards on the home page are loaded automatically from the active published event ticket plans under Event Management -> Events.</small>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_ticket_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_ticket_section" id="hide_ticket_section" value="1" {{ old('hide_ticket_section', $banner['hide_ticket_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_ticket_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Venue Section
        </div>
        <div class="card-body">
            @php
                $venueImagesForAdmin = old('venue_images_list');
                if ($venueImagesForAdmin === null) {
                    $storedVenueImages = $banner['venue_images'] ?? [];
                    if (! is_array($storedVenueImages) || empty($storedVenueImages)) {
                        $storedVenueImages = [
                            $banner['venue_image_1'] ?? 'images/misc/l1.webp',
                            $banner['venue_image_2'] ?? 'images/misc/l2.webp',
                        ];
                    }
                    $venueImagesForAdmin = implode("\n", array_filter(array_map(static fn ($value) => trim((string) $value), $storedVenueImages)));
                }
            @endphp
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="venue_subtitle">Venue Subtitle</label>
                    <input class="form-control" type="text" name="venue_subtitle" id="venue_subtitle" value="{{ old('venue_subtitle', $banner['venue_subtitle'] ?? 'Event Location') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="venue_title">Venue Title</label>
                    <input class="form-control" type="text" name="venue_title" id="venue_title" value="{{ old('venue_title', $banner['venue_title'] ?? 'Location & Venue') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="venue_description">Venue Description</label>
                <textarea class="form-control" name="venue_description" id="venue_description" rows="3" required>{{ old('venue_description', $banner['venue_description'] ?? '') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="venue_images_list">Venue Image URLs/Paths (one per line)</label>
                    <textarea class="form-control" name="venue_images_list" id="venue_images_list" rows="5" placeholder="images/misc/l1.webp&#10;https://example.com/venue-2.jpg">{{ $venueImagesForAdmin }}</textarea>
                    <small class="form-text text-muted">Add any number of venue images. One per line.</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="venue_images_files">Choose Venue Images (Multiple)</label>
                    <input class="form-control-file" type="file" name="venue_images_files[]" id="venue_images_files" accept="image/*" multiple>
                    <small class="form-text text-muted">You can upload multiple images at once.</small>
                    <small class="form-text text-muted">Recommended image size: 1200x800px.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="venue_view_more_text">View More Button Text</label>
                    <input class="form-control" type="text" name="venue_view_more_text" id="venue_view_more_text" value="{{ old('venue_view_more_text', $banner['venue_view_more_text'] ?? 'View More') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="venue_view_more_link">View More Button Link (internal/external)</label>
                    <input class="form-control" type="text" name="venue_view_more_link" id="venue_view_more_link" value="{{ old('venue_view_more_link', $banner['venue_view_more_link'] ?? '#') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="venue_address_heading">Address Heading</label>
                    <input class="form-control" type="text" name="venue_address_heading" id="venue_address_heading" value="{{ old('venue_address_heading', $banner['venue_address_heading'] ?? 'Address') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="venue_address_text">Address Text</label>
                    <input class="form-control" type="text" name="venue_address_text" id="venue_address_text" value="{{ old('venue_address_text', $banner['venue_address_text'] ?? '121 AI Blvd, San Francisco, CA 94107') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="venue_phone_heading">Phone Heading</label>
                    <input class="form-control" type="text" name="venue_phone_heading" id="venue_phone_heading" value="{{ old('venue_phone_heading', $banner['venue_phone_heading'] ?? 'Phone') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="venue_phone_text">Phone Text</label>
                    <input class="form-control" type="text" name="venue_phone_text" id="venue_phone_text" value="{{ old('venue_phone_text', $banner['venue_phone_text'] ?? 'Call: +1 123 456 789') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="venue_email_heading">Email Heading</label>
                    <input class="form-control" type="text" name="venue_email_heading" id="venue_email_heading" value="{{ old('venue_email_heading', $banner['venue_email_heading'] ?? 'Email') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="venue_email_text">Email Text</label>
                    <input class="form-control" type="text" name="venue_email_text" id="venue_email_text" value="{{ old('venue_email_text', $banner['venue_email_text'] ?? 'contact@aivent.com') }}" required>
                </div>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_venue_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_venue_section" id="hide_venue_section" value="1" {{ old('hide_venue_section', $banner['hide_venue_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_venue_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Map Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="map_heading">Map Heading</label>
                    <input class="form-control" type="text" name="map_heading" id="map_heading" value="{{ old('map_heading', $banner['map_heading'] ?? 'Reach Us') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="map_title">Map Title</label>
                    <input class="form-control" type="text" name="map_title" id="map_title" value="{{ old('map_title', $banner['map_title'] ?? 'Get Direction to the Event Hall') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="map_content">Map Content</label>
                <textarea class="form-control" name="map_content" id="map_content" rows="3" required>{{ old('map_content', $banner['map_content'] ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label for="map_iframe">Map Iframe Embed Code</label>
                <textarea class="form-control" name="map_iframe" id="map_iframe" rows="4" required>{{ old('map_iframe', $banner['map_iframe'] ?? '') }}</textarea>
                <small class="form-text text-muted">Paste full Google Maps iframe embed code here. Tabs are removed in frontend by design.</small>
            </div>

            <hr>
            <h6>Address</h6>
            <div class="form-group">
                <label for="map_address_title">Address Title</label>
                <input class="form-control" type="text" name="map_address_title" id="map_address_title" value="{{ old('map_address_title', $banner['map_address_title'] ?? 'Brighton Waterfront Hotel, Brighton, London') }}" required>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="map_address_line_1">Address Line 1</label>
                    <input class="form-control" type="text" name="map_address_line_1" id="map_address_line_1" value="{{ old('map_address_line_1', $banner['map_address_line_1'] ?? '1Hd- 50, 010 Avenue, NY 90001') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="map_address_line_2">Address Line 2</label>
                    <input class="form-control" type="text" name="map_address_line_2" id="map_address_line_2" value="{{ old('map_address_line_2', $banner['map_address_line_2'] ?? 'United States') }}" required>
                </div>
            </div>

            <hr>
            <h6>Tickets Info</h6>
            <div class="form-group">
                <label for="map_tickets_title">Tickets Info Title</label>
                <input class="form-control" type="text" name="map_tickets_title" id="map_tickets_title" value="{{ old('map_tickets_title', $banner['map_tickets_title'] ?? 'Tickets info') }}" required>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="map_tickets_name">Tickets Name Line</label>
                    <input class="form-control" type="text" name="map_tickets_name" id="map_tickets_name" value="{{ old('map_tickets_name', $banner['map_tickets_name'] ?? 'Name: Ronaldo Konig') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="map_tickets_phone">Tickets Phone Line</label>
                    <input class="form-control" type="text" name="map_tickets_phone" id="map_tickets_phone" value="{{ old('map_tickets_phone', $banner['map_tickets_phone'] ?? 'Phone: 009-21-55-595') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="map_tickets_email">Tickets Email Line</label>
                    <input class="form-control" type="text" name="map_tickets_email" id="map_tickets_email" value="{{ old('map_tickets_email', $banner['map_tickets_email'] ?? 'Email: info@example.com') }}" required>
                </div>
            </div>

            <hr>
            <h6>Programme Details</h6>
            <div class="form-group">
                <label for="map_programme_title">Programme Details Title</label>
                <input class="form-control" type="text" name="map_programme_title" id="map_programme_title" value="{{ old('map_programme_title', $banner['map_programme_title'] ?? 'Programme Details') }}" required>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="map_programme_name">Programme Name Line</label>
                    <input class="form-control" type="text" name="map_programme_name" id="map_programme_name" value="{{ old('map_programme_name', $banner['map_programme_name'] ?? 'Name: Ronaldo Konig') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="map_programme_phone">Programme Phone Line</label>
                    <input class="form-control" type="text" name="map_programme_phone" id="map_programme_phone" value="{{ old('map_programme_phone', $banner['map_programme_phone'] ?? 'Phone: 009-21-55-595') }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="map_programme_email">Programme Email Line</label>
                    <input class="form-control" type="text" name="map_programme_email" id="map_programme_email" value="{{ old('map_programme_email', $banner['map_programme_email'] ?? 'Email: info@example.com') }}" required>
                </div>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_map_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_map_section" id="hide_map_section" value="1" {{ old('hide_map_section', $banner['hide_map_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_map_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Things To Do Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="things_subtitle">Section Subtitle</label>
                    <input class="form-control" type="text" name="things_subtitle" id="things_subtitle" value="{{ old('things_subtitle', $banner['things_subtitle'] ?? 'Things To Do') }}" required>
                </div>
                <div class="form-group col-md-8">
                    <label for="things_title">Section Title</label>
                    <input class="form-control" type="text" name="things_title" id="things_title" value="{{ old('things_title', $banner['things_title'] ?? 'Discover Local Attractions') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label for="things_description">Section Description</label>
                <textarea class="form-control" name="things_description" id="things_description" rows="2">{{ old('things_description', $banner['things_description'] ?? '') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="things_button_text">Button Text</label>
                    <input class="form-control" type="text" name="things_button_text" id="things_button_text" value="{{ old('things_button_text', $banner['things_button_text'] ?? 'VIEW ALL ATTRACTIONS') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="things_button_link">Button Link</label>
                    <input class="form-control" type="text" name="things_button_link" id="things_button_link" value="{{ old('things_button_link', $banner['things_button_link'] ?? '#') }}" required>
                </div>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_things_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_things_section" id="hide_things_section" value="1" {{ old('hide_things_section', $banner['hide_things_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_things_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Newsletter Section
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="newsletter_title">Newsletter Title</label>
                    <input class="form-control" type="text" name="newsletter_title" id="newsletter_title" value="{{ old('newsletter_title', $banner['newsletter_title'] ?? 'Join the Future of Innovation') }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="newsletter_placeholder">Input Placeholder</label>
                    <input class="form-control" type="text" name="newsletter_placeholder" id="newsletter_placeholder" value="{{ old('newsletter_placeholder', $banner['newsletter_placeholder'] ?? 'Enter Your Email Address') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label for="newsletter_button_text">Button Text</label>
                <input class="form-control" type="text" name="newsletter_button_text" id="newsletter_button_text" value="{{ old('newsletter_button_text', $banner['newsletter_button_text'] ?? 'SIGN UP') }}" required>
            </div>

            <div class="form-group mt-3 mb-0">
                <input type="hidden" name="hide_newsletter_section" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hide_newsletter_section" id="hide_newsletter_section" value="1" {{ old('hide_newsletter_section', $banner['hide_newsletter_section'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hide_newsletter_section">Hide this section from Home page</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            Actions
        </div>
        <div class="card-body">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </div>
</form>
@endsection
