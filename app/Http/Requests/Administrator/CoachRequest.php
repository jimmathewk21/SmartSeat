<?php

namespace App\Http\Requests\Administrator;

use Illuminate\Validation\Rule;

class CoachRequest extends CatalogueRequest
{
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'regex:/\\A[A-Z0-9]{1,10}\\z/', Rule::unique('coaches')->where('train_id', $this->route('train')->id)->ignore($this->route('coach'))],
            'travel_class' => ['required', Rule::in(['SL', '3A', '2A', '1A', 'CC', '2S', 'EC'])],
        ];
    }
}
