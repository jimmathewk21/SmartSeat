<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class MassDestroyFaqCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('faq_category_delete');
    }

    public function rules()
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:faq_categories,id'],
        ];
    }
}

