<?php

namespace Tests\Unit;

use App\Services\TrainDemoSeederPreflight;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class TrainDemoSeederPreflightTest extends TestCase
{
    #[Test]
    public function it_aborts_when_a_reserved_trip_has_booking_history(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('1 bookings');

        TrainDemoSeederPreflight::assertSafeCounts([
            'bookings' => 1,
            'waitlist entries' => 0,
            'booking-seat assignments' => 0,
            'booking passengers' => 0,
            'waitlist passengers' => 0,
            'booked or inconsistent inventory rows' => 0,
            'route stops using reserved stations from other trains' => 0,
        ]);
    }

    #[Test]
    public function it_aborts_when_reserved_inventory_or_assignments_have_state(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('booking-seat assignments');

        TrainDemoSeederPreflight::assertSafeCounts([
            'bookings' => 0,
            'waitlist entries' => 0,
            'booking-seat assignments' => 1,
            'booking passengers' => 0,
            'waitlist passengers' => 0,
            'booked or inconsistent inventory rows' => 1,
            'route stops using reserved stations from other trains' => 0,
        ]);
    }

    #[Test]
    public function it_allows_a_clean_reserved_dataset_to_pass_the_safety_policy(): void
    {
        $result = TrainDemoSeederPreflight::assertSafeCounts([
            'bookings' => 0,
            'waitlist entries' => 0,
            'booking-seat assignments' => 0,
            'booking passengers' => 0,
            'waitlist passengers' => 0,
            'booked or inconsistent inventory rows' => 0,
            'route stops using reserved stations from other trains' => 0,
        ]);
        $this->assertNull($result);
    }
}
