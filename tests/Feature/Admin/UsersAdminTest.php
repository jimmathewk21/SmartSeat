<?php

namespace Tests\Feature\Admin;

use App\Mail\HtmlBodyMail;
use App\Mail\PlainTextMail;
use App\Models\Participant;
use App\Models\LoginActivity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UsersAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_created_user_gets_default_user_role_when_no_roles_selected(): void
    {
        Role::query()->firstOrCreate(['title' => 'User']);
        $role = Role::create(['title' => 'User Admin']);
        Permission::insertOrIgnore([
            ['title' => 'user_access'],
            ['title' => 'user_create'],
        ]);

        $permissionIds = Permission::whereIn('title', ['user_access', 'user_create'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $payload = [
            'name' => 'Default Role User',
            'mobile' => '9999999999',
            'company_name' => 'Default Role Company',
            'email' => 'default-role@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $payload)
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', $payload['email'])->firstOrFail();

        $this->assertTrue($user->roles()->where('title', 'User')->exists());
        $this->assertSame(
            1,
            $user->roles()->where('title', 'User')->count()
        );
    }

    public function test_admin_created_user_keeps_explicit_roles_without_duplicate_default_user_role(): void
    {
        $userRole = Role::query()->firstOrCreate(['title' => 'User']);
        $secondaryRole = Role::create(['title' => 'Event Editor']);
        $role = Role::create(['title' => 'User Admin']);
        Permission::insertOrIgnore([
            ['title' => 'user_access'],
            ['title' => 'user_create'],
        ]);

        $permissionIds = Permission::whereIn('title', ['user_access', 'user_create'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $payload = [
            'name' => 'Explicit Role User',
            'mobile' => '8888888888',
            'company_name' => 'Explicit Role Company',
            'email' => 'explicit-role@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'roles' => [$userRole->id, $secondaryRole->id],
        ];

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $payload)
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', $payload['email'])->firstOrFail();

        $this->assertEqualsCanonicalizing(
            ['User', 'Event Editor'],
            $user->roles()->pluck('title')->all()
        );
    }

    public function test_users_export_includes_mobile_number_column_and_value(): void
    {
        $role = Role::create(['title' => 'User Admin']);
        Permission::insertOrIgnore([
            ['title' => 'user_access'],
        ]);

        $permissionIds = Permission::whereIn('title', ['user_access'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $user = User::factory()->create([
            'name' => 'Export User',
            'email' => 'export@example.com',
            'mobile' => '9876543210',
            'company_name' => 'Export Co',
        ]);

        Participant::factory()->forUser($user)->create([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.export'));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Mobile', $csv);
        $this->assertStringContainsString('9876543210', $csv);
    }

    public function test_users_index_displays_city_and_country_for_latest_login_ip(): void
    {
        $role = Role::create(['title' => 'User Admin']);
        Permission::insertOrIgnore([
            ['title' => 'user_access'],
        ]);

        $permissionIds = Permission::whereIn('title', ['user_access'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $user = User::factory()->create([
            'name' => 'Geo User',
            'email' => 'geo@example.com',
            'company_name' => 'Geo Co',
        ]);

        LoginActivity::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => 'success',
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
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Geo User')
            ->assertSee('198.51.100.10')
            ->assertSee('Bangalore')
            ->assertSee('India');
    }

    public function test_admin_bulk_approval_queues_template_based_approval_email(): void
    {
        $role = Role::create(['title' => 'User Admin']);
        Permission::insertOrIgnore([
            ['title' => 'user_access'],
            ['title' => 'user_edit'],
        ]);

        $permissionIds = Permission::whereIn('title', ['user_access', 'user_edit'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $user = User::factory()->create([
            'name' => 'Template User',
            'email' => 'template-user@example.com',
            'mobile' => '9000000000',
            'company_name' => 'Template Co',
        ]);

        Participant::factory()->forUser($user)->create([
            'is_approved' => false,
        ]);

        Mail::fake();

        $this->actingAs($admin)
            ->post(route('admin.users.bulkApproval'), [
                'ids' => [$user->id],
                'status' => 'approved',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('participants', [
            'user_id' => $user->id,
            'is_approved' => true,
        ]);

        Mail::assertQueued(HtmlBodyMail::class, function (HtmlBodyMail $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });
        Mail::assertNotQueued(PlainTextMail::class);
    }

    public function test_create_user_page_preselects_default_user_role(): void
    {
        $role = Role::query()->firstOrCreate(['title' => 'User']);
        $adminRole = Role::query()->firstOrCreate(['title' => 'User Admin']);
        Permission::insertOrIgnore([
            ['title' => 'user_access'],
            ['title' => 'user_create'],
        ]);

        $permissionIds = Permission::whereIn('title', ['user_access', 'user_create'])->pluck('id');
        $adminRole->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$adminRole->id]);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('option value="' . $role->id . '" selected', false)
            ->assertSee($role->title);
    }

    public function test_users_index_formats_date_and_time_human_readably(): void
    {
        $role = Role::query()->firstOrCreate(['title' => 'User Admin']);
        Permission::insertOrIgnore([
            ['title' => 'user_access'],
        ]);

        $permissionIds = Permission::whereIn('title', ['user_access'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $user = User::factory()->create([
            'name' => 'Date Format User',
            'email' => 'date-format@example.com',
            'created_at' => '2006-04-21 14:23:34',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('21st April 2006 at 2:23 PM', false);
    }

    public function test_admin_users_index_shows_top_action_buttons_for_admin_role(): void
    {
        $adminRole = Role::query()->firstOrCreate(['title' => 'Admin']);
        Permission::insertOrIgnore([
            ['title' => 'user_management_access'],
            ['title' => 'user_access'],
            ['title' => 'user_create'],
            ['title' => 'user_edit'],
            ['title' => 'user_delete'],
        ]);

        $permissionIds = Permission::whereIn('title', [
            'user_management_access',
            'user_access',
            'user_create',
            'user_edit',
            'user_delete',
        ])->pluck('id');

        $adminRole->permissions()->syncWithoutDetaching($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$adminRole->id]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Add User', false)
            ->assertSee('Export Excel', false)
            ->assertSee('Delete', false)
            ->assertSee('Approve', false)
            ->assertSee('Block', false);
    }
}
