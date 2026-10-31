<?php

namespace App\Http\Requests\Administrator;

use Illuminate\Validation\Rule;

class SeatRequest extends CatalogueRequest
{
    public function rules(): array
    {
        return [
            'number' => ['required', 'integer', 'between:1,200', Rule::unique('seats')->where('coach_id', $this->route('coach')->id)->ignore($this->route('seat'))],
            'berth_type' => ['required', Rule::in(['lower', 'middle', 'upper', 'side_lower', 'side_upper', 'chair'])],
            'accessibility_status' => ['sometimes', Rule::in(['unknown', 'accessible', 'not_accessible'])],
        ];
    }
}
