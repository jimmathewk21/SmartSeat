<?php

namespace Tests\Feature\Admin;

use App\Models\HotelBooking;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HotelBookingsAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_filter_hotel_bookings_by_guest_name_hotel_and_status(): void
    {
        $admin = $this->createHotelBookingAdmin();

        HotelBooking::query()->create($this->bookingPayload([
            'booking_code' => 'HB-ALPHA',
            'full_name' => 'Alice Booker',
            'hotel_name' => 'Hotel Alpha',
            'status' => 'new',
        ]));

        HotelBooking::query()->create($this->bookingPayload([
            'booking_code' => 'HB-BETA',
            'full_name' => 'Bob Guest',
            'hotel_name' => 'Hotel Beta',
            'status' => 'confirmed',
        ]));

        $response = $this->actingAs($admin)->get(route('admin.hotel-bookings.index', [
            'guest_name' => 'Alice',
            'hotel_name' => 'Hotel Alpha',
            'status' => 'pending',
        ]));

        $response->assertOk()
            ->assertSee('HB-ALPHA')
            ->assertSee('Pending')
            ->assertDontSee('HB-BETA');
    }

    public function test_admin_can_bulk_update_export_and_delete_hotel_bookings(): void
    {
        $admin = $this->createHotelBookingAdmin();

        $pending = HotelBooking::query()->create($this->bookingPayload([
            'booking_code' => 'HB-PENDING',
            'full_name' => 'Pending Booker',
            'status' => 'pending',
        ]));

        $ignored = HotelBooking::query()->create($this->bookingPayload([
            'booking_code' => 'HB-IGNORE',
            'full_name' => 'Ignored Booker',
            'status' => 'cancelled',
        ]));

        $this->actingAs($admin)
            ->post(route('admin.hotel-bookings.bulkStatus'), [
                'ids' => [$pending->id, $ignored->id],
                'status' => 'confirmed',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hotel_bookings', [
            'id' => $pending->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('hotel_bookings', [
            'id' => $ignored->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.hotel-bookings.export', [
            'ids' => [$pending->id],
        ]));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('HB-PENDING', $csv);
        $this->assertStringContainsString('Confirmed', $csv);
        $this->assertStringNotContainsString('HB-IGNORE', $csv);

        $this->actingAs($admin)
            ->delete(route('admin.hotel-bookings.massDestroy'), [
                'ids' => [$pending->id, $ignored->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('hotel_bookings', ['id' => $pending->id]);
        $this->assertDatabaseMissing('hotel_bookings', ['id' => $ignored->id]);
    }

    private function createHotelBookingAdmin(): User
    {
        $role = Role::firstOrCreate(['title' => 'Hotel Booking Admin']);
        Permission::insertOrIgnore([
            ['title' => 'hotel_booking_access'],
        ]);

        $permissionIds = Permission::whereIn('title', ['hotel_booking_access'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $user = User::factory()->create();
        $user->roles()->sync([$role->id]);

        return $user;
    }

    private function bookingPayload(array $overrides = []): array
    {
        return array_merge([
            'booking_code' => 'HB-DEFAULT',
            'full_name' => 'Sample Booker',
            'email' => 'sample@example.com',
            'phone' => '9999999999',
            'country' => 'India',
            'hotel_name' => 'Sample Hotel',
            'room_name' => 'Deluxe Room',
            'rate_inr' => 13000,
            'rate_usd' => 178,
            'occupancy' => 'single',
            'bed_size' => 'King Size Bed',
            'preference' => 'Non-Smoking Room',
            'check_in_date' => '2026-05-01',
            'check_out_date' => '2026-05-03',
            'number_of_guests' => 1,
            'number_of_rooms' => 1,
            'number_of_nights' => 2,
            'subtotal_inr' => 26000,
            'subtotal_usd' => 356,
            'gst_rate' => 18,
            'gst_amount_inr' => 0,
            'gst_amount_usd' => 0,
            'total_inr' => 26000,
            'total_usd' => 356,
            'special_requests' => 'Late check-in',
            'status' => 'pending',
            'created_at' => Carbon::parse('2026-05-01 10:00:00'),
            'updated_at' => Carbon::parse('2026-05-01 10:00:00'),
        ], $overrides);
    }
}
