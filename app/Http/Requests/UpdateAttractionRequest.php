<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttractionRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('attraction_edit');
    }

    public function rules()
    {
        return [
            'name' => ['string', 'required', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'direction_url' => ['nullable', 'url', 'max:255'],
            'detail' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'show_on_homepage' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
