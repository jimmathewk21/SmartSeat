<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class SpeakerSortingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_speaker_index_sorts_by_header_links_and_keeps_pagination_query_string(): void
    {
        $admin = $this->makeSpeakerAdmin();
        $token = 'AdminSortBench-' . Str::lower(Str::random(8));

        Speaker::factory()->create([
            'name' => $token . ' Speaker 10',
            'company_name' => $token . ' Company 10',
            'designation' => $token . ' Designation 10',
            'country' => 'Denmark',
            'topic' => $token . ' Topic 10',
            'email' => $token . '.speaker10@example.com',
            'is_active' => true,
        ]);

        Speaker::factory()->create([
            'name' => $token . ' Speaker 1',
            'company_name' => $token . ' Company 1',
            'designation' => $token . ' Designation 1',
            'country' => 'Brazil',
            'topic' => $token . ' Topic 1',
            'email' => $token . '.speaker1@example.com',
            'is_active' => true,
        ]);

        Speaker::factory()->create([
            'name' => $token . ' Speaker 2',
            'company_name' => $token . ' Company 2',
            'designation' => $token . ' Designation 2',
            'country' => 'Canada',
            'topic' => $token . ' Topic 2',
            'email' => $token . '.speaker2@example.com',
            'is_active' => true,
        ]);

        $expectedOrders = [
            'name' => [
                'asc' => [$token . ' Speaker 1', $token . ' Speaker 2', $token . ' Speaker 10'],
                'desc' => [$token . ' Speaker 10', $token . ' Speaker 2', $token . ' Speaker 1'],
            ],
            'company_name' => [
                'asc' => [$token . ' Speaker 1', $token . ' Speaker 2', $token . ' Speaker 10'],
                'desc' => [$token . ' Speaker 10', $token . ' Speaker 2', $token . ' Speaker 1'],
            ],
            'designation' => [
                'asc' => [$token . ' Speaker 1', $token . ' Speaker 2', $token . ' Speaker 10'],
                'desc' => [$token . ' Speaker 10', $token . ' Speaker 2', $token . ' Speaker 1'],
            ],
            'email' => [
                'asc' => [$token . ' Speaker 1', $token . ' Speaker 2', $token . ' Speaker 10'],
                'desc' => [$token . ' Speaker 10', $token . ' Speaker 2', $token . ' Speaker 1'],
            ],
            'topic' => [
                'asc' => [$token . ' Speaker 1', $token . ' Speaker 2', $token . ' Speaker 10'],
                'desc' => [$token . ' Speaker 10', $token . ' Speaker 2', $token . ' Speaker 1'],
            ],
        ];

        foreach ($expectedOrders as $sortBy => $directions) {
            foreach ($directions as $sortDir => $expectedOrder) {
                $response = $this->actingAs($admin)
                    ->get(route('admin.speakers.index', [
                        'search' => $token,
                        'sort_by' => $sortBy,
                        'sort_dir' => $sortDir,
                    ]));

                $response->assertOk();

                $this->assertSame(
                    $expectedOrder,
                    array_slice($this->extractSpeakerNamesFromResponse($response->getContent()), 0, 3),
                    sprintf('Unexpected speaker order for %s/%s.', $sortBy, $sortDir)
                );
            }
        }

        for ($i = 1; $i <= 26; $i++) {
            Speaker::factory()->create([
                'name' => sprintf('%s ZZZ Speaker %02d', $token, $i),
                'company_name' => sprintf('%s ZZZ Company %02d', $token, $i),
                'designation' => sprintf('%s ZZZ Designation %02d', $token, $i),
                'country' => sprintf('ZZZ Country %02d', $i),
                'topic' => sprintf('%s ZZZ Topic %02d', $token, $i),
                'email' => sprintf('%s.zzz%02d@example.com', $token, $i),
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.speakers.index', [
                'search' => $token,
                'sort_by' => 'name',
                'sort_dir' => 'asc',
            ]));

        $response->assertOk()
            ->assertSee(htmlspecialchars(route('admin.speakers.index', [
                'search' => $token,
                'sort_by' => 'name',
                'sort_dir' => 'asc',
                'page' => 2,
            ]), ENT_QUOTES), false);
    }

    public function test_admin_speaker_index_renders_sort_headers_and_no_sort_panel(): void
    {
        $admin = $this->makeSpeakerAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.speakers.index'));

        $response->assertOk()
            ->assertSee('aria-label="Sort by name"', false)
            ->assertSee('aria-label="Sort by company name"', false)
            ->assertSee('aria-label="Sort by designation"', false)
            ->assertSee('aria-label="Sort by email"', false)
            ->assertSee('aria-label="Sort by topic"', false)
            ->assertDontSee('Sort By')
            ->assertDontSee('speaker-sort-name')
            ->assertDontSee('speaker-sort-company')
            ->assertDontSee('speaker-sort-topic');
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

    /**
     * @return array<int, string>
     */
    private function extractSpeakerNamesFromResponse(string $html): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $rows = $xpath->query('//tbody/tr');
        $names = [];

        if ($rows === false) {
            return $names;
        }

        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length < 3) {
                continue;
            }

            $name = trim(preg_replace('/\s+/', ' ', $cells->item(2)->textContent ?? '') ?? '');
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }
}
