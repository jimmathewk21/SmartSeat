<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'boarding_station_id' => ['required', 'integer', 'different:destination_station_id', 'exists:stations,id'],
            'destination_station_id' => ['required', 'integer', 'different:boarding_station_id', 'exists:stations,id'],
            'inventory_ids' => ['required', 'array', 'min:1', 'max:6'],
            'inventory_ids.*' => ['required', 'integer', 'distinct', 'exists:trip_seat_inventories,id'],
            'passengers' => ['required', 'array', 'min:1', 'max:6'],
            'passengers.*.name' => ['required', 'string', 'max:255'],
            'passengers.*.age' => ['nullable', 'integer', 'between:0,120'],
            'passengers.*.gender' => ['nullable', 'string', 'max:20'],
            'passengers.*.requires_accessible_seat' => ['sometimes', 'boolean'],
        ];
    }
}
