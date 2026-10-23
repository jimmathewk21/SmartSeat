<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('testimonial_edit');
    }

    public function rules()
    {
        return [
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'name' => ['string', 'required', 'max:255'],
            'photo_url' => ['nullable', 'string', 'max:255'],
            'photo_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'company' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'message' => ['required', 'string'],
            'experience_word' => ['nullable', 'string', 'max:80'],
            'stars' => ['required', 'integer', 'in:3,4,5'],
            'video_id' => ['nullable', 'string', 'max:128'],
            'testimonial_date' => ['nullable', 'date'],
            'mail_image_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'show_on_homepage' => ['required', 'boolean'],
            'show_on_scrollbar' => ['required', 'boolean'],
            'is_key_figure' => ['required', 'boolean'],
        ];
    }
}
