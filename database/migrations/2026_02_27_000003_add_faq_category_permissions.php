<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddFaqCategoryPermissions extends Migration
{
    public function up()
    {
        $titles = [
            'faq_category_access',
            'faq_category_create',
            'faq_category_edit',
            'faq_category_show',
            'faq_category_delete',
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
            'faq_category_access',
            'faq_category_create',
            'faq_category_edit',
            'faq_category_show',
            'faq_category_delete',
        ];

        $permissionIds = Permission::whereIn('title', $titles)->pluck('id');
        if ($permissionIds->isNotEmpty()) {
            Role::query()->each(function (Role $role) use ($permissionIds) {
                $role->permissions()->detach($permissionIds);
            });

            Permission::whereIn('id', $permissionIds)->delete();
        }
    }
}

