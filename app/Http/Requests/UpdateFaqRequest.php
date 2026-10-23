<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('faq_edit');
    }

    public function rules()
    {
        return [
            'faq_category_id' => ['required', 'integer', 'exists:faq_categories,id'],
            'question' => ['string', 'required', 'max:255'],
            'answer' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'show_on_homepage' => ['required', 'boolean'],
        ];
    }
}
