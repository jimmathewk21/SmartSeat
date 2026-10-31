<?php

namespace App\Http\Requests\Administrator;

use Illuminate\Validation\Rule;

class RouteStopRequest extends CatalogueRequest
{
    public function rules(): array
    {
        $train = $this->route('train');
        $stop = $this->route('routeStop');

        return [
            'station_code' => ['required', 'string', 'regex:/\A[A-Z]{1,5}\z/', 'exists:stations,code'],
            'stop_order' => ['required', 'integer', 'between:1,100', Rule::unique('train_route_stops')->where('train_id', $train->id)->ignore($stop)],
            'arrival_offset_minutes' => ['required', 'integer', 'between:0,44639'],
            'departure_offset_minutes' => ['required', 'integer', 'between:0,44639', 'gte:arrival_offset_minutes'],
        ];
    }
}
