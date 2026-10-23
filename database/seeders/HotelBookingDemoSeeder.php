<?php

namespace Database\Seeders;

use App\Models\HotelBooking;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HotelBookingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $fromDate = Carbon::now()->addDays(2)->startOfDay();
        $toDate = Carbon::now()->addMonths(3)->endOfDay();

        Setting::query()->updateOrCreate(
            ['key' => 'hotel_booking_settings'],
            [
                'value' => json_encode([
                    'hotel_name' => 'Grand Bay Conference Hotel',
                    'hotel_address' => '121 AI Blvd, San Francisco, CA 94107',
                    'available_from_date' => $fromDate->toDateString(),
                    'available_to_date' => $toDate->toDateString(),
                    'direction_from' => 'San Francisco International Airport',
                    'direction_to' => '121 AI Blvd, San Francisco, CA 94107',
                    'admin_notification_email' => 'admin@example.com',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]
        );

        HotelBooking::query()->updateOrCreate(
            ['booking_code' => 'HB-DEMO001'],
            [
                'full_name' => 'Rahul Sharma',
                'email' => 'rahul.demo@example.com',
                'phone' => '+91 9876543210',
                'check_in_date' => $fromDate->copy()->addDays(7)->toDateString(),
                'check_out_date' => $fromDate->copy()->addDays(10)->toDateString(),
                'number_of_guests' => 2,
                'number_of_rooms' => 1,
                'special_requests' => 'Early check-in if available.',
                'status' => 'new',
            ]
        );

        HotelBooking::query()->updateOrCreate(
            ['booking_code' => 'HB-DEMO002'],
            [
                'full_name' => 'Anita Verma',
                'email' => 'anita.demo@example.com',
                'phone' => '+91 9988776655',
                'check_in_date' => $fromDate->copy()->addDays(14)->toDateString(),
                'check_out_date' => $fromDate->copy()->addDays(18)->toDateString(),
                'number_of_guests' => 3,
                'number_of_rooms' => 2,
                'special_requests' => 'Need airport pickup details.',
                'status' => 'confirmed',
            ]
        );
    }
}

