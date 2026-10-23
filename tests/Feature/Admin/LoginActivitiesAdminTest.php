<?php

namespace Tests\Feature\Admin;

use App\Models\LoginActivity;
use App\Models\Participant;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginActivitiesAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorized_user_can_view_login_activities_index_and_show_pages(): void
    {
        $admin = User::factory()->create();
        $role = Role::create(['title' => 'Login Activity Admin']);
        Permission::insertOrIgnore([
            ['title' => 'login_activity_access'],
            ['title' => 'login_activity_show'],
        ]);

        $permissionIds = Permission::whereIn('title', ['login_activity_access', 'login_activity_show'])
            ->pluck('id');

        $role->permissions()->sync($permissionIds);
        $admin->roles()->sync([$role->id]);

        $attemptUser = User::factory()->create([
            'name' => 'Attempt User',
            'email' => 'attempt@example.com',
            'company_name' => 'Attempt Company',
        ]);

        $participant = Participant::factory()->forUser($attemptUser)->create();

        $loginActivity = LoginActivity::create([
            'user_id' => $attemptUser->id,
            'participant_id' => $participant->id,
            'email' => $attemptUser->email,
            'status' => 'failed',
            'ip_address' => '198.51.100.10',
            'user_agent' => 'PHPUnit',
            'attempted_at' => now(),
        ]);

        Http::fake([
            'https://get.geojs.io/v1/ip/geo/198.51.100.10.json' => Http::response([
                'city' => 'Bangalore',
                'country' => 'India',
            ], 200),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.login-activities.index'))
            ->assertOk()
            ->assertSee('Login Attempts')
            ->assertSee($attemptUser->email)
            ->assertSee('Attempt Company')
            ->assertSee('Bangalore')
            ->assertSee('India')
            ->assertSee(now()->format('d-M-Y'));

        $this->actingAs($admin)
            ->get(route('admin.login-activities.show', $loginActivity))
            ->assertOk()
            ->assertSee('Login Attempt')
            ->assertSee('198.51.100.10')
            ->assertSee('Bangalore')
            ->assertSee('India');
    }

    public function test_authorized_user_can_delete_all_login_activities(): void
    {
        $admin = User::factory()->create();
        $role = Role::create(['title' => 'Login Activity Admin']);
        Permission::insertOrIgnore([
            ['title' => 'login_activity_access'],
        ]);

        $permissionIds = Permission::whereIn('title', ['login_activity_access'])->pluck('id');
        $role->permissions()->sync($permissionIds);
        $admin->roles()->sync([$role->id]);

        LoginActivity::create([
            'email' => 'first@example.com',
            'status' => 'success',
            'attempted_at' => now(),
        ]);

        LoginActivity::create([
            'email' => 'second@example.com',
            'status' => 'failed',
            'attempted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.login-activities.clearAll'))
            ->assertRedirect(route('admin.login-activities.index'));

        $this->assertDatabaseCount('login_activities', 0);
    }

    public function test_user_without_permission_cannot_view_login_activities_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.login-activities.index'))
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_delete_all_login_activities(): void
    {
        $user = User::factory()->create();

        LoginActivity::create([
            'email' => 'keep@example.com',
            'status' => 'success',
            'attempted_at' => now(),
        ]);

        $this->actingAs($user)
            ->delete(route('admin.login-activities.clearAll'))
            ->assertForbidden();

        $this->assertDatabaseHas('login_activities', [
            'email' => 'keep@example.com',
            'status' => 'success',
        ]);
    }
}
