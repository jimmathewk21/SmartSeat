<?php

namespace Tests\Feature\Auth;

use App\Models\Participant;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class ProfileUpdateNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_profile_update_notifies_admin_when_company_name_changes(): void
    {
        $user = User::factory()->create([
            'name' => 'Original User',
            'email' => 'original@example.com',
            'company_name' => 'Original Company',
            'mobile' => '9999999999',
        ]);

        Participant::factory()->forUser($user)->create([
            'name' => 'Original User',
            'email' => 'original@example.com',
            'mobile' => '9999999999',
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')
            ->once()
            ->withArgs(function (string $templateKey, array $payload) {
                return $templateKey === 'user_profile_updated'
                    && $payload['name'] === 'Original User'
                    && $payload['email'] === 'original@example.com'
                    && $payload['company_name'] === 'Updated Company'
                    && $payload['changed_fields'] === ['Company Name']
                    && str_contains($payload['updated_at'], '-');
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $this->actingAs($user)
            ->post(route('profile.password.updateProfile'), [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
                'mobile' => '9999999999',
                'company_name' => 'Updated Company',
            ])
            ->assertRedirect(route('profile.password.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Original User',
            'email' => 'original@example.com',
            'company_name' => 'Updated Company',
        ]);

        $this->assertDatabaseHas('participants', [
            'user_id' => $user->id,
            'name' => 'Original User',
            'email' => 'original@example.com',
            'mobile' => '9999999999',
        ]);
    }

    public function test_profile_update_does_not_notify_admin_when_relevant_fields_do_not_change(): void
    {
        $user = User::factory()->create([
            'name' => 'Same User',
            'email' => 'same@example.com',
            'company_name' => 'Same Company',
            'mobile' => '8888888888',
        ]);

        Participant::factory()->forUser($user)->create([
            'name' => 'Same User',
            'email' => 'same@example.com',
            'mobile' => '8888888888',
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        $mailer = Mockery::mock(AdminNotificationMailer::class);
        $mailer->shouldReceive('send')->never();
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $this->actingAs($user)
            ->post(route('profile.password.updateProfile'), [
                'name' => 'Same User',
                'email' => 'same@example.com',
                'mobile' => '7777777777',
                'company_name' => 'Same Company',
            ])
            ->assertRedirect(route('profile.password.edit'));

        $this->assertDatabaseHas('participants', [
            'user_id' => $user->id,
            'mobile' => '8888888888',
        ]);
    }
}
