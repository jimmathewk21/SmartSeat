<?php

namespace App\Http\Requests;

use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('user_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'mobile' => [
                'string',
                'nullable',
            ],
            'company_name' => [
                'string',
                'nullable',
            ],
            'email' => [
                'required',
                'unique:users,email,' . request()->route('user')->id,
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],
            'roles' => [
                'array',
            ],
            'user_type' => [
                'string',
                'nullable',
            ],
            'approve_train_owner' => ['nullable', 'boolean'],
            'train_ids' => ['nullable', 'array'],
            'train_ids.*' => ['integer', 'distinct', 'exists:trains,id'],
        ];
    }
}
