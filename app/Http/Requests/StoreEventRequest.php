<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StoreEventRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('event_create');
    }

    public function rules()
    {
        return [
            'title' => ['string', 'required', 'max:255'],
            'slug' => [
                'string',
                'required',
                'max:255',
                Rule::unique('events', 'slug')->whereNull('deleted_at'),
            ],
            'venue' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'gallery_url' => ['nullable', 'string', 'max:255'],
            'brochure_url' => ['nullable', 'string', 'max:255'],
            'ticket_hero_label' => ['nullable', 'string', 'max:120'],
            'ticket_hero_suffix' => ['nullable', 'string', 'max:255'],
            'ticket_hero_description' => ['nullable', 'string', 'max:500'],
            'is_published' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'plans' => ['nullable', 'array'],
            'plans.*.id' => ['nullable', 'integer'],
            'plans.*.name' => ['required', 'string', 'max:255'],
            'plans.*.caption' => ['nullable', 'string', 'max:255'],
            'plans.*.background_image_url' => ['nullable', 'string', 'max:255'],
            'plans.*.mrp_inr' => ['required', 'numeric', 'min:0'],
            'plans.*.sale_inr' => ['required', 'numeric', 'min:0'],
            'plans.*.mrp_usd' => ['required', 'numeric', 'min:0'],
            'plans.*.sale_usd' => ['required', 'numeric', 'min:0'],
            'plans.*.benefits' => ['nullable', 'string'],
            'plans.*.valid_till_text' => ['nullable', 'string', 'max:255'],
            'plans.*.is_active' => ['required', 'boolean'],
            'plans.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $plans = $this->input('plans', []);
            foreach ((array) $plans as $index => $row) {
                $mrpInr = (float) ($row['mrp_inr'] ?? 0);
                $saleInr = (float) ($row['sale_inr'] ?? 0);
                $mrpUsd = (float) ($row['mrp_usd'] ?? 0);
                $saleUsd = (float) ($row['sale_usd'] ?? 0);

                if ($saleInr > $mrpInr) {
                    $validator->errors()->add("plans.$index.sale_inr", 'INR sale price must be less than or equal to INR MRP.');
                }
                if ($saleUsd > $mrpUsd) {
                    $validator->errors()->add("plans.$index.sale_usd", 'USD sale price must be less than or equal to USD MRP.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('slug', '')),
        ]);
    }
}
