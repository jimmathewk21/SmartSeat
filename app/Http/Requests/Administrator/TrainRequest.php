<?php

namespace App\Http\Requests\Administrator;

use Illuminate\Validation\Rule;

class TrainRequest extends CatalogueRequest
{
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'regex:/\\A[0-9]{5}\\z/', Rule::unique('trains')->ignore($this->route('train'))],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
