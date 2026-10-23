<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddLoginActivityPermissions extends Migration
{
    public function up()
    {
        $permissions = [
            'login_activity_show',
            'login_activity_access',
        ];

        foreach ($permissions as $title) {
            Permission::firstOrCreate(['title' => $title]);
        }

        $adminRole = Role::find(1);
        if ($adminRole) {
            $adminPermissionIds = Permission::whereIn('title', $permissions)->pluck('id');
            $adminRole->permissions()->syncWithoutDetaching($adminPermissionIds);
        }
    }

    public function down()
    {
        $titles = [
            'login_activity_show',
            'login_activity_access',
        ];

        $permissionIds = Permission::whereIn('title', $titles)->pluck('id');
        if ($permissionIds->isNotEmpty()) {
            Role::query()->each(function (Role $role) use ($permissionIds) {
                $role->permissions()->detach($permissionIds);
            });
        }

        Permission::whereIn('title', $titles)->delete();
    }
}
