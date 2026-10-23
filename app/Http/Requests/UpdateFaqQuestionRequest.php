<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFaqQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('faq_access');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending', 'answered'])],
            'answer' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
