<?php

namespace Tests\Feature\Train;

use App\Models\Permission;
use App\Models\Participant;
use App\Models\Train;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TrainOwnerAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private Role $trainOwnerRole;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDefaultConnection() !== 'mysql'
            || config('database.connections.mysql.database') !== 'smartseat_train_test'
            || DB::connection('mysql')->getDatabaseName() !== 'smartseat_train_test') {
            $this->markTestSkipped('Requires the verified smartseat_train_test database.');
        }

        if (! Schema::connection('mysql')->hasColumn('users', 'train_owner_approved_at')
            || ! Schema::connection('mysql')->hasColumn('trains', 'owner_user_id')) {
            $this->markTestSkipped('Requires the additive train-owner authorization migration on smartseat_train_test.');
        }

        $this->trainOwnerRole = $this->createRoleWithPermissions('Train Owner', [
            'train_management_access', 'train_management_edit',
        ]);
    }

    public function test_private_login_page_is_available_but_owner_panel_requires_approval(): void
    {
        $this->get(route('train-owner.login'))->assertOk()->assertSee('Train owner sign in');
        $this->get(route('train-owner.trains.index'))->assertRedirect(route('login'));

        $owner = User::factory()->create();
        $owner->roles()->attach($this->trainOwnerRole->id);

        $this->actingAs($owner)->get(route('train-owner.trains.index'))->assertForbidden();
    }

    public function test_approved_owner_can_use_private_login_without_event_approval(): void
    {
        $owner = User::factory()->create(['password' => 'owner-secret']);
        Participant::factory()->forUser($owner)->create([
            'is_approved' => false,
            'train_access_enabled' => false,
        ]);
        $owner->roles()->attach($this->trainOwnerRole->id);
        $owner->forceFill(['train_owner_approved_at' => now()])->save();

        $this->get(route('train-owner.login'))->assertOk();
        $this->post(route('train-owner.login.submit'), [
            '_token' => $this->csrfToken(),
            'email' => $owner->email,
            'password' => 'owner-secret',
        ])->assertRedirect(route('train-owner.trains.index'));

        $this->assertAuthenticatedAs($owner);
        $this->assertTrue(session('train_owner_session'));
        $this->get(route('train-owner.trains.index'))->assertOk();
        $this->get(route('frontend.trains.bookings'))->assertRedirect(route('train-owner.login'));
        $this->assertGuest();
    }

    public function test_passenger_role_and_admin_role_do_not_implicitly_grant_owner_access(): void
    {
        $passengerRole = Role::firstOrCreate(['title' => 'User']);
        $passenger = User::factory()->create();
        $passenger->roles()->attach($passengerRole->id);

        $this->actingAs($passenger)->get(route('train-owner.trains.index'))->assertForbidden();

        $adminRole = $this->createRoleWithPermissions('Admin');

        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);
        $admin->forceFill(['train_owner_approved_at' => now()])->save();

        $this->actingAs($admin)->get(route('train-owner.trains.index'))->assertForbidden();
    }

    public function test_approved_owner_can_only_edit_assigned_trains(): void
    {
        $owner = User::factory()->create();
        $owner->roles()->attach($this->trainOwnerRole->id);
        $owner->forceFill(['train_owner_approved_at' => now()])->save();
        $ownTrain = Train::factory()->create();
        $ownTrain->forceFill(['owner_user_id' => $owner->id])->save();
        $otherTrain = Train::factory()->create();

        $this->actingAs($owner)->get(route('train-owner.trains.index'))
            ->assertOk()->assertSee($ownTrain->name)->assertDontSee($otherTrain->name);
        $this->get(route('train-owner.trains.edit', $otherTrain))->assertNotFound();
        $this->put(route('train-owner.trains.update', $otherTrain), [
            '_token' => $this->csrfToken(),
            'code' => 'CROSS-OWNER', 'name' => 'Unauthorized edit', 'is_active' => 0,
        ])->assertNotFound();

        $this->put(route('train-owner.trains.update', $ownTrain), [
            '_token' => $this->csrfToken(),
            'code' => $ownTrain->code, 'name' => 'Authorized update', 'is_active' => 0,
        ])->assertRedirect(route('train-owner.trains.index'));
        $this->assertDatabaseHas('trains', ['id' => $ownTrain->id, 'name' => 'Authorized update', 'is_active' => false]);
        $this->assertDatabaseMissing('trains', ['id' => $otherTrain->id, 'name' => 'Unauthorized edit']);
    }

    public function test_edit_routes_require_the_edit_permission_in_addition_to_owner_access(): void
    {
        $ownerRole = $this->createRoleWithPermissions('Train Owner', ['train_management_access']);
        $owner = User::factory()->create();
        $owner->roles()->attach($ownerRole->id);
        $owner->forceFill(['train_owner_approved_at' => now()])->save();
        $train = Train::factory()->create();
        $train->forceFill(['owner_user_id' => $owner->id])->save();

        $this->actingAs($owner)->get(route('train-owner.trains.index'))->assertOk();
        $this->get(route('train-owner.trains.edit', $train))->assertForbidden();
        $this->put(route('train-owner.trains.update', $train), [
            '_token' => $this->csrfToken(),
            'code' => $train->code, 'name' => 'Must remain unchanged', 'is_active' => 1,
        ])->assertForbidden();
        $this->assertDatabaseHas('trains', ['id' => $train->id, 'name' => $train->name]);
    }

    public function test_admin_user_editor_can_approve_owner_and_assign_a_train(): void
    {
        $adminRole = $this->createRoleWithPermissions('Admin', ['user_edit']);

        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);
        $owner = User::factory()->create();
        Participant::factory()->forUser($owner)->create(['is_approved' => false]);
        $train = Train::factory()->create();
        $ownerRoleId = $this->trainOwnerRole->id;

        $this->actingAs($admin)->get(route('admin.users.edit', $owner))->assertOk();
        $this->put(route('admin.users.update', $owner), [
            '_token' => $this->csrfToken(),
            'name' => $owner->name,
            'email' => $owner->email,
            'mobile' => $owner->mobile,
            'company_name' => $owner->company_name,
            'roles' => [$ownerRoleId],
            'approve_train_owner' => '1',
            'train_ids' => [$train->id],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertNotNull($owner->fresh()->train_owner_approved_at);
        $this->assertDatabaseHas('trains', ['id' => $train->id, 'owner_user_id' => $owner->id]);
    }

    private function createRoleWithPermissions(string $title, array $permissionTitles = []): Role
    {
        $role = Role::query()->create(['title' => $title]);
        $permissionIds = collect($permissionTitles)
            ->map(fn (string $permissionTitle): int => Permission::query()
                ->firstOrCreate(['title' => $permissionTitle])
                ->id)
            ->all();

        $role->permissions()->sync($permissionIds);

        return $role;
    }

    private function csrfToken(): string
    {
        return (string) session('_token');
    }
}
