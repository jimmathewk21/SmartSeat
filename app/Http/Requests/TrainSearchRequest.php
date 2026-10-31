<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class TrainSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if (! $this->hasAny(['origin', 'destination', 'journey_date'])) {
            return [];
        }

        return [
            'origin' => ['bail', 'required', 'string', 'regex:/\\A[A-Z]{1,5}\\z/', 'exists:stations,code'],
            'destination' => ['bail', 'required', 'string', 'different:origin', 'regex:/\\A[A-Z]{1,5}\\z/', 'exists:stations,code'],
            'journey_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.CarbonImmutable::today(config('smartseat.railway_timezone'))->toDateString()],
        ];
    }
}
