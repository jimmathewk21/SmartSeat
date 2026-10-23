<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddTicketBookingPermissions extends Migration
{
    public function up()
    {
        $titles = [
            'ticket_booking_access',
            'ticket_booking_show',
            'ticket_booking_export',
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
            'ticket_booking_access',
            'ticket_booking_show',
            'ticket_booking_export',
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

