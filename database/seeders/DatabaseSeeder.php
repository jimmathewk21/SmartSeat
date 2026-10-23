<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            PermissionRoleTableSeeder::class,
            UsersTableSeeder::class,
            RoleUserTableSeeder::class,
            CountriesTableSeeder::class,
            EventDemoSeeder::class,
            TicketDemoSeeder::class,
            SponsorDemoSeeder::class,
            TestimonialsDemoSeeder::class,
            FaqDemoSeeder::class,
            AgendaDemoSeeder::class,
            DownloadDemoSeeder::class,
            WorldEventsDemoSeeder::class,
            MyContactsDemoSeeder::class,
            AttractionsDemoSeeder::class,
            HotelBookingDemoSeeder::class,
        ]);
    }
}
