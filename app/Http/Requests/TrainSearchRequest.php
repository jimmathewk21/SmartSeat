<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class TrainSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_station_id' => ['required_with:to_station_id,date', 'nullable', 'integer', 'exists:stations,id', 'different:to_station_id'],
            'to_station_id' => ['required_with:from_station_id,date', 'nullable', 'integer', 'exists:stations,id', 'different:from_station_id'],
            'date' => ['required_with:from_station_id,to_station_id', 'nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }
}
