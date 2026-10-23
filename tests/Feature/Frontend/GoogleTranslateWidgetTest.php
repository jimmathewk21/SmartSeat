<?php

namespace Tests\Feature\Frontend;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GoogleTranslateWidgetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_login_page_includes_google_translate_widget_markup(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('id="google_translate_element"', false);
        $response->assertSee('googleTranslateElementInit', false);
        $response->assertSee('translate.google.com/translate_a/element.js?cb=googleTranslateElementInit', false);
    }

    public function test_admin_dashboard_does_not_include_google_translate_widget_markup(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.home'));

        $response->assertOk();
        $response->assertDontSee('id="google_translate_element"', false);
        $response->assertDontSee('googleTranslateElementInit', false);
    }

    private function createAdminUser(): User
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

        $user = User::factory()->create();
        $user->roles()->sync([$adminRole->id]);

        return $user;
    }
}
