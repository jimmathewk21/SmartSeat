<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerLayoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_frontend_speaker_cards_use_the_dense_responsive_grid_classes(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['speakers' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $speaker = Speaker::factory()->create([
            'name' => 'Layout Speaker',
            'is_active' => true,
        ]);

        $response = $this->get(route('frontend.speakers.index'));

        $response->assertOk()
            ->assertSee('col-xxl-2 col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12', false)
            ->assertSee($speaker->name);
    }
}
