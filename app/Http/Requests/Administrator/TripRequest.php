<?php

namespace App\Http\Requests\Administrator;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;

class TripRequest extends CatalogueRequest
{
    public function rules(): array
    {
        return [
            'service_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.CarbonImmutable::today(config('smartseat.railway_timezone'))->toDateString(), Rule::unique('train_trips')->where('train_id', $this->route('train')->id)],
        ];
    }
}
