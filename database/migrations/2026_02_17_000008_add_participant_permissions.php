<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddParticipantPermissions extends Migration
{
    public function up()
    {
        $titles = [
            'participant_access',
            'participant_show',
            'participant_approve',
        ];

        foreach ($titles as $title) {
            Permission::firstOrCreate(['title' => $title]);
        }

        $adminRole = Role::find(1);
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching(
                Permission::whereIn('title', $titles)->pluck('id')
            );
        }
    }

    public function down()
    {
        $titles = [
            'participant_access',
            'participant_show',
            'participant_approve',
        ];

        $permissionIds = Permission::whereIn('title', $titles)->pluck('id');
        if ($permissionIds->isNotEmpty()) {
            Role::query()->each(function (Role $role) use ($permissionIds) {
                $role->permissions()->detach($permissionIds);
            });
            Permission::whereIn('title', $titles)->delete();
        }
    }
}
