<?php

namespace App\Actions\Fortify;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\AccountDetails;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function create(#[\SensitiveParameter] array $input): User
    {
        $details = AccountDetails::validate($input);
        $user = new User($details);
        $user->role = UserRole::Passenger;
        $user->save();

        return $user;
    }
}
