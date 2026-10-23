<div class="form-group">
    <label class="required" for="event_id">Event</label>
    <select class="form-control select2" name="event_id" id="event_id" required>
        @foreach($events as $id => $title)
            <option value="{{ $id }}" @selected((string) old('event_id', $day->event_id ?? '') === (string) $id)>{{ $title }}</option>
        @endforeach
    </select>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label class="required" for="title">Day Title (ex: 14th Feb)</label>
        <input class="form-control" type="text" name="title" id="title" value="{{ old('title', $day->title ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label for="weekday">Weekday</label>
        <input class="form-control" type="text" name="weekday" id="weekday" value="{{ old('weekday', $day->weekday ?? '') }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="agenda_date">Date</label>
        <input class="form-control" type="date" name="agenda_date" id="agenda_date" value="{{ old('agenda_date', optional($day->agenda_date ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="form-group col-md-3">
        <label class="required" for="sort_order">Sort Order</label>
        <input class="form-control" type="number" min="0" name="sort_order" id="sort_order" value="{{ old('sort_order', $day->sort_order ?? 0) }}" required>
    </div>
    <div class="form-group col-md-3">
        <label class="required" for="is_active">Active</label>
        <select class="form-control" name="is_active" id="is_active" required>
            <option value="1" @selected((string) old('is_active', isset($day) ? ($day->is_active ? '1' : '0') : '1') === '1')>{{ trans('global.yes') }}</option>
            <option value="0" @selected((string) old('is_active', isset($day) ? ($day->is_active ? '1' : '0') : '1') === '0')>{{ trans('global.no') }}</option>
        </select>
    </div>
</div>

