@php
    $selectedSpeakers = collect(old('speaker_ids', isset($item) ? $item->speakers->pluck('id')->all() : []))->map(fn($id) => (string) $id)->all();
@endphp
<div class="form-group">
    <label class="required" for="event_id">Event</label>
    <select class="form-control select2" name="event_id" id="event_id" required>
        @foreach($events as $id => $title)
            <option value="{{ $id }}" @selected((string) old('event_id', $item->event_id ?? '') === (string) $id)>{{ $title }}</option>
        @endforeach
    </select>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label class="required" for="agenda_day_id">Agenda Day</label>
        <select class="form-control select2" name="agenda_day_id" id="agenda_day_id" required>
            @foreach($days as $id => $title)
                <option value="{{ $id }}" @selected((string) old('agenda_day_id', $item->agenda_day_id ?? '') === (string) $id)>{{ $title }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label class="required" for="agenda_room_id">Meeting Room</label>
        <select class="form-control select2" name="agenda_room_id" id="agenda_room_id" required>
            @foreach($rooms as $id => $title)
                <option value="{{ $id }}" @selected((string) old('agenda_room_id', $item->agenda_room_id ?? '') === (string) $id)>{{ $title }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group">
    <label class="required" for="title">Session Title</label>
    <input class="form-control" type="text" name="title" id="title" value="{{ old('title', $item->title ?? '') }}" required>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label for="start_time">Start Time</label>
        <input class="form-control" type="text" name="start_time" id="start_time" value="{{ old('start_time', $item->start_time ?? '') }}" placeholder="09:30 AM">
    </div>
    <div class="form-group col-md-3">
        <label for="end_time">End Time</label>
        <input class="form-control" type="text" name="end_time" id="end_time" value="{{ old('end_time', $item->end_time ?? '') }}" placeholder="10:30 AM">
    </div>
    <div class="form-group col-md-6">
        <label for="panel_theme">Theme of the panel</label>
        <input class="form-control" type="text" name="panel_theme" id="panel_theme" value="{{ old('panel_theme', $item->panel_theme ?? '') }}">
    </div>
</div>

<div class="form-group">
    <label for="topic_description">Small Description (topics, notes)</label>
    <textarea class="form-control" name="topic_description" id="topic_description" rows="3">{{ old('topic_description', $item->topic_description ?? '') }}</textarea>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="moderator_name">Moderator Name (optional custom text)</label>
        <input class="form-control" type="text" name="moderator_name" id="moderator_name" value="{{ old('moderator_name', $item->moderator_name ?? '') }}">
    </div>
    <div class="form-group col-md-6">
        <label for="moderator_speaker_id">Moderator Speaker Profile</label>
        <select class="form-control select2" name="moderator_speaker_id" id="moderator_speaker_id">
            <option value="">None</option>
            @foreach($speakers as $id => $name)
                <option value="{{ $id }}" @selected((string) old('moderator_speaker_id', $item->moderator_speaker_id ?? '') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="moderator_ppt_url">Moderator file URL/Path</label>
        <input class="form-control" type="text" name="moderator_ppt_url" id="moderator_ppt_url" value="{{ old('moderator_ppt_url', $item->moderator_ppt_url ?? '') }}">
        <small class="form-text text-muted">Allowed types: PDF, JPG, JPEG, Word, and PowerPoint.</small>
        <div class="mt-2">
            <input class="form-control-file" type="file" name="moderator_ppt_file" id="moderator_ppt_file" accept=".pdf,.jpg,.jpeg,.doc,.docx,.ppt,.pptx">
        </div>
    </div>
    <div class="form-group col-md-6">
        <label for="agenda_file_url">Session file URL/Path</label>
        <input class="form-control" type="text" name="agenda_file_url" id="agenda_file_url" value="{{ old('agenda_file_url', $item->agenda_file_url ?? '') }}">
        <small class="form-text text-muted">Allowed types: PDF, JPG, JPEG, Word, and PowerPoint.</small>
        <div class="mt-2">
            <input class="form-control-file" type="file" name="agenda_file" id="agenda_file" accept=".pdf,.jpg,.jpeg,.doc,.docx,.ppt,.pptx">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="speaker_ids">Speakers</label>
    <select class="form-control select2" name="speaker_ids[]" id="speaker_ids" multiple>
        @foreach($speakers as $id => $name)
            <option value="{{ $id }}" @selected(in_array((string) $id, $selectedSpeakers, true))>{{ $name }}</option>
        @endforeach
    </select>
    <small class="form-text text-muted">Agenda uses the linked speaker profile data directly, so speaker name, company, photo, and presentation file update automatically.</small>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label class="required" for="sort_order">Sort Order</label>
        <input class="form-control" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" required>
    </div>
    <div class="form-group col-md-3">
        <label class="required" for="is_active">Active</label>
        <select class="form-control" name="is_active" id="is_active" required>
            <option value="1" @selected((string) old('is_active', isset($item) ? ($item->is_active ? '1' : '0') : '1') === '1')>{{ trans('global.yes') }}</option>
            <option value="0" @selected((string) old('is_active', isset($item) ? ($item->is_active ? '1' : '0') : '1') === '0')>{{ trans('global.no') }}</option>
        </select>
    </div>
</div>
