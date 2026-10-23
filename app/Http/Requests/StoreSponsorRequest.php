<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSponsorRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('sponsor_create');
    }

    public function rules()
    {
        return [
            'sponsor_category_id' => [
                'required',
                'integer',
                'exists:sponsor_categories,id',
            ],
            'sponsor_category_ids' => [
                'nullable',
                'array',
                'min:1',
            ],
            'sponsor_category_ids.*' => [
                'integer',
                'exists:sponsor_categories,id',
            ],
            'name' => [
                'string',
                'required',
                'max:255',
            ],
            'sub_category' => [
                'nullable',
                'string',
                'max:255',
            ],
            'use_sub_category_text' => [
                'required',
                'boolean',
            ],
            'sub_category_text' => [
                'nullable',
                'string',
                'max:255',
            ],
            'website_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'linkedin_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'logo_url' => [
                'nullable',
                'string',
                'max:255',
            ],
            'logo_size' => [
                'nullable',
                Rule::in(['sm', 'md', 'lg']),
            ],
            'logo_file' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp,svg',
                'max:4096',
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
            'detail' => [
                'nullable',
                'string',
            ],
            'asset_type' => [
                'required',
                Rule::in(['file', 'link']),
            ],
            'resource_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'contact_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'contact_designation' => [
                'nullable',
                'string',
                'max:255',
            ],
            'contact_mobile' => [
                'nullable',
                'string',
                'max:255',
            ],
            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'advert_url' => [
                'nullable',
                'string',
                'max:255',
            ],
            'advert_file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png,webp',
                'max:15360',
            ],
            'brochure_url' => [
                'nullable',
                'string',
                'max:255',
            ],
            'brochure_file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png,webp',
                'max:15360',
            ],
            'sponsor_video_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'show_on_homepage' => [
                'required',
                'boolean',
            ],
            'show_on_scrollbar' => [
                'required',
                'boolean',
            ],
            'show_contact_details' => [
                'required',
                'boolean',
            ],
            'show_website_url' => [
                'required',
                'boolean',
            ],
            'show_linkedin_url' => [
                'required',
                'boolean',
            ],
            'show_bio' => [
                'required',
                'boolean',
            ],
            'show_advert' => [
                'required',
                'boolean',
            ],
        ];
    }
}
