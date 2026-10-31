<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\AccountDetails;

final class CreateAdministratorAccount
{
    /** Internal bootstrap action. The command must verify the target first. */
    public function create(#[\SensitiveParameter] array $input): User
    {
        $details = AccountDetails::validate($input);
        $user = new User($details);
        $user->role = UserRole::Administrator;
        // Insert only: uniqueness is also enforced by the users.email index.
        $user->save();

        return $user;
    }
}
