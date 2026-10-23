<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissionTitles = [
            'user_management_access',
            'user_access',
            'user_create',
            'user_edit',
            'user_delete',
        ];

        $permissions = collect($permissionTitles)->map(function (string $title): Permission {
            return Permission::firstOrCreate(['title' => $title]);
        });

        $adminRole = Role::query()->where('title', 'Admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($permissions->pluck('id')->all());
        }
    }

    public function down(): void
    {
        //
    }
};
