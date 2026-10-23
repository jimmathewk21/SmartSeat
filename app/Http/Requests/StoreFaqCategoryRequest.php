<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFaqCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('faq_category_create');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'max:255',
                Rule::unique('faq_categories', 'name')->whereNull('deleted_at'),
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }
}

