<?php

namespace Tests\Feature\Frontend;

use App\Models\User;
use App\Models\VenueHotel;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class HotelReservationSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reservation_submission_stores_all_fields_and_notifies_admin(): void
    {
        $user = User::factory()->create();

        $hotel = VenueHotel::factory()->create([
            'name' => 'Royal Convention Hall',
            'booking_available_from' => '2026-06-15',
            'booking_available_to' => '2026-08-16',
            'hotel_rooms' => [[
                'name' => 'Classic Room',
                'caption' => 'Select your room type',
                'size' => '35 sqm / 376 sqf',
                'image' => 'images/misc/1.webp',
                'benefits' => ['Complimentary breakfast.', 'High-speed internet.'],
                'single_rate_inr' => 11000,
                'double_rate_inr' => 15000,
                'single_rate_usd' => 149,
                'double_rate_usd' => 199,
            ]],
        ]);

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->withArgs(function (string $templateKey, array $payload): bool {
                return $templateKey === 'hotel_booking'
                    && $payload['hotel_name'] === 'Royal Convention Hall'
                    && $payload['room_name'] === 'Classic Room'
                    && $payload['full_name'] === 'Test Booker'
                    && $payload['email'] === 'booker@gmail.com'
                    && $payload['phone'] === '9999999999'
                    && $payload['country'] === 'India'
                    && $payload['occupancy'] === 'double'
                    && $payload['bed_size'] === 'King Size Bed'
                    && $payload['preference'] === 'Non-Smoking Room'
                    && $payload['number_of_guests'] === 2
                    && $payload['number_of_rooms'] === 3
                    && $payload['number_of_nights'] === 2
                    && $payload['subtotal_inr'] === '90,000.00'
                    && $payload['gst_amount_inr'] === '0.00'
                    && $payload['total_inr'] === '90,000.00'
                    && $payload['special_requests'] === 'Airport pickup required.';
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $this->actingAs($user)
            ->post(route('frontend.venue.reserve.store', $hotel), [
                'selected_room_index' => 0,
                'full_name' => 'Test Booker',
                'email' => 'booker@gmail.com',
                'phone' => '9999999999',
                'country' => 'India',
                'number_of_rooms' => 3,
                'check_in_date' => '2026-06-20',
                'check_out_date' => '2026-06-22',
                'occupancy' => 'double',
                'number_of_guests' => 2,
                'bed_size' => 'King Size Bed',
                'preference' => 'Non-Smoking Room',
                'special_requests' => 'Airport pickup required.',
            ])
            ->assertRedirect(route('frontend.venue.reserve', ['venueHotel' => $hotel->id, 'room' => 0]));

        $this->assertDatabaseHas('hotel_bookings', [
            'venue_hotel_id' => $hotel->id,
            'hotel_name' => 'Royal Convention Hall',
            'room_name' => 'Classic Room',
            'full_name' => 'Test Booker',
            'email' => 'booker@gmail.com',
            'phone' => '9999999999',
            'country' => 'India',
            'occupancy' => 'double',
            'bed_size' => 'King Size Bed',
            'preference' => 'Non-Smoking Room',
            'check_in_date' => '2026-06-20',
            'check_out_date' => '2026-06-22',
            'number_of_guests' => 2,
            'number_of_rooms' => 3,
            'special_requests' => 'Airport pickup required.',
            'rate_inr' => 15000.00,
            'rate_usd' => 199.00,
            'number_of_nights' => 2,
            'subtotal_inr' => 90000.00,
            'subtotal_usd' => 1194.00,
            'gst_rate' => 18.00,
            'gst_amount_inr' => 0.00,
            'gst_amount_usd' => 0.00,
            'total_inr' => 90000.00,
            'total_usd' => 1194.00,
            'status' => 'pending',
        ]);
    }
}
