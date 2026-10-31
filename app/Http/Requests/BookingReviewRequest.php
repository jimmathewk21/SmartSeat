<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['reserve', 'waitlist'])],
            'seats' => ['sometimes', 'array', 'list', 'max:'.config('smartseat.booking.max_passengers')],
            'seats.*' => ['required', 'integer', 'min:1', 'distinct'],
        ];
    }
}
