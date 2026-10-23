<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerTopicCategoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_topic_category_is_visible_on_speaker_listing_and_profile(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['speakers' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $speaker = Speaker::factory()->create([
            'name' => 'Visible Speaker',
            'category' => 'Keynote',
            'topic_category' => 'Artificial Intelligence',
            'topic' => 'AI governance',
            'is_active' => true,
        ]);

        $this->get(route('frontend.speakers.index'))
            ->assertOk()
            ->assertSee('Artificial Intelligence', false)
            ->assertSee('Topic Category:', false);

        $this->get(route('frontend.speakers.show', $speaker))
            ->assertOk()
            ->assertSee('Artificial Intelligence', false)
            ->assertSee('Topic Category:', false);
    }
}
