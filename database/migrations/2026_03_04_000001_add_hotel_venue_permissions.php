<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddHotelVenuePermissions extends Migration
{
    public function up()
    {
        $titles = [
            'venue_access',
            'hotel_booking_access',
            'hotel_booking_settings_access',
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
            'venue_access',
            'hotel_booking_access',
            'hotel_booking_settings_access',
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

