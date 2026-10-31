<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BookingEngine;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\BookingTestCase;

class SeatPreferenceBookingTest extends BookingTestCase
{
    public function test_manual_seat_selection_is_not_overridden_by_soft_recommendations(): void
    {
        $journey = $this->journey();
        $second = $this->addSeat($journey);
        $journey['trip']->inventory()->findOrFail($second)->seat->update(['berth_type' => 'upper']);
        $data = $this->input();
        $data['passengers'][0]['preferred_berth'] = 'lower';
        $booking = app(BookingEngine::class)->book(User::factory()->create(), $journey['trip'], $data, 'reserve', [$second], (string) Str::uuid());
        $this->assertSame($second, $booking->passengers()->first()->assignments()->value('inventory_id'));
        $this->assertDatabaseHas('passengers', ['booking_id' => $booking->id, 'preferred_berth' => 'lower']);
    }

    public function test_accessibility_is_required_on_manual_selection_and_waitlist_promotion(): void
    {
        $journey = $this->journey();
        $journey['seat']->update(['accessibility_status' => 'accessible']);
        $second = $this->addSeat($journey);
        $reserved = $this->reserve($journey);
        $owner = User::factory()->create();
        $data = $this->input();
        $data['passengers'][0]['accessible_seat_required'] = true;
        $engine = app(BookingEngine::class);
        try {
            $engine->book($owner, $journey['trip'], $data, 'reserve', [$second], (string) Str::uuid());
            $this->fail('Unknown accessibility must not satisfy an explicit requirement.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('seats', $exception->errors());
        }
        $waiting = $engine->book($owner, $journey['trip'], $data, 'waitlist', [], (string) Str::uuid());
        $this->assertSame('waitlisted', $waiting->status);
        $engine->cancel($reserved->user, $reserved);
        $assignment = $waiting->passengers()->first()->assignments()->firstOrFail();
        $this->assertSame($journey['seat']->id, $assignment->inventory->seat_id);
        $this->assertSame('confirmed', $waiting->refresh()->status);
    }

    public function test_existing_default_submission_hashes_remain_compatible(): void
    {
        $journey = $this->journey();
        $owner = User::factory()->create();
        $key = (string) Str::uuid();
        $ids = [$journey['trip']->inventory()->value('id')];
        $booking = $this->reserve($journey, $owner, $ids, $key);
        $legacy = hash('sha256', json_encode([$journey['trip']->id, $this->input(), 'reserve', $ids], JSON_THROW_ON_ERROR));
        $this->assertSame($legacy, $booking->request_hash);
        $this->assertSame($booking->id, $this->reserve($journey, $owner, $ids, $key)->id);
    }

    public function test_recommendation_ui_requires_an_explicit_action_and_does_not_reserve_on_get(): void
    {
        $journey = $this->journey();
        $this->actingAs(User::factory()->create(), 'web')->get(route('bookings.create', $journey['trip']))->assertOk()->assertSee('Preferred berth');
        $key = array_key_last(session('booking_drafts'));
        $data = $this->input();
        $data['keep_group_together'] = true;
        $data['passengers'][0]['preferred_berth'] = 'lower';
        $this->post(route('booking-drafts.details', $journey['trip']), $data + ['draft_key' => $key])->assertRedirect();
        $this->get(route('booking-drafts.seats', $key))->assertOk()->assertSee('Recommended seats')->assertSee('Use these suggestions for review')->assertSee('explicit lower');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertNull(session('booking_drafts.'.$key.'.review'));
    }
}
