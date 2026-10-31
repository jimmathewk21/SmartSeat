<?php

namespace App\Http\Requests\Administrator;

class TrainWaitlistModelRequest extends CatalogueRequest
{
    public function rules(): array
    {
        return ['confirm_training' => ['required', 'accepted']];
    }
}
