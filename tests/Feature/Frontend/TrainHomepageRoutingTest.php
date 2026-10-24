<?php

namespace Tests\Feature\Frontend;

use App\Services\TrainSearchService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrainHomepageRoutingTest extends TestCase
{
    public function test_station_picker_keeps_catalog_ids_and_escapes_station_labels(): void
    {
        $this->mock(TrainSearchService::class, function ($mock): void {
            $mock->shouldReceive('stations')->once()->andReturn(collect([
                (object) ['id' => 103, 'name' => 'Mumbai CSMT', 'city' => 'Mumbai', 'code' => 'CSMT'],
                (object) ['id' => 204, 'name' => '<script>unsafe</script>', 'city' => 'Pune', 'code' => 'PUNE'],
            ]));
        });

        $this->get(route('frontend.trains.home'))
            ->assertOk()
            ->assertSee('name="from_station_id" required data-station-select', false)
            ->assertSee('name="to_station_id" required data-station-select', false)
            ->assertSee('value="103"', false)
            ->assertSee('value="204"', false)
            ->assertSee('Mumbai CSMT · Mumbai (CSMT)')
            ->assertSee('&lt;script&gt;unsafe&lt;/script&gt;', false)
            ->assertDontSee('<script>unsafe</script>', false)
            ->assertDontSee('name="passenger_count"', false)
            ->assertSee('images/railway/railway-hero.webp', false)
            ->assertSee('Photography &amp; credits', false);
    }

    public function test_root_and_trains_render_the_railway_search_page_and_event_home_route_is_removed(): void
    {
        $this->mock(TrainSearchService::class, function ($mock): void {
            $mock->shouldReceive('stations')->times(3)->andReturn(collect());
        });

        foreach (['/', '/trains', '/index.html'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('SmartSeat')
                ->assertSee('INDIAN RAIL JOURNEYS')
                ->assertSee('Find your way')
                ->assertDontSee('AIvent')
                ->assertDontSee('AI Summit 2026')
                ->assertDontSee('Event Schedule')
                ->assertDontSee('Buy Tickets');
        }

        $this->assertNull(Route::getRoutes()->getByName('frontend.home'));
        $legacyHome = Route::getRoutes()->getByName('frontend.trains.legacy-home');
        $this->assertNotNull($legacyHome);
        $this->assertSame('index.html', $legacyHome->uri());
        $this->assertContains('page.access', $legacyHome->gatherMiddleware());

    }
}
