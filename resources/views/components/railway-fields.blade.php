@props(['type', 'record' => null])
@php
    $fields = match ($type) {
        'station' => [
            'code' => ['Station code', 'text', 'Uppercase Indian railway code, 1–5 letters.'],
            'name' => ['Station name', 'text', 'Use the full station name.'],
            'city' => ['City', 'text', 'City or town served by this station.'],
        ],
        'train' => [
            'number' => ['Train number', 'text', 'Exactly five digits; leading zeroes are preserved.'],
            'name' => ['Train name', 'text', 'A clear public name, up to 150 characters.'],
            'is_active' => ['Catalogue status', ['1' => 'Active', '0' => 'Inactive'], 'Only active trains can be scheduled.'],
        ],
        'stop' => [
            'station_code' => ['Station code', 'text', 'Use an existing station code from Stations.'],
            'stop_order' => ['Route position', 'number', 'Unique position from 1 to 100, in travel order.'],
            'arrival_offset_minutes' => ['Arrival: minutes from origin midnight', 'number', 'Example: day 0 at 08:00 = 480; day 1 at 08:00 = 1920.'],
            'departure_offset_minutes' => ['Departure: minutes from origin midnight', 'number', 'Include overnight days. Departure must follow arrival and precede the next stop.'],
        ],
        'coach' => [
            'label' => ['Coach label', 'text', 'Unique on this train, using 1–10 uppercase letters or digits.'],
            'travel_class' => ['Travel class', array_combine(['SL','3A','2A','1A','CC','2S','EC'], ['SL','3A','2A','1A','CC','2S','EC']), 'Choose the coach class. A train can contain up to 30 coaches.'],
        ],
        'seat' => [
            'number' => ['Seat number', 'number', 'A unique seat number from 1 to 200 within this coach.'],
            'berth_type' => ['Berth type', ['lower'=>'Lower','middle'=>'Middle','upper'=>'Upper','side_lower'=>'Side lower','side_upper'=>'Side upper','chair'=>'Chair'], 'Choose the berth or seated accommodation.'],
            'accessibility_status' => ['Accessibility verification', ['unknown'=>'Not verified','accessible'=>'Verified accessible','not_accessible'=>'Not accessible'], 'Only mark accessible after checking the actual seat. Lower berths are not automatically accessible.'],
        ],
        'trip' => ['service_date' => ['Origin departure date', 'date', 'The date at the first station, in India time. Downstream boarding may be on a later day.']],
    };
@endphp
<div class="railway-fields">
@foreach($fields as $name => [$label, $kind, $help])
    @php
        $initial = $name === 'station_code' ? $record?->station?->code : data_get($record, $name, $name === 'is_active' ? 1 : '');
        $value = \App\Support\FormValue::text(old($name, $initial));
    @endphp
    <div class="field">
        <label for="{{ $name }}">{{ $label }}</label>
        @if(is_array($kind))
            <select id="{{ $name }}" name="{{ $name }}" aria-describedby="{{ $name }}-help" required>
                @foreach($kind as $key => $option)<option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $option }}</option>@endforeach
            </select>
        @else
            <input id="{{ $name }}" name="{{ $name }}" type="{{ $kind }}" value="{{ $value }}" aria-describedby="{{ $name }}-help" required @if($kind === 'number') min="{{ str_contains($name, 'offset') ? 0 : 1 }}" max="{{ $name === 'stop_order' ? 100 : ($name === 'number' ? 200 : 44639) }}" @endif @if($type === 'train' && $name === 'number') inputmode="numeric" minlength="5" maxlength="5" pattern="[0-9]{5}" @endif>
        @endif
        <small id="{{ $name }}-help">{{ $help }}</small>
    </div>
@endforeach
</div>
