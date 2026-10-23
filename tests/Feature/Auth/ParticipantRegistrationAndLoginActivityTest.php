<?php

namespace Tests\Feature\Auth;

use App\Models\Participant;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ParticipantRegistrationAndLoginActivityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_registration_creates_linked_participant_and_hashed_password(): void
    {
        Role::query()->firstOrCreate(['title' => 'User']);

        $payload = [
            '_token' => 'auth-test-csrf-token',
            'name' => 'Participant User',
            'company_name' => 'Participant Company',
            'email' => 'participant@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'mobile' => '9876543210',
            'captcha' => '5',
        ];

        $this->withSession([
            '_token' => 'auth-test-csrf-token',
            'register_captcha_answer' => '5',
            'register_captcha_question' => '2 + 3 = ?',
        ]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.77',
            'HTTP_USER_AGENT' => 'PHPUnit Register Browser',
        ])->post(route('register'), $payload)
            ->assertRedirect(route('login'));

        $user = User::where('email', $payload['email'])->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check($payload['password'], $user->password));
        $this->assertNotSame($payload['password'], $user->password);
        $this->assertTrue($user->roles()->where('title', 'User')->exists());

        $this->assertDatabaseHas('participants', [
            'user_id' => $user->id,
            'email' => $payload['email'],
            'mobile' => $payload['mobile'],
            'is_approved' => false,
            'train_access_enabled' => false,
        ]);

        $participant = Participant::query()->where('user_id', $user->id)->first();

        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'participant_id' => $participant?->id,
            'email' => $payload['email'],
            'status' => 'registered',
            'ip_address' => '203.0.113.77',
            'user_agent' => 'PHPUnit Register Browser',
        ]);
    }

    public function test_successful_login_is_logged_with_ip_and_updates_participant_last_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Login Success',
            'email' => 'success@example.com',
            'password' => 'secret123',
        ]);

        $participant = Participant::factory()->forUser($user)->create([
            'mobile' => '1111111111',
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        $this->get(route('login'))->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
            ->post(route('login'), [
                '_token' => session('_token'),
                'email' => $user->email,
                'password' => 'secret123',
            ])
            ->assertRedirect(route('frontend.home'));

        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'participant_id' => null,
            'email' => $user->email,
            'status' => 'success',
            'ip_address' => '203.0.113.9',
        ]);

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'last_login_ip' => '203.0.113.9',
        ]);
    }

    public function test_failed_login_is_logged_with_ip(): void
    {
        $user = User::factory()->create([
            'name' => 'Login Failed',
            'email' => 'failed@example.com',
            'password' => 'secret123',
        ]);

        $participant = Participant::factory()->forUser($user)->create([
            'mobile' => '2222222222',
        ]);

        $this->get(route('login'))->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.12'])
            ->post(route('login'), [
                '_token' => session('_token'),
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'participant_id' => null,
            'email' => $user->email,
            'status' => 'failed',
            'ip_address' => '198.51.100.12',
        ]);
    }

    public function test_pending_participant_is_blocked_from_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => 'secret123',
        ]);

        $participant = Participant::factory()->forUser($user)->create([
            'mobile' => '3333333333',
            'is_approved' => false,
        ]);

        $this->get(route('login'))->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.30'])
            ->post(route('login'), [
                '_token' => session('_token'),
                'email' => $user->email,
                'password' => 'secret123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('pending_review_message');

        $this->assertGuest();

        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'participant_id' => null,
            'email' => $user->email,
            'status' => 'pending',
            'ip_address' => '198.51.100.30',
        ]);
    }

    public function test_revoked_participant_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create([
            'email' => 'revoked@example.com',
        ]);

        $participant = Participant::factory()->forUser($user)->create([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        $this->actingAs($user);

        $participant->forceFill([
            'is_approved' => false,
            'approved_at' => null,
        ])->save();

        $user->forceFill([
            'verification_status' => false,
        ])->save();

        $this->get(route('frontend.home'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_notice_title', 'Access Revoked')
            ->assertSessionHas('pending_review_message');

        $this->assertGuest();
    }

    public function test_admin_login_sends_admin_notification_email(): void
    {
        DB::table('roles')->updateOrInsert(
            ['id' => 1],
            [
                'title' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]
        );
        $adminRole = Role::query()->findOrFail(1);

        $admin = User::factory()->create([
            'name' => 'Main Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);
        $admin->roles()->sync([$adminRole->id]);

        $recipient = User::factory()->create([
            'name' => 'Recipient Admin',
            'email' => 'recipient@example.com',
        ]);
        $recipient->roles()->sync([$adminRole->id]);

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->withArgs(function (string $templateKey, array $payload) use ($admin) {
                return $templateKey === 'admin_login'
                    && $payload['admin_name'] === $admin->name
                    && $payload['admin_email'] === $admin->email
                    && $payload['login_ip'] === '203.0.113.50'
                    && $payload['user_agent'] === 'PHPUnit Admin Browser'
                    && str_contains($payload['login_at'], '-');
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $this->get(route('login'))->assertOk();
        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.50',
            'HTTP_USER_AGENT' => 'PHPUnit Admin Browser',
        ])->post(route('login'), [
            '_token' => session('_token'),
            'email' => $admin->email,
            'password' => 'secret123',
        ])->assertRedirect(route('admin.home'));
    }
}
