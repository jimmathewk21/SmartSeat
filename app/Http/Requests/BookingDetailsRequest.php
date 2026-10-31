<?php

namespace App\Http\Requests;

use App\Support\BookingRules;
use Illuminate\Foundation\Http\FormRequest;

class BookingDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return BookingRules::details() + ['draft_key' => ['required', 'uuid']];
    }
}
