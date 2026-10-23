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

    public function test_guest_access_for_home_and_dynamic_pages_can_be_controlled_from_settings(): void
    {
        FrontendPageAccessSettings::set([]);

        Page::create([
            'title' => 'Public Info',
            'slug' => 'public-info',
            'content' => '<p>Test</p>',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('frontend.home'))
            ->assertRedirect(route('login'));

        $this->get(route('frontend.page.show', 'public-info'))
            ->assertRedirect(route('login'));

        FrontendPageAccessSettings::set([
            'home' => [
                'guest' => true,
            ],
            'dynamic_pages' => [
                'guest' => true,
            ],
        ]);

        $this->get(route('frontend.home'))
            ->assertOk();

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
                    'home' => [
                        'guest' => '1',
                    ],
                    'sponsors' => [
                        'guest' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.frontend.page-access.edit'));

        $settings = FrontendPageAccessSettings::get();

        $this->assertTrue($settings['home']['guest']);
        $this->assertTrue($settings['sponsors']['guest']);
        $this->assertFalse($settings['dynamic_pages']['guest']);
    }
}
