<?php

namespace App\Http\Requests\Administrator;

use Illuminate\Validation\Rule;

class StationRequest extends CatalogueRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'regex:/\\A[A-Z]{1,5}\\z/', Rule::unique('stations')->ignore($this->route('station'))],
            'name' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
        ];
    }
}
