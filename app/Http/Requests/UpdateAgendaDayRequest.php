<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAgendaDayRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('event_access');
    }

    public function rules()
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'title' => ['required', 'string', 'max:255'],
            'weekday' => ['nullable', 'string', 'max:100'],
            'agenda_date' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

