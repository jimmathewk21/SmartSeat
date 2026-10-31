<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DatabaseTestCase;

class AuthenticationTest extends DatabaseTestCase
{
    private function registration(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Asha Rao',
            'email' => 'asha@example.test',
            'password' => 'JourneyAhead!42',
            'password_confirmation' => 'JourneyAhead!42',
        ], $overrides);
    }

    public function test_registration_always_assigns_passenger_and_hashes_password(): void
    {
        $this->post(route('register.store'), $this->registration([
            'email' => 'ASHA@EXAMPLE.TEST',
            'role' => 'administrator',
            'is_admin' => true,
        ]))->assertRedirect(route('dashboard'));

        $this->assertAuthenticated('web');
        $this->assertDatabaseHas('users', ['email' => 'asha@example.test', 'role' => 'passenger']);
        $this->assertDatabaseMissing('users', ['role' => 'administrator']);
        $user = User::where('email', 'asha@example.test')->firstOrFail();
        $this->assertSame(UserRole::Passenger, $user->role);
        $this->assertNotSame('JourneyAhead!42', $user->password);
        $this->assertTrue(Hash::check('JourneyAhead!42', $user->password));
    }

    #[DataProvider('invalidRegistrations')]
    public function test_registration_rejects_invalid_input(array $overrides, string $field): void
    {
        $this->from(route('register'))->post(route('register.store'), $this->registration($overrides))
            ->assertRedirect(route('register'))->assertSessionHasErrors($field);
        $this->assertGuest('web');
        $this->assertDatabaseCount('users', 0);
    }

    public static function invalidRegistrations(): array
    {
        return [
            'missing name' => [['name' => ''], 'name'],
            'name array' => [['name' => []], 'name'],
            'invalid email' => [['email' => 'not-email'], 'email'],
            'email array' => [['email' => []], 'email'],
            'missing password' => [['password' => ''], 'password'],
            'short password' => [['password' => 'Aa!1', 'password_confirmation' => 'Aa!1'], 'password'],
            'no uppercase' => [['password' => 'journeyahead!42', 'password_confirmation' => 'journeyahead!42'], 'password'],
            'no lowercase' => [['password' => 'JOURNEYAHEAD!42', 'password_confirmation' => 'JOURNEYAHEAD!42'], 'password'],
            'no number' => [['password' => 'JourneyAhead!!', 'password_confirmation' => 'JourneyAhead!!'], 'password'],
            'no symbol' => [['password' => 'JourneyAhead42', 'password_confirmation' => 'JourneyAhead42'], 'password'],
            'mismatch' => [['password_confirmation' => 'AnotherPassword!42'], 'password'],
            'too long' => [['password' => str_repeat('Ab1!', 19), 'password_confirmation' => str_repeat('Ab1!', 19)], 'password'],
            'too many bytes' => [['password' => str_repeat('é', 35).'Aa1!', 'password_confirmation' => str_repeat('é', 35).'Aa1!'], 'password'],
            'null byte' => [['password' => "JourneyAhead!42\0", 'password_confirmation' => "JourneyAhead!42\0"], 'password'],
        ];
    }

    public function test_duplicate_registration_cannot_overwrite_or_promote_an_account(): void
    {
        $user = User::factory()->create(['email' => 'asha@example.test']);
        $originalPassword = $user->password;
        $this->post(route('register.store'), $this->registration(['role' => 'administrator']))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'password' => $originalPassword, 'role' => 'passenger']);
    }

    public function test_login_regenerates_session_and_logout_invalidates_it(): void
    {
        $user = User::factory()->create(['password' => 'JourneyAhead!42']);
        $this->withSession(['old_session_marker' => 'present']);
        $beforeLogin = session()->getId();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'JourneyAhead!42'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertNotSame($beforeLogin, session()->getId());

        $beforeLogout = session()->getId();
        $token = session()->token();
        $this->post(route('logout'))->assertRedirect(route('home'))->assertSessionMissing('old_session_marker');
        $this->assertGuest('web');
        $this->assertNotSame($beforeLogout, session()->getId());
        $this->assertNotSame($token, session()->token());
    }

    public function test_login_rejects_missing_fields_and_incorrect_credentials(): void
    {
        $this->post(route('login.store'), [])->assertSessionHasErrors(['email', 'password']);
        $user = User::factory()->create();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('web');
    }

    public function test_login_rejects_array_input_instead_of_throwing(): void
    {
        $this->post(route('login.store'), ['email' => [], 'password' => []])
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_accepts_uppercase_email_and_rejects_invalid_email(): void
    {
        $this->post(route('login.store'), ['email' => 'invalid', 'password' => 'anything'])
            ->assertSessionHasErrors('email');
        $user = User::factory()->create(['email' => 'asha@example.test', 'password' => 'JourneyAhead!42']);
        $this->post(route('login.store'), ['email' => 'ASHA@EXAMPLE.TEST', 'password' => 'JourneyAhead!42'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_login_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson(route('login.store'), ['email' => 'absent@example.test', 'password' => 'wrong'])
                ->assertUnprocessable();
        }
        $this->postJson(route('login.store'), ['email' => 'absent@example.test', 'password' => 'wrong'])
            ->assertTooManyRequests();
    }

    public function test_password_confirmation_checks_the_password(): void
    {
        $user = User::factory()->create(['password' => 'JourneyAhead!42']);
        $this->actingAs($user, 'web')->get(route('password.confirm'))->assertOk()->assertSee('Current password');
        $this->post(route('password.confirm.store'), ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->assertNull(session('auth.password_confirmed_at'));
        $this->post(route('password.confirm.store'), ['password' => 'JourneyAhead!42'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('auth.password_confirmed_at');
    }

    public function test_role_is_not_mass_assignable(): void
    {
        $user = new User(['name' => 'Passenger', 'role' => 'administrator']);
        $this->assertFalse($user->isAdministrator());
        $this->assertSame(UserRole::Passenger, $user->role);
    }
}
