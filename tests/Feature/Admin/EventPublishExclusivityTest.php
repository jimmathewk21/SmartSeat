<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EventPublishExclusivityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_publishing_a_new_upcoming_event_unpublishes_other_current_or_upcoming_events(): void
    {
        $role = Role::create(['title' => 'Event Admin Exclusive']);
        Permission::insertOrIgnore([
            ['title' => 'event_access'],
            ['title' => 'event_create'],
            ['title' => 'event_edit'],
            ['title' => 'event_show'],
        ]);
        $permissionIds = Permission::whereIn('title', [
            'event_access',
            'event_create',
            'event_edit',
            'event_show',
        ])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Upcoming Event One',
            'slug' => 'upcoming-event-one',
            'venue' => 'Mumbai',
            'start_at' => now()->addDays(20)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(21)->format('Y-m-d H:i:s'),
            'description' => 'Event one',
            'image_url' => '',
            'gallery_url' => '',
            'brochure_url' => '',
            'is_published' => '1',
            'sort_order' => 1,
            'plans' => [[
                'name' => 'Economy',
                'mrp_inr' => '399',
                'sale_inr' => '299',
                'mrp_usd' => '5.99',
                'sale_usd' => '4.99',
                'benefits' => '',
                'valid_till_text' => '',
                'is_active' => '1',
                'sort_order' => 1,
            ]],
        ])->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'slug' => 'upcoming-event-one',
            'is_published' => 1,
            'is_active' => 1,
        ]);

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Upcoming Event Two',
            'slug' => 'upcoming-event-two',
            'venue' => 'Delhi',
            'start_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(31)->format('Y-m-d H:i:s'),
            'description' => 'Event two',
            'image_url' => '',
            'gallery_url' => '',
            'brochure_url' => '',
            'is_published' => '1',
            'sort_order' => 2,
            'plans' => [[
                'name' => 'Economy',
                'mrp_inr' => '499',
                'sale_inr' => '399',
                'mrp_usd' => '6.99',
                'sale_usd' => '5.99',
                'benefits' => '',
                'valid_till_text' => '',
                'is_active' => '1',
                'sort_order' => 1,
            ]],
        ])->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'slug' => 'upcoming-event-two',
            'is_published' => 1,
        ]);

        $this->assertDatabaseHas('events', [
            'slug' => 'upcoming-event-one',
            'is_published' => 0,
            'is_active' => 0,
        ]);

        $publishedUpcomingCount = DB::table('events')
            ->whereIn('slug', ['upcoming-event-one', 'upcoming-event-two'])
            ->where('is_published', true)
            ->count();

        $this->assertSame(1, $publishedUpcomingCount);
    }
}
