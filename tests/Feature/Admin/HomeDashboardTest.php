<?php

namespace Tests\Feature\Admin;

use App\Models\LoginActivity;
use App\Models\Participant;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_home_loads_with_latest_login_activity_eager_loaded(): void
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

        Permission::query()->firstOrCreate(['title' => 'user_access']);
        $permissionIds = Permission::query()
            ->whereIn('title', ['user_access'])
            ->pluck('id');
        $adminRole->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$adminRole->id]);

        $user = User::factory()->create();
        $participant = Participant::factory()->forUser($user)->create([
            'is_approved' => true,
        ]);

        LoginActivity::query()->create([
            'user_id' => $user->id,
            'participant_id' => $participant->id,
            'email' => $user->email,
            'status' => 'success',
            'ip_address' => '203.0.113.10',
            'user_agent' => 'PHPUnit',
            'attempted_at' => now()->subMinute(),
        ]);

        LoginActivity::query()->create([
            'user_id' => $user->id,
            'participant_id' => $participant->id,
            'email' => $user->email,
            'status' => 'success',
            'ip_address' => '203.0.113.11',
            'user_agent' => 'PHPUnit',
            'attempted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.home'));

        $response->assertOk();
    }
}
