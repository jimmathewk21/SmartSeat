<?php

namespace App\Http\Requests;

use App\Support\TicketCheckoutSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventOrderRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $settings = TicketCheckoutSettings::get();
        $minQty = max(1, (int) ($settings['min_ticket_quantity'] ?? 1));
        $maxQty = max($minQty, (int) ($settings['max_ticket_quantity'] ?? 10));
        $isSummaryRoute = $this->routeIs('frontend.events.summary');

        return [
            'event_ticket_plan_id' => ['required', 'integer', 'exists:event_ticket_plans,id'],
            'quantity' => ['required', 'integer', 'min:' . $minQty, 'max:' . $maxQty],
            'billing_country_code' => ['required', 'string', 'size:2'],
            'preferred_currency' => ['nullable', 'string', Rule::in(['INR', 'USD'])],
            'billing_company_name' => [$isSummaryRoute ? 'nullable' : 'required', 'string', 'max:255'],
            'billing_address_line_1' => [$isSummaryRoute ? 'nullable' : 'required', 'string', 'max:255'],
            'billing_address_line_2' => ['nullable', 'string', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:50'],
            'promo_code' => ['nullable', 'string', 'size:6'],
            'delegates' => [$isSummaryRoute ? 'nullable' : 'required', 'array', 'min:1'],
            'delegates.*.delegate_name' => [$isSummaryRoute ? 'nullable' : 'required', 'string', 'max:255'],
            'delegates.*.designation' => ['nullable', 'string', 'max:255'],
            'delegates.*.company_name' => [$isSummaryRoute ? 'nullable' : 'required', 'string', 'max:255'],
            'delegates.*.country' => [$isSummaryRoute ? 'nullable' : 'required', 'string', 'max:120'],
            'delegates.*.email' => [$isSummaryRoute ? 'nullable' : 'required', 'email', 'max:255'],
            'delegates.*.mobile' => [$isSummaryRoute ? 'nullable' : 'required', 'string', 'max:40'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $billingCountryCode = strtoupper((string) $this->input('billing_country_code', ''));

        $this->merge([
            'billing_country_code' => $billingCountryCode,
            'preferred_currency' => strtoupper((string) $this->input('preferred_currency', 'INR')),
            'gstin' => $billingCountryCode === 'IN'
                ? (string) $this->input('gstin', '')
                : '',
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $currency = strtoupper((string) $this->input('preferred_currency', 'INR'));
            $countryCode = strtoupper((string) $this->input('billing_country_code', ''));

            if ($currency === 'INR' && $countryCode !== 'IN') {
                $validator->errors()->add('billing_country_code', 'For INR, billing country must be India.');
            }

            if ($currency === 'USD' && $countryCode === 'IN') {
                $validator->errors()->add('billing_country_code', 'For USD, billing country cannot be India.');
            }
        });
    }
}
