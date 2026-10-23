<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\FrontendPageAccessSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FrontendPageAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_page_access_definitions_keep_train_and_shared_pages_only(): void
    {
        $definitions = FrontendPageAccessSettings::definitions();

        $this->assertSame([
            'home',
            'dynamic_pages',
            'faqs',
            'contact',
            'my_contacts',
            'download',
        ], array_keys($definitions));
        $this->assertSame(
            ['frontend.trains.home', 'frontend.trains.search', 'frontend.trains.legacy-home', 'frontend.newsletter.store'],
            $definitions['home']['routes']
        );
        $this->assertSame(['/', 'trains', 'index.html'], $definitions['home']['paths']);
    }

    public function test_guest_access_for_dynamic_pages_can_be_controlled_from_settings(): void
    {
        FrontendPageAccessSettings::set([]);

        Page::create([
            'title' => 'Public Info',
            'slug' => 'public-info',
            'content' => '<p>Test</p>',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('frontend.page.show', 'public-info'))
            ->assertRedirect(route('login'));

        FrontendPageAccessSettings::set([
            'dynamic_pages' => [
                'guest' => true,
            ],
        ]);

        $this->get(route('frontend.page.show', 'public-info'))
            ->assertOk()
            ->assertSee('Public Info');
    }

    public function test_admin_can_update_page_access_settings(): void
    {
        $role = Role::create(['title' => 'Settings Admin']);
        Permission::insertOrIgnore([
            ['title' => 'setting_access'],
            ['title' => 'setting_edit'],
        ]);

        $permissionIds = Permission::whereIn('title', [
            'setting_access',
            'setting_edit',
        ])->pluck('id');

        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $this->actingAs($admin)
            ->put(route('admin.frontend.page-access.update'), [
                'pages' => [
                    'dynamic_pages' => [
                        'guest' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.frontend.page-access.edit'));

        $settings = FrontendPageAccessSettings::get();

        $this->assertTrue($settings['dynamic_pages']['guest']);
    }
}
