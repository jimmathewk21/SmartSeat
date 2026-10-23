<?php

namespace Database\Seeders;

use App\Models\MyContact;
use Illuminate\Database\Seeder;

class MyContactsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name' => 'Aarav Mehta',
                'designation' => 'Passenger Support',
                'company_name' => 'SmartSeat',
                'official_email' => 'aarav.mehta@example.com',
                'mobile_number' => '+919800001101',
                'country' => 'India',
                'category' => 'Customer Support',
                'role' => 'Support',
                'contact_status' => MyContact::STATUS_KNOWN,
            ],
            [
                'name' => 'Ananya Rao',
                'designation' => 'Train Operations',
                'company_name' => 'SmartSeat',
                'official_email' => 'ananya.rao@example.com',
                'mobile_number' => '+919800001102',
                'country' => 'India',
                'category' => 'Rail Operations',
                'role' => 'Operations',
                'contact_status' => MyContact::STATUS_DEFAULT,
            ],
            [
                'name' => 'Kabir Nair',
                'designation' => 'Passenger Assistance',
                'company_name' => 'SmartSeat',
                'official_email' => 'kabir.nair@example.com',
                'mobile_number' => '+919800001103',
                'country' => 'India',
                'category' => 'Customer Support',
                'role' => 'Support',
                'contact_status' => MyContact::STATUS_DEFAULT,
            ],
        ];

        foreach ($rows as $row) {
            MyContact::query()->updateOrCreate(
                ['official_email' => $row['official_email']],
                array_merge($row, [
                    'event_name' => null,
                    'event_names' => null,
                    'is_subscribed' => true,
                    'is_published' => true,
                    'show_email' => true,
                    'show_whatsapp' => false,
                    'show_linkedin' => false,
                    'show_website' => false,
                    'show_category' => true,
                    'show_expertise' => false,
                ])
            );
        }
    }
}
