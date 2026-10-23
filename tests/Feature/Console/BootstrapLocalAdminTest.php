<?php

namespace Tests\Feature\Console;

use App\Models\Permission;
use App\Models\Participant;
use App\Models\Role;
use App\Models\User;
use App\Services\LocalAdminBootstrapGuard;
use App\Services\LocalAdminBootstrapService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class BootstrapLocalAdminTest extends TestCase
{
    private $connection;

    private bool $transactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (
            config('database.default') !== 'mysql'
            || config('database.connections.mysql.database') !== 'smartseat_train_test'
        ) {
            $this->markTestSkipped('Requires the separately prepared smartseat_train_test database.');
        }

        $this->connection = DB::connection('mysql');
        $activeDatabase = $this->connection->selectOne('SELECT DATABASE() AS active_database')->active_database ?? null;
        if ($activeDatabase !== 'smartseat_train_test') {
            $this->markTestSkipped('The active MySQL database is not smartseat_train_test.');
        }

        foreach (['roles', 'permissions', 'permission_role', 'role_user', 'users', 'participants'] as $table) {
            if (! Schema::connection('mysql')->hasTable($table)) {
                $this->markTestSkipped('The isolated test database does not contain the required application schema.');
            }
        }

        $this->connection->beginTransaction();
        $this->transactionStarted = true;
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted && $this->connection?->transactionLevel() > 0) {
            $this->connection->rollBack();
        }

        parent::tearDown();
    }

    public function test_command_refuses_non_local_environment_before_database_access(): void
    {
        DB::shouldReceive('connection')->never();

        $this->artisan('smartseat:bootstrap-local-admin')
            ->expectsOutputToContain('APP_ENV is local')
            ->assertExitCode(1);
    }

    public function test_command_refuses_when_actual_database_is_not_smartseat_dev(): void
    {
        $this->app->instance('env', 'local');
        config(['database.connections.mysql.database' => 'smartseat_dev']);

        $this->artisan('smartseat:bootstrap-local-admin')
            ->expectsOutputToContain('active connection is not attached to smartseat_dev')
            ->assertExitCode(1);
    }

    public function test_provisioning_rerun_does_not_duplicate_or_reset_the_administrator(): void
    {
        $this->prepareExpectedPermissionState();
        $this->allowServiceAccessToIsolatedTestDatabase();

        $service = app(LocalAdminBootstrapService::class);
        $email = 'bootstrap-repeat-' . bin2hex(random_bytes(5)) . '@example.test';
        $password = 'First-test-password-123';

        $this->assertTrue($service->provision('Local Test Administrator', $email, $password));

        $user = User::query()->where('email', $email)->firstOrFail();
        $hashBeforeRepeat = $user->password;

        $this->assertFalse($service->provision('Local Test Administrator', $email, 'Different-test-password-456'));

        $user->refresh();
        $this->assertSame($hashBeforeRepeat, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertSame([1], $user->roles()->orderBy('roles.id')->pluck('roles.id')->map(fn ($id) => (int) $id)->all());
        $this->assertDatabaseMissing('participants', ['user_id' => $user->id]);
        $this->assertSame(1, User::query()->where('email', $email)->count());
        $this->assertSame(
            collect(LocalAdminBootstrapService::REQUIRED_PERMISSION_TITLES)->sort()->values()->all(),
            Role::query()->findOrFail(1)->permissions()->orderBy('title')->pluck('title')->all()
        );
    }

    public function test_existing_pending_participant_account_is_never_promoted_or_approved(): void
    {
        $this->prepareExpectedPermissionState();
        $this->allowServiceAccessToIsolatedTestDatabase();

        $email = 'bootstrap-protected-' . bin2hex(random_bytes(5)) . '@example.test';
        $user = User::create([
            'name' => 'Existing Pending Account',
            'email' => $email,
            'password' => Hash::make('Unrelated-test-password-123'),
        ]);
        $originalPasswordHash = $user->password;
        $participant = Participant::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_approved' => false,
            'approved_at' => null,
        ]);

        try {
            app(LocalAdminBootstrapService::class)->provision(
                'Existing Pending Account',
                $email,
                'New-admin-password-456'
            );
            $this->fail('An existing participant account must not be promoted to administrator.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('not this bootstrap administrator', $exception->getMessage());
        }

        $user->refresh();
        $this->assertSame([], $user->roles()->pluck('roles.id')->all());
        $this->assertSame($originalPasswordHash, $user->password);
        $this->assertTrue(Hash::check('Unrelated-test-password-123', $user->password));
        $this->assertFalse((bool) $participant->fresh()->is_approved);
        $this->assertNull($participant->fresh()->approved_at);
    }

    public function test_unexpected_admin_role_state_aborts_before_writes(): void
    {
        $this->prepareExpectedPermissionState();
        $this->allowServiceAccessToIsolatedTestDatabase();

        $this->connection->table('roles')->insert([
            'id' => 1,
            'title' => 'Legacy role at reserved ID',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        $email = 'bootstrap-role-conflict-' . bin2hex(random_bytes(5)) . '@example.test';

        try {
            app(LocalAdminBootstrapService::class)->provision('Conflict Test Admin', $email, 'A-long-test-password-123');
            $this->fail('An unexpected reserved role state must abort.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('unexpected state', $exception->getMessage());
        }

        $this->assertSame('Legacy role at reserved ID', $this->connection->table('roles')->where('id', 1)->value('title'));
        $this->assertSame(0, User::query()->where('email', $email)->count());
        $this->assertSame(0, $this->connection->table('permission_role')->where('role_id', 1)->count());
    }

    public function test_unexpected_admin_permission_mapping_aborts_without_repair(): void
    {
        $this->prepareExpectedPermissionState();
        $this->allowServiceAccessToIsolatedTestDatabase();

        $permissionIds = Permission::query()
            ->whereIn('title', LocalAdminBootstrapService::REQUIRED_PERMISSION_TITLES)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->connection->table('roles')->insert([
            'id' => 1,
            'title' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        $this->connection->table('permission_role')->insert([
            'role_id' => 1,
            'permission_id' => $permissionIds[0],
        ]);
        $email = 'bootstrap-permission-conflict-' . bin2hex(random_bytes(5)) . '@example.test';

        try {
            app(LocalAdminBootstrapService::class)->provision('Conflict Test Admin', $email, 'A-long-test-password-123');
            $this->fail('An unexpected permission mapping must abort.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('does not have exactly', $exception->getMessage());
        }

        $this->assertSame([$permissionIds[0]], $this->connection->table('permission_role')->where('role_id', 1)->pluck('permission_id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame(0, User::query()->where('email', $email)->count());
    }

    private function prepareExpectedPermissionState(): void
    {
        $requiredTitles = LocalAdminBootstrapService::REQUIRED_PERMISSION_TITLES;
        $permissionIds = Permission::withTrashed()->whereIn('title', $requiredTitles)->pluck('id');

        $this->connection->table('role_user')->where('role_id', 1)->delete();
        $this->connection->table('permission_role')->where('role_id', 1)->delete();
        if ($permissionIds->isNotEmpty()) {
            $this->connection->table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        }
        $this->connection->table('roles')->where('id', 1)->orWhere('title', 'Admin')->delete();
        $this->connection->table('permissions')->whereIn('title', $requiredTitles)->delete();

        foreach ($requiredTitles as $title) {
            $this->connection->table('permissions')->insert([
                'title' => $title,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);
        }
    }

    private function allowServiceAccessToIsolatedTestDatabase(): void
    {
        $guard = Mockery::mock(LocalAdminBootstrapGuard::class);
        $guard->shouldReceive('assertAllowed')->atLeast()->once();
        $this->app->instance(LocalAdminBootstrapGuard::class, $guard);
    }
}
