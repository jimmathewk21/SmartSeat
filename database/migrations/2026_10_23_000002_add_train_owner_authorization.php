<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('train_owner_approved_at')->nullable()->after('verification_status');
        });

        Schema::table('trains', function (Blueprint $table): void {
            $table->foreignId('owner_user_id')->nullable()->after('name')->constrained('users')->nullOnDelete();
        });

        $now = now();
        foreach (['train_management_access', 'train_management_edit'] as $title) {
            $permission = DB::table('permissions')->where('title', $title)->orderBy('id')->first(['id', 'deleted_at']);
            if (! $permission) {
                DB::table('permissions')->insert(['title' => $title, 'created_at' => $now, 'updated_at' => $now]);
            } elseif ($permission->deleted_at !== null) {
                DB::table('permissions')->where('id', $permission->id)->update(['deleted_at' => null, 'updated_at' => $now]);
            }
        }

        $ownerRole = DB::table('roles')->where('title', 'Train Owner')->orderBy('id')->first(['id', 'deleted_at']);
        if (! $ownerRole) {
            DB::table('roles')->insert(['title' => 'Train Owner', 'created_at' => $now, 'updated_at' => $now]);
        } elseif ($ownerRole->deleted_at !== null) {
            DB::table('roles')->where('id', $ownerRole->id)->update(['deleted_at' => null, 'updated_at' => $now]);
        }

        $roleId = DB::table('roles')->where('title', 'Train Owner')->orderBy('id')->value('id');
        $permissionIds = DB::table('permissions')->whereIn('title', ['train_management_access', 'train_management_edit'])->pluck('id');
        foreach ($permissionIds as $permissionId) {
            if (! DB::table('permission_role')->where('role_id', $roleId)->where('permission_id', $permissionId)->exists()) {
                DB::table('permission_role')->insert(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('trains', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('owner_user_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('train_owner_approved_at');
        });
    }
};
