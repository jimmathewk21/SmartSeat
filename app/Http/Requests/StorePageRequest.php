<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('setting_edit');
    }

    public function rules()
    {
        return [
            'subtitle' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'title_suffix' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:pages,slug'],
            'content' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
