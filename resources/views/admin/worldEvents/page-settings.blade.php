@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">World Events Page Settings</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.world-events.page-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="col-md-4 form-group">
                    <label for="hero_label">Header Label</label>
                    <input type="text" class="form-control" name="hero_label" id="hero_label" value="{{ old('hero_label', $settings['hero_label']) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="hero_title">Header Title</label>
                    <input type="text" class="form-control" name="hero_title" id="hero_title" value="{{ old('hero_title', $settings['hero_title']) }}" required>
                </div>
                <div class="col-md-4 form-group">
                    <label for="hero_title_suffix">Header Suffix</label>
                    <input type="text" class="form-control" name="hero_title_suffix" id="hero_title_suffix" value="{{ old('hero_title_suffix', $settings['hero_title_suffix']) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="hero_description">Header Description</label>
                <textarea class="form-control" name="hero_description" id="hero_description" rows="3">{{ old('hero_description', $settings['hero_description']) }}</textarea>
            </div>

            <div class="form-row">
                <div class="col-md-4 form-group">
                    <label for="overview_title">Overview Section Title</label>
                    <input type="text" class="form-control" name="overview_title" id="overview_title" value="{{ old('overview_title', $settings['overview_title']) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="monthly_tab_text">Monthly Tab Text</label>
                    <input type="text" class="form-control" name="monthly_tab_text" id="monthly_tab_text" value="{{ old('monthly_tab_text', $settings['monthly_tab_text']) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="yearly_tab_text">Yearly Tab Text</label>
                    <input type="text" class="form-control" name="yearly_tab_text" id="yearly_tab_text" value="{{ old('yearly_tab_text', $settings['yearly_tab_text']) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="search_placeholder">Search Placeholder</label>
                    <input type="text" class="form-control" name="search_placeholder" id="search_placeholder" value="{{ old('search_placeholder', $settings['search_placeholder']) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-4 form-group">
                    <label for="event_filter_label">Event Filter Label</label>
                    <input type="text" class="form-control" name="event_filter_label" id="event_filter_label" value="{{ old('event_filter_label', $settings['event_filter_label']) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="country_filter_label">Country Filter Label</label>
                    <input type="text" class="form-control" name="country_filter_label" id="country_filter_label" value="{{ old('country_filter_label', $settings['country_filter_label']) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="empty_state_text">No Events Available Text</label>
                    <input type="text" class="form-control" name="empty_state_text" id="empty_state_text" value="{{ old('empty_state_text', $settings['empty_state_text']) }}">
                    <small class="form-text text-muted">Shown on the world events frontend when no events are available for the selected filters, month, or year.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="col-md-6 form-group">
                    <label for="monthly_timeline_title">Monthly Timeline Title</label>
                    <input type="text" class="form-control" name="monthly_timeline_title" id="monthly_timeline_title" value="{{ old('monthly_timeline_title', $settings['monthly_timeline_title']) }}">
                </div>
                <div class="col-md-6 form-group">
                    <label for="yearly_calendar_title">Yearly Calendar Title</label>
                    <input type="text" class="form-control" name="yearly_calendar_title" id="yearly_calendar_title" value="{{ old('yearly_calendar_title', $settings['yearly_calendar_title']) }}">
                </div>
            </div>

            <button class="btn btn-primary" type="submit">Save Settings</button>
            <a class="btn btn-default" href="{{ route('admin.world-events.index') }}">Cancel</a>
        </form>
    </div>
</div>
@endsection
