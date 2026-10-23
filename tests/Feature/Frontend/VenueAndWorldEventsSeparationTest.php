<?php

namespace Tests\Feature\Frontend;

use App\Models\User;
use App\Models\VenueHotel;
use App\Models\WorldEvent;
use App\Support\FrontendPageAccessSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VenueAndWorldEventsSeparationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_world_events_page_uses_only_world_event_records(): void
    {
        FrontendPageAccessSettings::set([
            'world_events' => [
                'user' => true,
                'member' => true,
                'guest' => true,
            ],
        ]);

        WorldEvent::factory()->create([
            'name' => 'Global IP Summit',
            'country' => 'India',
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'theme' => 'Patents',
        ]);

        VenueHotel::factory()->create([
            'name' => 'Palm Business Bay Hotel',
            'country' => 'UAE',
        ]);

        $this->get(route('frontend.world-events.index', [
            'view' => 'monthly',
            'year' => 2026,
            'month' => 5,
        ]))
            ->assertOk()
            ->assertSee('Global IP Summit')
            ->assertDontSee('Palm Business Bay Hotel');
    }

    public function test_venue_page_uses_only_venue_hotels(): void
    {
        $user = User::factory()->create();

        WorldEvent::factory()->create([
            'name' => 'Tokyo IP Forum',
        ]);

        VenueHotel::factory()->create([
            'name' => 'Royal Convention Hotel',
        ]);

        $this->actingAs($user)
            ->get(route('frontend.venue.index'))
            ->assertOk()
            ->assertSee('Royal Convention Hotel')
            ->assertDontSee('Tokyo IP Forum');
    }
}
