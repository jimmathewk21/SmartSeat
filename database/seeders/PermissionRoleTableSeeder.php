<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionRoleTableSeeder extends Seeder
{
    public function run()
    {
        $admin_permissions = Permission::all();
        Role::findOrFail(1)->permissions()->sync($admin_permissions->pluck('id'));
        $user_permissions = $admin_permissions->filter(function ($permission) {
            return substr($permission->title, 0, 5) != 'user_'
                && substr($permission->title, 0, 5) != 'role_'
                && substr($permission->title, 0, 11) != 'permission_'
                && ! str_starts_with($permission->title, 'train_management_')
                && $permission->title !== 'developer_options';
        });
        Role::findOrFail(2)->permissions()->sync($user_permissions);
        Role::where('title', 'Train Owner')->firstOrFail()->permissions()->sync(
            Permission::whereIn('title', ['train_management_access', 'train_management_edit'])->pluck('id')
        );
    }
}
