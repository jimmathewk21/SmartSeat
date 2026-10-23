<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerGenderFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_frontend_speaker_index_filters_by_gender(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['speakers' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $maleSpeaker = Speaker::factory()->create([
            'name' => 'Frontend Male Speaker',
            'gender' => 'male',
            'is_active' => true,
        ]);

        $femaleSpeaker = Speaker::factory()->create([
            'name' => 'Frontend Female Speaker',
            'gender' => 'female',
            'is_active' => true,
        ]);

        $this->get(route('frontend.speakers.index', ['speaker_type' => 'male']))
            ->assertOk()
            ->assertSee('Gender', false)
            ->assertSee($maleSpeaker->name)
            ->assertDontSee($femaleSpeaker->name);

        $this->get(route('frontend.speakers.index', ['speaker_type' => 'female']))
            ->assertOk()
            ->assertSee('Gender', false)
            ->assertSee($femaleSpeaker->name)
            ->assertDontSee($maleSpeaker->name);
    }
}
