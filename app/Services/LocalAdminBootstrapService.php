<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class LocalAdminBootstrapService
{
    public const REQUIRED_PERMISSION_TITLES = [
        'participant_access',
        'participant_show',
        'participant_approve',
    ];

    private const ADMIN_ROLE_ID = 1;

    public function __construct(private readonly LocalAdminBootstrapGuard $guard)
    {
    }

    /** Read-only preflight used to avoid prompting for a password on a safe rerun. */
    public function isAlreadyProvisioned(string $name, string $email): bool
    {
        $this->guard->assertAllowed();
        $this->validateIdentity($name, $email);

        return $this->inspect($name, $email)['already_provisioned'];
    }

    /** Create a new administrator or return false when the same administrator is already set up. */
    public function provision(string $name, string $email, string $password): bool
    {
        $this->guard->assertAllowed();
        $this->validateIdentity($name, $email);

        Validator::make(['password' => $password], [
            'password' => ['required', 'string', 'min:12'],
        ])->validate();

        $connectionName = (string) config('database.default');

        return DB::connection($connectionName)->transaction(function () use ($name, $email, $password, $connectionName): bool {
            $state = $this->inspect($name, $email, lockForUpdate: true);

            if ($state['already_provisioned']) {
                return false;
            }

            $role = $state['role'];
            if (! $role) {
                $role = new Role;
                $role->id = self::ADMIN_ROLE_ID;
                $role->title = 'Admin';
                $role->save();

                $role->permissions()->attach($state['permission_ids']);
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $user->roles()->attach($role->id);

            return true;
        });
    }

    private function inspect(string $name, string $email, bool $lockForUpdate = false): array
    {
        $connectionName = (string) config('database.default');

        foreach (['roles', 'permissions', 'permission_role', 'role_user', 'users', 'participants'] as $table) {
            if (! Schema::connection($connectionName)->hasTable($table)) {
                throw new RuntimeException("Required table [{$table}] is missing. Apply the existing application migrations before using this command.");
            }
        }

        $permissionRows = Permission::withTrashed()
            ->whereIn('title', self::REQUIRED_PERMISSION_TITLES)
            ->when($lockForUpdate, fn ($query) => $query->lockForUpdate())
            ->get(['id', 'title', 'deleted_at']);

        $permissionIds = [];
        foreach (self::REQUIRED_PERMISSION_TITLES as $title) {
            $rows = $permissionRows->where('title', $title)->values();
            if ($rows->count() !== 1 || $rows->first()->deleted_at !== null) {
                throw new RuntimeException("Permission [{$title}] is missing, duplicated, or inactive. No roles or permissions were changed.");
            }

            $permissionIds[$title] = (int) $rows->first()->id;
        }

        $roleRows = Role::withTrashed()
            ->where(function ($query): void {
                $query->where('id', self::ADMIN_ROLE_ID)->orWhere('title', 'Admin');
            })
            ->when($lockForUpdate, fn ($query) => $query->lockForUpdate())
            ->get(['id', 'title', 'deleted_at']);

        $role = null;
        if ($roleRows->isNotEmpty()) {
            if (
                $roleRows->count() !== 1
                || (int) $roleRows->first()->id !== self::ADMIN_ROLE_ID
                || $roleRows->first()->title !== 'Admin'
                || $roleRows->first()->deleted_at !== null
            ) {
                throw new RuntimeException('The reserved Admin role (ID 1) is in an unexpected state. No roles or permissions were changed.');
            }

            $role = Role::withTrashed()->find(self::ADMIN_ROLE_ID);
            $assignedPermissionIds = DB::connection($connectionName)
                ->table('permission_role')
                ->where('role_id', self::ADMIN_ROLE_ID)
                ->when($lockForUpdate, fn ($query) => $query->lockForUpdate())
                ->orderBy('permission_id')
                ->pluck('permission_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $expectedPermissionIds = array_values($permissionIds);
            sort($expectedPermissionIds);

            if ($assignedPermissionIds !== $expectedPermissionIds) {
                throw new RuntimeException('The existing Admin role does not have exactly the required local-bootstrap permissions. No roles or permissions were changed.');
            }
        } else {
            $orphanRoleMappings = DB::connection($connectionName)->table('permission_role')
                ->where('role_id', self::ADMIN_ROLE_ID)
                ->exists()
                || DB::connection($connectionName)->table('role_user')
                    ->where('role_id', self::ADMIN_ROLE_ID)
                    ->exists();

            if ($orphanRoleMappings) {
                throw new RuntimeException('Role ID 1 has orphaned role or permission mappings. No roles or permissions were changed.');
            }
        }

        $matchingUsers = User::withTrashed()
            ->where('email', $email)
            ->when($lockForUpdate, fn ($query) => $query->lockForUpdate())
            ->get();

        if ($matchingUsers->isEmpty()) {
            return [
                'already_provisioned' => false,
                'role' => $role,
                'permission_ids' => array_values($permissionIds),
            ];
        }

        $user = $matchingUsers->first();
        $assignedRoleIds = DB::connection($connectionName)->table('role_user')
            ->where('user_id', $user->id)
            ->when($lockForUpdate, fn ($query) => $query->lockForUpdate())
            ->orderBy('role_id')
            ->pluck('role_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (
            $matchingUsers->count() !== 1
            || $user->deleted_at !== null
            || $user->name !== $name
            || $assignedRoleIds !== [self::ADMIN_ROLE_ID]
            || DB::connection($connectionName)->table('participants')->where('user_id', $user->id)->exists()
        ) {
            throw new RuntimeException('That email already belongs to an account that is not this bootstrap administrator. No account, role, or participant was changed.');
        }

        return [
            'already_provisioned' => true,
            'role' => $role,
            'permission_ids' => array_values($permissionIds),
        ];
    }

    private function validateIdentity(string $name, string $email): void
    {
        Validator::make(compact('name', 'email'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ])->validate();
    }
}
