<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Actions\ConfirmPassword as FortifyConfirmPassword;

class ConfirmPassword extends FortifyConfirmPassword
{
    public function __invoke(StatefulGuard $guard, $user, #[\SensitiveParameter] mixed $password = null)
    {
        Validator::make(['password' => $password], ['password' => ['required', 'string', 'max:72']])->validate();

        return parent::__invoke($guard, $user, $password);
    }
}
