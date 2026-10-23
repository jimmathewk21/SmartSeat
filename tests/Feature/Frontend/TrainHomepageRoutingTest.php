<?php

namespace Tests\Feature\Frontend;

use App\Services\TrainSearchService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrainHomepageRoutingTest extends TestCase
{
    public function test_root_and_trains_render_the_railway_search_page_while_event_home_stays_routed(): void
    {
        $this->mock(TrainSearchService::class, function ($mock): void {
            $mock->shouldReceive('stations')->twice()->andReturn(collect());
        });

        foreach (['/', '/trains'] as $url) {
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

        $eventHome = Route::getRoutes()->getByName('frontend.home');
        $this->assertNotNull($eventHome);
        $this->assertSame('event-home', $eventHome->uri());
        $this->assertSame('frontend', $eventHome->defaults['view']);
        $this->assertContains('page.access', $eventHome->gatherMiddleware());

        $legacyEventHome = Route::getRoutes()->match(\Illuminate\Http\Request::create('/index.html', 'GET'));
        $this->assertSame('frontend', $legacyEventHome->defaults['view']);
        $this->assertContains('page.access', $legacyEventHome->gatherMiddleware());
    }
}
