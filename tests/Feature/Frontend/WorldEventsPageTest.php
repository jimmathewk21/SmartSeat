<?php

namespace Tests\Feature\Frontend;

use App\Models\Role;
use App\Models\User;
use App\Models\WorldEvent;
use App\Support\FrontendPageAccessSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WorldEventsPageTest extends TestCase
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

    public function test_world_events_page_defaults_to_current_year_and_month(): void
    {
        Carbon::setTestNow('2026-05-01 10:00:00');

        $this->allowWorldEventsPageAccess();

        $user = $this->adminUser();

        WorldEvent::factory()->create([
            'name' => 'LESI 2023',
            'country' => 'Canada',
            'start_date' => '2023-04-30',
            'end_date' => '2023-05-02',
            'is_active' => true,
        ]);

        WorldEvent::factory()->create([
            'name' => 'LESI 2027',
            'country' => 'Ireland',
            'start_date' => '2027-04-26',
            'end_date' => '2027-04-29',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('frontend.world-events.index'));

        $response->assertOk()
            ->assertSee('May 2026')
            ->assertSee('2026 Events Timeline')
            ->assertDontSee('2027 Events Timeline');

        Carbon::setTestNow();
    }

    public function test_world_events_page_filters_by_country(): void
    {
        $this->allowWorldEventsPageAccess();

        $user = $this->adminUser();

        WorldEvent::factory()->create([
            'name' => 'Berlin IP Forum',
            'country' => 'Germany',
            'start_date' => '2026-04-03',
            'end_date' => '2026-04-05',
        ]);

        WorldEvent::factory()->create([
            'name' => 'Tokyo IP Forum',
            'country' => 'Japan',
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-12',
        ]);

        $response = $this->actingAs($user)->get(route('frontend.world-events.index', [
            'view' => 'monthly',
            'year' => 2026,
            'month' => 4,
            'country' => 'Germany',
        ]));

        $response->assertOk();
        $response->assertSee('Berlin IP Forum');
        $response->assertSee('data-event-name="Berlin IP Forum"', false);
        $response->assertDontSee('data-event-name="Tokyo IP Forum"', false);
    }

    public function test_world_events_page_shows_past_events_for_selected_month(): void
    {
        Carbon::setTestNow('2026-05-21 10:00:00');

        $this->allowWorldEventsPageAccess();

        $user = $this->adminUser();

        WorldEvent::factory()->create([
            'name' => 'March IP Summit',
            'country' => 'India',
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-11',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('frontend.world-events.index', [
            'view' => 'monthly',
            'year' => 2026,
            'month' => 3,
        ]));

        $response->assertOk()
            ->assertSee('March IP Summit')
            ->assertSee('10 March - 11 March');

        Carbon::setTestNow();
    }

    public function test_world_events_page_shows_old_events_in_monthly_and_yearly_views(): void
    {
        Carbon::setTestNow('2026-05-21 10:00:00');

        $this->allowWorldEventsPageAccess();

        $user = $this->adminUser();

        WorldEvent::factory()->create([
            'name' => 'WIPR Summit',
            'country' => 'USA',
            'city' => 'Chicago',
            'start_date' => '2025-03-12',
            'end_date' => '2025-03-13',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('frontend.world-events.index', [
            'view' => 'monthly',
            'year' => 2025,
            'month' => 3,
        ]))
            ->assertOk()
            ->assertSee('WIPR Summit')
            ->assertSee('12 March - 13 March');

        $this->actingAs($user)->get(route('frontend.world-events.index', [
            'view' => 'yearly',
            'year' => 2025,
        ]))
            ->assertOk()
            ->assertSee('WIPR Summit')
            ->assertSee('Events in 2025');

        Carbon::setTestNow();
    }

    public function test_world_events_yearly_list_shows_newest_ids_first(): void
    {
        Carbon::setTestNow('2026-05-21 10:00:00');

        $this->allowWorldEventsPageAccess();

        $user = $this->adminUser();

        $older = WorldEvent::factory()->create([
            'name' => 'Older World Event',
            'country' => 'India',
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'is_active' => true,
        ]);

        $newer = WorldEvent::factory()->create([
            'name' => 'Newer World Event',
            'country' => 'India',
            'start_date' => '2026-09-10',
            'end_date' => '2026-09-12',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('frontend.world-events.index', [
                'view' => 'yearly',
                'year' => 2026,
            ]))
            ->assertOk()
            ->assertSeeInOrder([
                $newer->name,
                $older->name,
            ]);

        Carbon::setTestNow();
    }

    public function test_world_events_event_name_filter_options_are_not_limited_by_active_country_filter(): void
    {
        $this->allowWorldEventsPageAccess();

        $user = $this->adminUser();

        WorldEvent::factory()->create([
            'name' => 'Berlin IP Forum',
            'country' => 'Germany',
            'start_date' => '2026-04-03',
            'end_date' => '2026-04-05',
        ]);

        WorldEvent::factory()->create([
            'name' => 'Tokyo IP Forum',
            'country' => 'Japan',
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-12',
        ]);

        $response = $this->actingAs($user)->get(route('frontend.world-events.index', [
            'view' => 'monthly',
            'year' => 2026,
            'month' => 4,
            'country' => 'Germany',
        ]));

        $response->assertOk();
        $response->assertSee('Berlin IP Forum');
        $response->assertSee('Tokyo IP Forum');
    }

    private function allowWorldEventsPageAccess(): void
    {
        FrontendPageAccessSettings::set([
            'world_events' => [
                'user' => true,
                'member' => true,
                'guest' => true,
            ],
        ]);
    }
}
