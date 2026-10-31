<?php

namespace App\Support;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class AccountDetails
{
    /** Validate shared registration/bootstrap fields; never return privilege input. */
    public static function validate(#[\SensitiveParameter] array $input): array
    {
        foreach (['name', 'email'] as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $input[$field] = trim($input[$field]);
            }
        }

        if (isset($input['email']) && is_string($input['email'])) {
            $input['email'] = Str::lower($input['email']);
        }

        return Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => [
                'bail', 'required', 'string', 'max:72',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    // Bcrypt limits bytes, while Laravel's max rule counts characters.
                    if (strlen($value) > 72 || str_contains($value, "\0")) {
                        $fail('The password must be at most 72 bytes and must not contain null characters.');
                    }
                },
                Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed',
            ],
        ])->validate();
    }
}
