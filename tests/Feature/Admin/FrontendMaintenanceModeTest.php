<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\FrontendMaintenanceSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FrontendMaintenanceModeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_toggle_frontend_maintenance_mode(): void
    {
        $admin = $this->createDeveloperAdmin();

        $this->actingAs($admin)
            ->post(route('admin.maintenance.frontendMode'), [
                'enabled' => '1',
            ])
            ->assertRedirect();

        $this->assertTrue(FrontendMaintenanceSettings::enabled());

        $this->actingAs($admin)
            ->post(route('admin.maintenance.frontendMode'), [
                'enabled' => '0',
            ])
            ->assertRedirect();

        $this->assertFalse(FrontendMaintenanceSettings::enabled());
    }

    public function test_frontend_routes_show_under_construction_when_maintenance_is_enabled(): void
    {
        FrontendMaintenanceSettings::set([
            'enabled' => true,
        ]);

        $this->get(route('frontend.home'))
            ->assertStatus(503)
            ->assertSee('Website Maintenance');
    }

    public function test_admin_dashboard_stays_accessible_when_frontend_maintenance_is_enabled(): void
    {
        FrontendMaintenanceSettings::set([
            'enabled' => true,
        ]);

        $admin = $this->createDeveloperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.home'))
            ->assertOk();
    }

    private function createDeveloperAdmin(): User
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

        $role = Role::firstOrCreate(['title' => 'Developer Admin']);
        Permission::insertOrIgnore([
            ['title' => 'developer_options'],
        ]);

        $permissionIds = Permission::whereIn('title', ['developer_options'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $user = User::factory()->create();
        $user->roles()->sync([$adminRole->id, $role->id]);

        return $user;
    }
}
