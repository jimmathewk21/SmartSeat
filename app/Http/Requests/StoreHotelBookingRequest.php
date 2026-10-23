<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'country' => ['required', 'string', 'max:120'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'occupancy' => ['required', 'string', 'in:single,double'],
            'number_of_guests' => ['required', 'integer', 'min:1', 'max:20'],
            'number_of_rooms' => ['required', 'integer', 'min:1', 'max:20'],
            'bed_size' => ['nullable', 'string', 'max:80'],
            'preference' => ['nullable', 'string', 'max:120'],
            'special_requests' => ['nullable', 'string', 'max:5000'],
            'selected_room_index' => ['required', 'integer', 'min:0'],
        ];
    }
}
