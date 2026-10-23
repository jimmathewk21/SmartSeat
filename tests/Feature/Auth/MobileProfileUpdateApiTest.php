<?php

namespace Tests\Feature\Auth;

use App\Models\Participant;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class MobileProfileUpdateApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_profile_update_changes_the_current_user_and_participant(): void
    {
        $user = User::factory()->create([
            'name' => 'Original User',
            'email' => 'original@example.com',
            'company_name' => 'Original Company',
            'mobile' => '9999999999',
            'profile_picture' => null,
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
                    && $payload['company_name'] === 'Updated Company'
                    && $payload['changed_fields'] === ['Company Name'];
            });
        $this->app->instance(AdminNotificationMailer::class, $mailer);

        $token = $user->createToken('mobile-app')->plainTextToken;

        $response = $this->withToken($token)->putJson(route('api.v1.auth.profile'), [
            'name' => 'Updated User',
            'mobile' => '7777777777',
            'company_name' => 'Updated Company',
            'profile_picture' => 'https://example.com/profile.jpg',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.name', 'Updated User')
            ->assertJsonPath('user.mobile', '7777777777')
            ->assertJsonPath('user.company_name', 'Updated Company')
            ->assertJsonPath('user.profile_picture', 'https://example.com/profile.jpg')
            ->assertJsonPath('user.profile_photo_url', 'https://example.com/profile.jpg');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'mobile' => '7777777777',
            'company_name' => 'Updated Company',
            'profile_picture' => 'https://example.com/profile.jpg',
        ]);

        $this->assertDatabaseHas('participants', [
            'user_id' => $user->id,
            'name' => 'Updated User',
            'email' => 'original@example.com',
            'mobile' => '7777777777',
        ]);
    }

    public function test_profile_update_requires_authentication(): void
    {
        $this->putJson(route('api.v1.auth.profile'), [
            'name' => 'Unauthorized User',
        ])->assertUnauthorized();
    }
}
