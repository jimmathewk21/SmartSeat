<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorldEvent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WorldEventsAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_bulk_publish_and_unpublish_world_events(): void
    {
        $admin = $this->createVenueAdmin();
        $events = WorldEvent::factory()->count(2)->create([
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.world-events.bulkStatus'), [
                'ids' => $events->pluck('id')->all(),
                'status' => 'publish',
            ])
            ->assertRedirect();

        foreach ($events as $event) {
            $this->assertDatabaseHas('world_events', [
                'id' => $event->id,
                'is_active' => true,
            ]);
        }

        $this->actingAs($admin)
            ->post(route('admin.world-events.bulkStatus'), [
                'ids' => [$events->first()->id],
                'status' => 'unpublish',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('world_events', [
            'id' => $events->first()->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_export_and_delete_selected_world_events(): void
    {
        $admin = $this->createVenueAdmin();

        $published = WorldEvent::factory()->create([
            'name' => 'Exported World Event',
            'country' => 'Germany',
            'is_active' => true,
        ]);

        $unpublished = WorldEvent::factory()->create([
            'name' => 'Hidden World Event',
            'country' => 'Germany',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.world-events.export', [
            'country' => 'Germany',
            'is_active' => '1',
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Exported World Event', $csv);
        $this->assertStringNotContainsString('Hidden World Event', $csv);
        $this->assertStringContainsString('Published', $csv);

        $selectedExportResponse = $this->actingAs($admin)->get(route('admin.world-events.export', [
            'ids' => [$unpublished->id],
        ]));

        $selectedExportResponse->assertOk();

        $selectedCsv = $selectedExportResponse->streamedContent();

        $this->assertStringContainsString('Hidden World Event', $selectedCsv);
        $this->assertStringNotContainsString('Exported World Event', $selectedCsv);
        $this->assertStringContainsString('Unpublished', $selectedCsv);

        $this->actingAs($admin)
            ->delete(route('admin.world-events.massDestroy'), [
                'ids' => [$published->id, $unpublished->id],
            ])
            ->assertRedirect();

        $this->assertSoftDeleted('world_events', ['id' => $published->id]);
        $this->assertSoftDeleted('world_events', ['id' => $unpublished->id]);
    }

    public function test_admin_can_filter_and_export_world_events_by_year(): void
    {
        $admin = $this->createVenueAdmin();

        WorldEvent::factory()->create([
            'name' => 'World Event 2023',
            'start_date' => '2023-04-30',
            'end_date' => '2023-05-02',
            'is_active' => true,
        ]);

        WorldEvent::factory()->create([
            'name' => 'World Event 2026',
            'start_date' => '2026-04-26',
            'end_date' => '2026-04-29',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.world-events.index', [
                'year' => '2026',
            ]))
            ->assertOk()
            ->assertSee('World Event 2026')
            ->assertDontSee('<td>World Event 2023</td>', false);

        $response = $this->actingAs($admin)->get(route('admin.world-events.export', [
            'year' => '2026',
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('World Event 2026', $csv);
        $this->assertStringNotContainsString('World Event 2023', $csv);
    }

    public function test_admin_can_sort_world_events_by_year(): void
    {
        $admin = $this->createVenueAdmin();

        WorldEvent::factory()->create([
            'name' => 'World Event 2026',
            'start_date' => '2026-04-26',
            'end_date' => '2026-04-29',
            'is_active' => true,
        ]);

        WorldEvent::factory()->create([
            'name' => 'World Event 2023',
            'start_date' => '2023-04-30',
            'end_date' => '2023-05-02',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.world-events.index', [
            'sort_by' => 'year',
            'sort_dir' => 'asc',
        ]));

        $response->assertOk();
        $response->assertSeeInOrder([
            'World Event 2023',
            'World Event 2026',
        ]);
    }

    public function test_admin_world_events_default_to_descending_id_order(): void
    {
        $admin = $this->createVenueAdmin();

        WorldEvent::factory()->create([
            'name' => 'Older World Event',
            'start_date' => '2026-04-26',
            'end_date' => '2026-04-29',
            'is_active' => true,
        ]);

        WorldEvent::factory()->create([
            'name' => 'Newer World Event',
            'start_date' => '2026-05-16',
            'end_date' => '2026-05-20',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.world-events.index'));

        $response->assertOk()
            ->assertSeeInOrder([
                'Newer World Event',
                'Older World Event',
            ]);
    }

    public function test_admin_can_filter_and_export_world_events_by_event_name(): void
    {
        $admin = $this->createVenueAdmin();

        WorldEvent::factory()->create([
            'name' => 'LESI',
            'start_date' => '2026-04-26',
            'end_date' => '2026-04-29',
            'is_active' => true,
        ]);

        WorldEvent::factory()->create([
            'name' => 'INTA',
            'start_date' => '2026-05-16',
            'end_date' => '2026-05-20',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.world-events.index', [
                'event_name' => 'LESI',
            ]))
            ->assertOk()
            ->assertSee('LESI')
            ->assertDontSee('<td>INTA</td>', false);

        $response = $this->actingAs($admin)->get(route('admin.world-events.export', [
            'event_name' => 'LESI',
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('LESI', $csv);
        $this->assertStringNotContainsString('INTA', $csv);
    }

    public function test_admin_world_event_create_rejects_an_end_date_before_the_start_date(): void
    {
        $admin = $this->createVenueAdmin();

        $payload = [
            'name' => 'Invalid Date Range World Event',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-09',
            'days' => 'Mon',
            'city' => 'London',
            'country' => 'United Kingdom',
            'theme' => 'Theme',
            'website' => 'https://example.com',
            'stars' => 4,
            'is_active' => 1,
        ];

        $this->actingAs($admin)
            ->post(route('admin.world-events.store'), $payload)
            ->assertSessionHasErrors(['end_date']);

        $this->assertDatabaseMissing('world_events', [
            'name' => 'Invalid Date Range World Event',
        ]);
    }

    public function test_admin_can_create_a_world_event_with_a_valid_date_range(): void
    {
        $admin = $this->createVenueAdmin();

        $payload = [
            'name' => 'Valid Date Range World Event',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
            'days' => 'Mon-Wed',
            'city' => 'London',
            'country' => 'United Kingdom',
            'theme' => 'Conference',
            'website' => 'https://example.com',
            'stars' => 5,
            'is_active' => 1,
        ];

        $this->actingAs($admin)
            ->post(route('admin.world-events.store'), $payload)
            ->assertRedirect(route('admin.world-events.index'));

        $this->assertDatabaseHas('world_events', [
            'name' => 'Valid Date Range World Event',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
            'city' => 'London',
            'country' => 'United Kingdom',
            'theme' => 'Conference',
            'website' => 'https://example.com',
            'stars' => 5,
            'is_active' => true,
        ]);
    }

    public function test_admin_world_event_form_allows_logo_link_or_file(): void
    {
        $admin = $this->createVenueAdmin();

        $this->actingAs($admin)
            ->get(route('admin.world-events.create'))
            ->assertOk()
            ->assertSee('name="logo_path"', false)
            ->assertSee('name="logo_file"', false)
            ->assertSee('You can paste a public logo URL or keep the uploaded file path.', false);
    }

    public function test_admin_can_save_world_event_logo_as_url(): void
    {
        $admin = $this->createVenueAdmin();

        $payload = [
            'name' => 'Logo URL World Event',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
            'days' => 'Mon-Wed',
            'city' => 'London',
            'country' => 'United Kingdom',
            'theme' => 'Conference',
            'website' => 'https://example.com',
            'logo_path' => 'https://example.com/logo.webp',
            'stars' => 5,
            'is_active' => 1,
        ];

        $this->actingAs($admin)
            ->post(route('admin.world-events.store'), $payload)
            ->assertRedirect(route('admin.world-events.index'));

        $this->assertDatabaseHas('world_events', [
            'name' => 'Logo URL World Event',
            'logo_path' => 'https://example.com/logo.webp',
        ]);
    }

    private function createVenueAdmin(): User
    {
        $role = Role::firstOrCreate(['title' => 'Venue Admin']);
        Permission::insertOrIgnore([
            ['title' => 'venue_access'],
        ]);

        $permissionIds = Permission::whereIn('title', ['venue_access'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $user = User::factory()->create();
        $user->roles()->sync([$role->id]);

        return $user;
    }
}
