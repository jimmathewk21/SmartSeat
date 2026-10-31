<?php

namespace App\Support;

use Illuminate\Validation\Rule;

final class BookingRules
{
    public const CLASSES = ['SL', '3A', '2A', '1A', 'CC', '2S', 'EC'];

    public static function details(): array
    {
        return [
            'boarding_order' => ['required', 'integer', 'between:1,100'],
            'alighting_order' => ['required', 'integer', 'between:1,100', 'gt:boarding_order'],
            'travel_class' => ['required', Rule::in(self::CLASSES)],
            'keep_group_together' => ['sometimes', 'boolean'],
            'passengers' => ['required', 'array', 'list', 'min:1', 'max:'.config('smartseat.booking.max_passengers')],
            'passengers.*' => ['required', 'array:name,age,gender,preferred_berth,accessible_seat_required'],
            'passengers.*.name' => ['required', 'string', 'max:150', 'regex:/\\S/u', 'not_regex:/[[:cntrl:]]/u'],
            'passengers.*.age' => ['required', 'integer', 'between:0,120'],
            'passengers.*.gender' => ['required', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'passengers.*.preferred_berth' => ['sometimes', Rule::in(['any', 'lower', 'middle', 'upper', 'side_lower', 'side_upper', 'chair'])],
            'passengers.*.accessible_seat_required' => ['sometimes', 'boolean'],
        ];
    }
}
