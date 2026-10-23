<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerSortingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_frontend_speaker_index_sorts_by_core_fields_and_keeps_sort_in_pagination_links(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['speakers' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        Speaker::factory()->create([
            'name' => 'Speaker 10',
            'company_name' => 'Company 10',
            'country' => 'Denmark',
            'topic' => 'Topic 10',
            'email' => 'speaker10@example.com',
            'is_active' => true,
        ]);
        Speaker::factory()->create([
            'name' => 'Speaker 1',
            'company_name' => 'Company 1',
            'country' => 'Brazil',
            'topic' => 'Topic 1',
            'email' => 'speaker1@example.com',
            'is_active' => true,
        ]);
        Speaker::factory()->create([
            'name' => 'Speaker 2',
            'company_name' => 'Company 2',
            'country' => 'Canada',
            'topic' => 'Topic 2',
            'email' => 'speaker2@example.com',
            'is_active' => true,
        ]);

        $expectedOrders = [
            'name' => ['Speaker 1', 'Speaker 2', 'Speaker 10'],
            'company_name' => ['Speaker 1', 'Speaker 2', 'Speaker 10'],
            'country' => ['Speaker 1', 'Speaker 2', 'Speaker 10'],
            'topic' => ['Speaker 1', 'Speaker 2', 'Speaker 10'],
            'email' => ['Speaker 1', 'Speaker 2', 'Speaker 10'],
        ];

        foreach ($expectedOrders as $sortBy => $expectedOrder) {
            $response = $this->get(route('frontend.speakers.index', [
                'keyword' => 'Speaker',
                'sort_by' => $sortBy,
            ]));

            $response->assertOk()
                ->assertSeeInOrder($expectedOrder)
                ->assertSee('Sort By', false);
        }

        for ($i = 1; $i <= 58; $i++) {
            Speaker::factory()->create([
                'name' => sprintf('ZZZ Speaker %02d', $i),
                'company_name' => sprintf('ZZZ Company %02d', $i),
                'country' => sprintf('ZZZ Country %02d', $i),
                'topic' => sprintf('ZZZ Topic %02d', $i),
                'email' => sprintf('zzz%02d@example.com', $i),
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('frontend.speakers.index', [
            'keyword' => 'Speaker',
            'sort_by' => 'name',
        ]));

        $response->assertOk()
            ->assertSee(htmlspecialchars(route('frontend.speakers.index', [
                'keyword' => 'Speaker',
                'sort_by' => 'name',
                'page' => 2,
            ]), ENT_QUOTES), false);
    }
}
