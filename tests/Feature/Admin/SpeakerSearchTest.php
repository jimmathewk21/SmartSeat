<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerSearchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_speaker_index_searches_across_name_company_designation_email_and_topic(): void
    {
        $admin = $this->makeSpeakerAdmin();

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
            ['filters' => ['search' => 'Aria'], 'expected' => $speakers[0]],
            ['filters' => ['search' => 'Company Search Co'], 'expected' => $speakers[1]],
            ['filters' => ['search' => 'Head of Product'], 'expected' => $speakers[2]],
            ['filters' => ['search' => 'dara.emailfield@example.com'], 'expected' => $speakers[3]],
            ['filters' => ['search' => 'Leadership at scale'], 'expected' => $speakers[4]],
        ];

        foreach ($cases as $case) {
            $response = $this->actingAs($admin)
                ->get(route('admin.speakers.index', $case['filters']));

            $response->assertOk()
                ->assertSee($case['expected']->name);

            foreach ($speakers as $speaker) {
                if ($speaker->isNot($case['expected'])) {
                    $response->assertDontSee($speaker->name);
                }
            }
        }
    }

    public function test_admin_speaker_export_uses_the_keyword_search(): void
    {
        $admin = $this->makeSpeakerAdmin();

        $matchingSpeaker = Speaker::factory()->create([
            'name' => 'Export Match',
            'company_name' => 'Filtered Company',
            'designation' => 'Principal Architect',
            'email' => 'export.match@example.com',
            'topic' => 'Generative AI',
            'is_active' => true,
        ]);

        Speaker::factory()->create([
            'name' => 'Export Skip',
            'company_name' => 'Other Company',
            'designation' => 'Principal Architect',
            'email' => 'export.skip@example.com',
            'topic' => 'Generative AI',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.speakers.export', [
                'search' => 'Filtered Company',
            ]));

        $csv = $response->streamedContent();

        $this->assertStringContainsString($matchingSpeaker->name, $csv);
        $this->assertStringNotContainsString('Export Skip', $csv);
    }

    private function makeSpeakerAdmin(): User
    {
        $role = Role::create(['title' => 'Speaker Admin']);
        Permission::insertOrIgnore([
            ['title' => 'speaker_access'],
        ]);

        $permissionIds = Permission::whereIn('title', [
            'speaker_access',
        ])->pluck('id');

        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        return $admin;
    }
}
