<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_public_pages_render_without_database_queries(): void
    {
        DB::shouldReceive('connection')->never();
        foreach (['home', 'login', 'register'] as $name) {
            $this->get(route($name))->assertOk()->assertSee('SmartSeat');
        }
    }

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (['dashboard', 'administrator.dashboard', 'password.confirm'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }

    public function test_passengers_cannot_access_administration(): void
    {
        $user = User::factory()->make();
        $this->actingAs($user, 'web')->get(route('administrator.dashboard'))->assertForbidden();
    }

    public function test_passenger_empty_dashboard_requires_no_database(): void
    {
        $user = User::factory()->make(['name' => '<script>alert(1)</script>']);
        DB::shouldReceive('connection')->never();
        $this->actingAs($user, 'web')->get(route('dashboard'))->assertOk()
            ->assertSee('Your journeys, all in one place')->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_administrator_sees_empty_overview_and_correct_dashboard_redirect(): void
    {
        $user = User::factory()->administrator()->make();
        DB::shouldReceive('connection')->never();
        $this->actingAs($user, 'web')->get(route('dashboard'))->assertRedirect(route('administrator.dashboard'));
        $this->get(route('administrator.dashboard'))->assertOk()->assertSee('Your railway control room');
    }

    public function test_csrf_middleware_rejects_an_unprotected_login_post(): void
    {
        // Laravel bypasses CSRF in tests; turn off that bypass for this request only.
        $this->app['env'] = 'local';
        $this->post(route('login.store'), ['email' => 'asha@example.test', 'password' => 'bad'])
            ->assertStatus(419);
    }
}
