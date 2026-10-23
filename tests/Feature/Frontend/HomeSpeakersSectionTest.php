<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HomeSpeakersSectionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_home_page_speaker_cards_use_six_up_large_screen_grid_classes(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['home' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $speaker = Speaker::factory()->create([
            'name' => 'Home Speaker',
            'is_active' => true,
            'show_on_homepage' => true,
        ]);

        $response = $this->get(route('frontend.home'));

        $response->assertOk()
            ->assertSee('Meet Our Speakers')
            ->assertSee('col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12', false)
            ->assertSee($speaker->name);
    }
}
