<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('setting_edit');
    }

    public function rules()
    {
        $pageId = $this->route('page')?->id;

        return [
            'subtitle' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'title_suffix' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('pages', 'slug')->ignore($pageId),
            ],
            'content' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
