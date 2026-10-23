<?php

namespace Tests\Feature\Auth;

use App\Models\Participant;
use App\Models\Station;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class TrainAuthenticationFlowTest extends TestCase
{
    private bool $testTransactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDefaultConnection() !== 'mysql'
            || config('database.connections.mysql.database') !== 'smartseat_train_test') {
            $this->markTestSkipped('Requires the separately prepared smartseat_train_test database.');
        }

        if (DB::connection('mysql')->getDatabaseName() !== 'smartseat_train_test') {
            $this->markTestSkipped('The active MySQL database is not smartseat_train_test.');
        }

        if (! Schema::connection('mysql')->hasColumn('participants', 'train_access_enabled')) {
            $this->markTestSkipped('Requires the additive train-access migration on smartseat_train_test.');
        }

        DB::connection('mysql')->beginTransaction();
        $this->testTransactionStarted = true;
    }

    protected function tearDown(): void
    {
        if ($this->testTransactionStarted && DB::connection('mysql')->transactionLevel() > 0) {
            DB::connection('mysql')->rollBack();
        }

        parent::tearDown();
    }

    public function test_train_sign_in_returns_to_validated_search_and_uses_railway_ui(): void
    {
        $from = $this->station('Origin');
        $to = $this->station('Destination');
        $search = [
            'from_station_id' => $from->id,
            'to_station_id' => $to->id,
            'date' => now()->addDay()->toDateString(),
        ];
        $expected = route('frontend.trains.search', $search);

        $loginPage = $this->get(route('frontend.trains.login', [...$search, 'redirect' => 'https://example.invalid']));
        $loginPage
            ->assertOk()
            ->assertSee('SmartSeat')
            ->assertSee('Train booking')
            ->assertDontSee('EVENT')
            ->assertDontSee('conference')
            ->assertDontSee('Registration Submitted')
            ->assertDontSee('success-modal');
        $this->assertStringNotContainsString(route('train-owner.login'), $loginPage->getContent());

        $this->assertSame(route('frontend.trains.search', $search, false), session('url.intended'));

        $user = User::factory()->create([
            'email' => 'train-login-'.Str::lower(Str::random(8)).'@example.test',
            'password' => 'secret123',
        ]);

        $this->post(route('login'), [
            '_token' => $this->csrfToken(),
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect($expected);

        $this->assertAuthenticatedAs($user);
    }

    public function test_pending_train_user_stays_blocked_and_gets_train_specific_notice(): void
    {
        $user = User::factory()->create([
            'email' => 'train-pending-'.Str::lower(Str::random(8)).'@example.test',
            'password' => 'secret123',
        ]);
        Participant::factory()->forUser($user)->create(['is_approved' => false]);

        $this->get(route('frontend.trains.login'))->assertOk();
        $this->post(route('login'), [
            '_token' => $this->csrfToken(),
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect(route('frontend.trains.login'))
            ->assertSessionHas('train_auth_notice')
            ->assertSessionMissing('pending_review_message');

        $this->assertGuest();
        $this->get(route('frontend.trains.login'))
            ->assertOk()
            ->assertSee('awaiting administrator approval')
            ->assertDontSee('EVENT')
            ->assertDontSee('conference')
            ->assertDontSee('Registration Submitted')
            ->assertDontSee('success-modal');
    }

    public function test_event_login_keeps_its_existing_pending_approval_behavior(): void
    {
        $user = User::factory()->create([
            'email' => 'event-pending-'.Str::lower(Str::random(8)).'@example.test',
            'password' => 'secret123',
        ]);
        Participant::factory()->forUser($user)->create(['is_approved' => false]);

        $this->get(route('login'))->assertOk();
        $this->post(route('login'), [
            '_token' => $this->csrfToken(),
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('auth_notice_title', 'Registration Submitted')
            ->assertSessionHas('pending_review_message');

        $this->assertGuest();
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Registration Submitted')
            ->assertSee('pendingReviewModal')
            ->assertSee('SmartSeat')
            ->assertDontSee('igisummit.com');
    }

    public function test_train_registration_uses_the_existing_registration_post_route(): void
    {
        $registrationPage = $this->get(route('frontend.trains.register'));
        $registrationPage
            ->assertOk()
            ->assertSee('action="'.route('register').'"', false)
            ->assertSee('name="company_name"', false)
            ->assertSee('name="captcha"', false)
            ->assertSee('sign in and book as soon as registration is complete')
            ->assertSee('name="train_registration_token"', false)
            ->assertDontSee('EVENT')
            ->assertDontSee('conference')
            ->assertDontSee('Registration Submitted');
        $this->assertStringNotContainsString(route('train-owner.login'), $registrationPage->getContent());
    }

    public function test_train_registration_enables_only_train_access_and_can_reach_booking_routes(): void
    {
        $this->get(route('frontend.trains.register'))->assertOk();
        $registrationToken = session('train_registration_token');
        $captcha = session('register_captcha_answer');
        $email = 'train-new-'.Str::lower(Str::random(8)).'@example.test';

        $this->post(route('register'), [
            '_token' => $this->csrfToken(),
            'name' => 'Train User',
            'company_name' => 'SmartSeat Test',
            'email' => $email,
            'mobile' => '9000000000',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'captcha' => $captcha,
            'train_registration_token' => $registrationToken,
        ])->assertRedirect(route('frontend.trains.login'))
            ->assertSessionHas('train_auth_notice', 'Your account is ready. Sign in to continue with your train booking.');

        $user = User::query()->where('email', $email)->firstOrFail();
        $participant = Participant::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertTrue($participant->train_access_enabled);
        $this->assertFalse($participant->is_approved);
        $this->assertFalse((bool) $user->verification_status);

        $this->get(route('frontend.trains.login'))->assertOk();
        $this->post(route('login'), [
            '_token' => $this->csrfToken(),
            'email' => $email,
            'password' => 'secret123',
        ])->assertRedirect(route('frontend.trains.search'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('frontend.trains.bookings'))->assertOk();

        $trip = \App\Models\TrainTrip::factory()->create();
        $this->from(route('frontend.trains.search'))
            ->post(route('frontend.trains.book', $trip), ['_token' => $this->csrfToken()])
            ->assertRedirect(route('frontend.trains.search'))
            ->assertSessionHasErrors('inventory_ids');

    }

    public function test_event_registration_form_clears_train_origin_and_pending_event_users_remain_blocked(): void
    {
        $this->get(route('frontend.trains.register'))->assertOk();
        $this->assertIsString(session('train_registration_token'));

        $this->get(route('register'))->assertOk();
        $this->assertNull(session('train_registration_token'));

        $user = User::factory()->create([
            'email' => 'event-pending-'.Str::lower(Str::random(8)).'@example.test',
            'password' => 'secret123',
        ]);
        Participant::factory()->forUser($user)->create([
            'is_approved' => false,
            'train_access_enabled' => false,
        ]);

        $this->actingAs($user)->get(route('frontend.trains.bookings'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->get(route('login'))->assertOk();
        $this->post(route('login'), [
            '_token' => $this->csrfToken(),
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('auth_notice_title', 'Registration Submitted')
            ->assertSessionHas('pending_review_message')
            ->assertSessionMissing('train_auth_flow');

        $this->assertGuest();
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Registration Submitted')
            ->assertSee('pendingReviewModal')
            ->assertSee('SmartSeat')
            ->assertDontSee('igisummit.com');
    }

    private function station(string $label): Station
    {
        return Station::query()->create([
            'code' => 'T'.Str::upper(Str::random(7)),
            'name' => 'Test '.$label,
            'city' => 'Test City',
            'state' => 'Test State',
            'is_active' => true,
        ]);
    }

    private function csrfToken(): string
    {
        return (string) session('_token');
    }
}
