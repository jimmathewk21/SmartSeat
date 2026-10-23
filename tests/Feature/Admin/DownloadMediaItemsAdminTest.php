<?php

namespace Tests\Feature\Admin;

use App\Models\DownloadMediaItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DownloadMediaItemsAdminTest extends TestCase
{
    use DatabaseTransactions;

    private function adminUser(): User
    {
        $role = Role::firstOrCreate(['title' => 'Settings Admin']);
        Permission::insertOrIgnore([
            ['title' => 'setting_access'],
        ]);
        $permissionIds = Permission::whereIn('title', ['setting_access'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        return $admin;
    }

    public function test_admin_can_bulk_publish_and_unpublish_download_items(): void
    {
        $admin = $this->adminUser();

        $firstItem = DownloadMediaItem::query()->create([
            'section_title' => 'Media Kit',
            'audience_type' => DownloadMediaItem::AUDIENCE_SPEAKER,
            'item_title' => 'First Item',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $secondItem = DownloadMediaItem::query()->create([
            'section_title' => 'Media Kit',
            'audience_type' => DownloadMediaItem::AUDIENCE_DELEGATE,
            'item_title' => 'Second Item',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.download-media-items.bulkStatus'), [
                'ids' => [$firstItem->id, $secondItem->id],
                'status' => 'publish',
            ])
            ->assertRedirect(route('admin.download-media-items.index'));

        $this->assertDatabaseHas('download_media_items', [
            'id' => $firstItem->id,
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('download_media_items', [
            'id' => $secondItem->id,
            'is_active' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.download-media-items.bulkStatus'), [
                'ids' => [$firstItem->id],
                'status' => 'unpublish',
            ])
            ->assertRedirect(route('admin.download-media-items.index'));

        $this->assertDatabaseHas('download_media_items', [
            'id' => $firstItem->id,
            'is_active' => 0,
        ]);
    }

    public function test_admin_can_bulk_delete_download_items(): void
    {
        $admin = $this->adminUser();

        $firstItem = DownloadMediaItem::query()->create([
            'section_title' => 'Media Kit',
            'audience_type' => DownloadMediaItem::AUDIENCE_SPONSOR,
            'item_title' => 'Delete First',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $secondItem = DownloadMediaItem::query()->create([
            'section_title' => 'Media Kit',
            'audience_type' => DownloadMediaItem::AUDIENCE_DELEGATE,
            'item_title' => 'Delete Second',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.download-media-items.massDestroy'), [
                'ids' => [$firstItem->id, $secondItem->id],
            ])
            ->assertRedirect(route('admin.download-media-items.index'));

        $this->assertSoftDeleted('download_media_items', ['id' => $firstItem->id]);
        $this->assertSoftDeleted('download_media_items', ['id' => $secondItem->id]);
    }
}
