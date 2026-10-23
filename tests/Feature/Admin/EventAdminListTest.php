<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\EventTicketPlan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventAdminListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_filter_events_by_location_and_sort_by_location(): void
    {
        $admin = $this->eventAdminUser();

        Event::factory()->create([
            'title' => 'Singapore Event',
            'slug' => 'singapore-event',
            'location' => 'Singapore',
            'sort_order' => 2,
        ]);

        Event::factory()->create([
            'title' => 'Bangkok Event',
            'slug' => 'bangkok-event',
            'location' => 'Bangkok',
            'sort_order' => 1,
        ]);

        $filteredResponse = $this->actingAs($admin)
            ->get(route('admin.events.index', [
                'location' => 'Singapore',
            ]));

        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Singapore Event');
        $filteredResponse->assertDontSee('Bangkok Event');

        $sortedResponse = $this->actingAs($admin)
            ->get(route('admin.events.index', [
                'sort_by' => 'location',
                'sort_dir' => 'asc',
            ]));

        $sortedResponse->assertOk();
        $content = $sortedResponse->getContent();
        $this->assertNotFalse($content);
        $this->assertLessThan(
            strpos($content, 'Singapore Event'),
            strpos($content, 'Bangkok Event')
        );
    }

    public function test_admin_event_list_shows_buy_ticket_link_for_bookable_event_with_active_plan(): void
    {
        $admin = $this->eventAdminUser();

        $event = Event::factory()->create([
            'title' => 'Ticketed Event',
            'slug' => 'ticketed-event',
            'is_published' => true,
            'is_active' => true,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(6),
        ]);

        EventTicketPlan::factory()->create([
            'event_id' => $event->id,
            'is_active' => true,
            'sale_inr_paise' => 29900,
            'sale_usd_cents' => 499,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee(route('frontend.events.tickets', ['event' => $event->slug]), false)
            ->assertSee('Open');
    }

    public function test_event_slug_is_normalized_when_created(): void
    {
        $admin = $this->eventAdminUser([
            'event_create',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Slug Event',
                'slug' => 'Slug Event 2027',
                'venue' => 'Mumbai',
                'location' => 'India',
                'start_at' => now()->addDays(12)->format('Y-m-d H:i:s'),
                'end_at' => now()->addDays(13)->format('Y-m-d H:i:s'),
                'description' => 'Normalized slug check',
                'is_published' => '0',
                'sort_order' => 1,
                'plans' => [],
            ])
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'title' => 'Slug Event',
            'slug' => 'slug-event-2027',
        ]);
    }

    /**
     * @param array<int, string> $extraPermissions
     */
    private function eventAdminUser(array $extraPermissions = []): User
    {
        $role = Role::create(['title' => 'Event Admin List']);
        $permissionTitles = array_values(array_unique(array_merge([
            'event_access',
            'event_create',
            'event_edit',
            'event_show',
            'event_delete',
        ], $extraPermissions)));

        Permission::insertOrIgnore(array_map(fn (string $title) => ['title' => $title], $permissionTitles));
        $permissionIds = Permission::whereIn('title', $permissionTitles)->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        return $admin;
    }
}
