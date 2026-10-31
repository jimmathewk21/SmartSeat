<?php

namespace App\Http\Requests\Administrator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

abstract class CatalogueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('access-administrator');
    }
}
