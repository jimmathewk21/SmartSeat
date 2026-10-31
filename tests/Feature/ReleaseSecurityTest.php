<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\BookingTestCase;

class ReleaseSecurityTest extends BookingTestCase
{
    public function test_invalid_array_input_can_render_validation_errors_on_authentication_forms(): void
    {
        $this->from(route('register'))->post(route('register.store'), ['name' => [], 'email' => [], 'password' => []])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->get(route('register'))->assertOk()->assertSee('Please check the following');
        $this->from(route('login'))->post(route('login.store'), ['email' => [], 'password' => []])
            ->assertSessionHasErrors(['email', 'password']);
        $this->get(route('login'))->assertOk()->assertSee('Please check the following');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_malformed_password_confirmation_is_a_validation_error(): void
    {
        $this->actingAs(User::factory()->create(), 'web');
        $this->postJson(route('password.confirm.store'), ['password' => ['unexpected']])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertNull(session('auth.password_confirmed_at'));
    }

    public function test_catalogue_and_passenger_forms_render_after_malformed_input(): void
    {
        $journey = $this->journey();
        $this->actingAs(User::factory()->administrator()->create(), 'web');
        $this->from(route('administrator.stations.index'))->post(route('administrator.stations.store'), ['code' => [], 'name' => [], 'city' => []])
            ->assertSessionHasErrors(['code', 'name', 'city']);
        $this->get(route('administrator.stations.index'))->assertOk()->assertSee('Please check the following');
        $this->get(route('bookings.create', $journey['trip']))->assertOk();
        $key = array_key_last(session('booking_drafts'));
        $input = $this->input();
        $input['passengers'][0]['name'] = [];
        $input['passengers'][0]['age'] = [];
        $this->from(route('bookings.create', $journey['trip']))->post(route('booking-drafts.details', $journey['trip']), $input + ['draft_key' => $key])
            ->assertSessionHasErrors(['passengers.0.name', 'passengers.0.age']);
        $this->get(route('bookings.create', $journey['trip']))->assertOk()->assertSee('Please check the following');
        $this->assertDatabaseCount('bookings', 0);
    }
}
