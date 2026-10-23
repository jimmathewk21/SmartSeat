<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('member_edit');
    }

    public function rules()
    {
        return [
            'name' => ['string', 'required', 'max:255'],
            'photo_url' => ['nullable', 'url', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
