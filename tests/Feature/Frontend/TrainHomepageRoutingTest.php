<?php

namespace Tests\Feature\Frontend;

use App\Services\TrainSearchService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrainHomepageRoutingTest extends TestCase
{
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
