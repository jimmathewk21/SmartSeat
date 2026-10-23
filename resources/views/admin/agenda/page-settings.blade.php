@extends('layouts.admin')
@section('content')
@php
    $agendaDownloadUrl = old('agenda_download_url', $settings['agenda_download_url'] ?? '#');
    $agendaDownloadIsAbsolute = str_starts_with((string) $agendaDownloadUrl, 'http://')
        || str_starts_with((string) $agendaDownloadUrl, 'https://')
        || str_starts_with((string) $agendaDownloadUrl, '//')
        || str_starts_with((string) $agendaDownloadUrl, '/');
    $agendaDownloadHref = $agendaDownloadIsAbsolute || $agendaDownloadUrl === '#'
        ? $agendaDownloadUrl
        : asset(ltrim((string) $agendaDownloadUrl, '/'));
@endphp
<div class="card">
    <div class="card-header">Agenda Page Settings</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.agenda.page-settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="event_id">Default Event</label>
                <select class="form-control select2" name="event_id" id="event_id">
                    @foreach($events as $id => $title)
                        <option value="{{ $id }}" @selected((string) old('event_id', $settings['event_id'] ?? '') === (string) $id)>{{ $title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="subtitle">Subtitle</label>
                <input class="form-control" type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $settings['subtitle'] ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="title">Title</label>
                <input class="form-control" type="text" name="title" id="title" value="{{ old('title', $settings['title'] ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="small_description">Small Description (left side)</label>
                <textarea class="form-control" name="small_description" id="small_description" rows="3">{{ old('small_description', $settings['small_description'] ?? '') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="agenda_download_text">Agenda Download Button Text</label>
                    <input class="form-control" type="text" name="agenda_download_text" id="agenda_download_text" value="{{ old('agenda_download_text', $settings['agenda_download_text'] ?? 'Download Agenda') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="agenda_download_url">Agenda Download URL (optional)</label>
                <input class="form-control" type="text" name="agenda_download_url" id="agenda_download_url" value="{{ old('agenda_download_url', $settings['agenda_download_url'] ?? '#') }}">
                <small class="form-text text-muted">You can also upload a file below. Uploading a file will use that file for the download button.</small>
                @if(!empty($agendaDownloadUrl) && $agendaDownloadUrl !== '#')
                    <div class="mt-2">
                        <a href="{{ $agendaDownloadHref }}" target="_blank" rel="noopener noreferrer">View current agenda file</a>
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label for="agenda_download_file">Upload Agenda File</label>
                <input class="form-control-file" type="file" name="agenda_download_file" id="agenda_download_file" accept=".pdf,.jpg,.jpeg,.doc,.docx,.ppt,.pptx">
                <small class="form-text text-muted">Allowed types: PDF, JPG, JPEG, Word, and PowerPoint.</small>
            </div>

            <div class="form-group mb-0">
                <button class="btn btn-danger" type="submit">{{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
