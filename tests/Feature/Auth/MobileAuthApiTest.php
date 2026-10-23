<?php

namespace Tests\Feature\Auth;

use App\Models\LoginActivity;
use App\Models\Participant;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class MobileAuthApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_returns_token_for_approved_participant(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        Participant::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => '1234567890',
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'participant'],
            ]);

        $this->assertDatabaseHas('login_activities', [
            'email' => 'jane@example.com',
            'status' => 'success',
        ]);
    }

    public function test_login_rejects_pending_participant(): void
    {
        $user = User::factory()->create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        Participant::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => '1234567890',
            'is_approved' => false,
            'approved_at' => null,
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseHas('login_activities', [
            'email' => 'pending@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_register_creates_pending_user_and_participant(): void
    {
        Role::query()->firstOrCreate(['title' => 'User'], ['title' => 'User']);

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')->once();
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $response = $this->postJson(route('api.v1.auth.register'), [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'new@example.com',
            'mobile' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJson([
                'notice_title' => 'Registration Submitted',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'name' => 'New User',
        ]);

        $this->assertDatabaseHas('participants', [
            'email' => 'new@example.com',
            'is_approved' => 0,
        ]);

        $this->assertDatabaseHas('login_activities', [
            'email' => 'new@example.com',
            'status' => 'registered',
        ]);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-app');

        $this->withToken($token->plainTextToken)
            ->postJson(route('api.v1.auth.logout'))
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_forgot_password_returns_success_message(): void
    {
        Notification::fake();

        User::factory()->create([
            'email' => 'forgot@example.com',
        ]);

        $this->postJson(route('api.v1.auth.forgot-password'), [
            'email' => 'forgot@example.com',
        ])->assertOk();
    }
}
