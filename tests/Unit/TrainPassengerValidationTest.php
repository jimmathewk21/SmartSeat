<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\TrainBookingService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TrainPassengerValidationTest extends TestCase
{
    public function test_booking_rejects_missing_passenger_name_before_database_access(): void
    {
        try {
            app(TrainBookingService::class)->book(new User(), 1, [1], [['age' => 28]], 1, 2);
            $this->fail('Booking requires a passenger name.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('passengers.0.name', $exception->errors());
        }
    }

    public function test_waitlist_rejects_missing_passenger_name_before_database_access(): void
    {
        try {
            app(TrainBookingService::class)->joinWaitlist(new User(), 1, [['age' => 28]], 1, 2);
            $this->fail('Waitlist entries require a passenger name.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('passengers.0.name', $exception->errors());
        }
    }
}
