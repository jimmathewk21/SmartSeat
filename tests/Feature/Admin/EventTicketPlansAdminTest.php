<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\EventTicketPlan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventTicketPlansAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_create_event_with_multiple_ticket_plans(): void
    {
        $role = Role::create(['title' => 'Event Admin']);
        Permission::insertOrIgnore([
            ['title' => 'event_access'],
            ['title' => 'event_create'],
            ['title' => 'event_edit'],
            ['title' => 'event_show'],
            ['title' => 'event_delete'],
        ]);
        $permissionIds = Permission::whereIn('title', [
            'event_access',
            'event_create',
            'event_edit',
            'event_show',
            'event_delete',
        ])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $payload = [
            'title' => 'AI Summit 2026',
            'slug' => 'ai-summit-2026',
            'venue' => 'Mumbai',
            'start_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(11)->format('Y-m-d H:i:s'),
            'description' => 'Conference description',
            'ticket_hero_label' => 'VIP BOOKING',
            'ticket_hero_suffix' => '/ book your pass',
            'ticket_hero_description' => 'Pick the best package, review what is included, and continue to checkout.',
            'is_published' => '1',
            'sort_order' => 1,
            'plans' => [
                ['name' => 'Economy', 'mrp_inr' => '399', 'sale_inr' => '299', 'mrp_usd' => '5.99', 'sale_usd' => '4.99', 'benefits' => "A\nB", 'valid_till_text' => 'Valid till June 30', 'is_active' => '1', 'sort_order' => 1],
                ['name' => 'Prime', 'mrp_inr' => '599', 'sale_inr' => '499', 'mrp_usd' => '7.99', 'sale_usd' => '6.99', 'benefits' => "C\nD", 'valid_till_text' => 'Valid till June 30', 'is_active' => '1', 'sort_order' => 2],
                ['name' => 'Premium (VIP)', 'mrp_inr' => '799', 'sale_inr' => '699', 'mrp_usd' => '9.99', 'sale_usd' => '8.99', 'benefits' => "E\nF", 'valid_till_text' => 'Valid till June 30', 'is_active' => '1', 'sort_order' => 3],
                ['name' => 'Super VIP', 'mrp_inr' => '999', 'sale_inr' => '899', 'mrp_usd' => '12.99', 'sale_usd' => '11.99', 'benefits' => "G\nH", 'valid_till_text' => 'Valid till June 30', 'is_active' => '1', 'sort_order' => 4],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.events.store'), $payload)
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', [
            'slug' => 'ai-summit-2026',
            'ticket_hero_label' => 'VIP BOOKING',
            'ticket_hero_suffix' => '/ book your pass',
            'ticket_hero_description' => 'Pick the best package, review what is included, and continue to checkout.',
            'is_published' => 1,
            'is_active' => 1,
        ]);
        $eventId = DB::table('events')->where('slug', 'ai-summit-2026')->value('id');

        $this->assertDatabaseHas('event_ticket_plans', [
            'event_id' => $eventId,
            'name' => 'Economy',
            'sale_inr_paise' => 29900,
            'sale_usd_cents' => 499,
        ]);
        $this->assertDatabaseHas('event_ticket_plans', [
            'event_id' => $eventId,
            'name' => 'Prime',
            'sale_inr_paise' => 49900,
            'sale_usd_cents' => 699,
        ]);
        $this->assertDatabaseHas('event_ticket_plans', [
            'event_id' => $eventId,
            'name' => 'Premium (VIP)',
            'sale_inr_paise' => 69900,
            'sale_usd_cents' => 899,
        ]);
        $this->assertDatabaseHas('event_ticket_plans', [
            'event_id' => $eventId,
            'name' => 'Super VIP',
            'sale_inr_paise' => 89900,
            'sale_usd_cents' => 1199,
        ]);
    }

    public function test_admin_can_make_existing_ticket_plan_inactive(): void
    {
        $role = Role::create(['title' => 'Event Editor']);
        Permission::insertOrIgnore([
            ['title' => 'event_access'],
            ['title' => 'event_edit'],
        ]);
        $permissionIds = Permission::whereIn('title', [
            'event_access',
            'event_edit',
        ])->pluck('id');
        $role->permissions()->sync($permissionIds);

        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        $event = Event::factory()->create([
            'title' => 'Edit Plan Event',
            'slug' => 'edit-plan-event',
            'is_published' => true,
            'is_active' => true,
        ]);
        $plan = EventTicketPlan::factory()->create([
            'event_id' => $event->id,
            'name' => 'Premium',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $payload = [
            'title' => $event->title,
            'slug' => $event->slug,
            'venue' => $event->venue,
            'start_at' => $event->start_at->format('Y-m-d H:i:s'),
            'end_at' => $event->end_at->format('Y-m-d H:i:s'),
            'description' => $event->description,
            'ticket_hero_label' => $event->ticket_hero_label,
            'ticket_hero_suffix' => $event->ticket_hero_suffix,
            'ticket_hero_description' => $event->ticket_hero_description,
            'is_published' => '1',
            'sort_order' => $event->sort_order,
            'plans' => [
                [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'background_image_url' => '',
                    'mrp_inr' => '349.00',
                    'sale_inr' => '299.00',
                    'mrp_usd' => '4.99',
                    'sale_usd' => '3.99',
                    'benefits' => $plan->benefits,
                    'valid_till_text' => $plan->valid_till_text,
                    'is_active' => '0',
                    'sort_order' => '1',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), $payload)
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('event_ticket_plans', [
            'id' => $plan->id,
            'event_id' => $event->id,
            'is_active' => 0,
        ]);
    }
}
