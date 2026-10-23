<?php

namespace Tests\Feature\Frontend;

use App\Support\FrontendPageAccessSettings;
use App\Models\Role;
use App\Models\WorldEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WorldEventsVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    private function adminUser(): User
    {
        $role = Role::firstOrCreate([
            'id' => 1,
        ], [
            'title' => 'Admin',
        ]);

        $user = User::factory()->create();
        $user->roles()->sync([$role->id]);

        return $user;
    }

    public function test_world_events_page_shows_only_published_world_events(): void
    {
        FrontendPageAccessSettings::set([
            'world_events' => [
                'user' => true,
                'member' => true,
                'guest' => true,
            ],
        ]);

        $user = $this->adminUser();

        WorldEvent::factory()->create([
            'name' => 'Published World IP Expo',
            'is_active' => true,
            'start_date' => '2026-04-03',
            'end_date' => '2026-04-05',
        ]);

        WorldEvent::factory()->create([
            'name' => 'Draft World IP Expo',
            'is_active' => false,
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-12',
        ]);

        $this->actingAs($user)->get(route('frontend.world-events.index', [
            'view' => 'monthly',
            'year' => 2026,
            'month' => 4,
        ]))
            ->assertOk()
            ->assertSee('Published World IP Expo')
            ->assertDontSee('Draft World IP Expo');
    }
}
