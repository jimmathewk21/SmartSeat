<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerKeyFigureOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_key_figures_are_listed_before_other_speakers(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['speakers' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $regularSpeaker = Speaker::factory()->create([
            'name' => 'Regular Speaker',
            'is_active' => true,
            'is_key_figure' => false,
            'sort_order' => 0,
        ]);

        $keyFigure = Speaker::factory()->create([
            'name' => 'Key Figure Speaker',
            'is_active' => true,
            'is_key_figure' => true,
            'sort_order' => 99,
        ]);

        $response = $this->get(route('frontend.speakers.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            $keyFigure->name,
            $regularSpeaker->name,
        ], false);
        $response->assertSee('title="Key Figure (Click to filter)"', false);
    }
}
