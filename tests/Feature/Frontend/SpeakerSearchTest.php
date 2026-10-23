<?php

namespace Tests\Feature\Frontend;

use App\Models\Setting;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerSearchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_frontend_speaker_index_searches_by_core_fields_including_email(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'frontend_page_access'],
            ['value' => json_encode(['speakers' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        $speakers = [
            Speaker::factory()->create([
                'name' => 'Aria Namefield',
                'company_name' => 'Northwind Labs',
                'designation' => 'Chief Strategy Officer',
                'email' => 'aria.namefield@example.com',
                'topic' => 'Future of work',
                'is_active' => true,
            ]),
            Speaker::factory()->create([
                'name' => 'Bora Companyfield',
                'company_name' => 'Company Search Co',
                'designation' => 'Innovation Lead',
                'email' => 'bora.companyfield@example.com',
                'topic' => 'Digital transformation',
                'is_active' => true,
            ]),
            Speaker::factory()->create([
                'name' => 'Cora Designationfield',
                'company_name' => 'Metro Systems',
                'designation' => 'Head of Product',
                'email' => 'cora.designationfield@example.com',
                'topic' => 'Customer experience',
                'is_active' => true,
            ]),
            Speaker::factory()->create([
                'name' => 'Dara Emailfield',
                'company_name' => 'Skyline Ventures',
                'designation' => 'Marketing Director',
                'email' => 'dara.emailfield@example.com',
                'topic' => 'Growth marketing',
                'is_active' => true,
            ]),
            Speaker::factory()->create([
                'name' => 'Ena Topicfield',
                'company_name' => 'Topic Search Inc',
                'designation' => 'Principal Consultant',
                'email' => 'ena.topicfield@example.com',
                'topic' => 'Leadership at scale',
                'is_active' => true,
            ]),
        ];

        $cases = [
            ['query' => 'Aria Namefield', 'expected' => $speakers[0]],
            ['query' => 'Company Search Co', 'expected' => $speakers[1]],
            ['query' => 'Head of Product', 'expected' => $speakers[2]],
            ['query' => 'dara.emailfield@example.com', 'expected' => $speakers[3]],
            ['query' => 'Leadership at scale', 'expected' => $speakers[4]],
        ];

        foreach ($cases as $case) {
            $response = $this->get(route('frontend.speakers.index', ['keyword' => $case['query']]));

            $response->assertOk()
                ->assertSee($case['expected']->name)
                ->assertSee('Search by name, company, designation, email, or topic', false);

            foreach ($speakers as $speaker) {
                if ($speaker->isNot($case['expected'])) {
                    $response->assertDontSee($speaker->name);
                }
            }
        }
    }
}
