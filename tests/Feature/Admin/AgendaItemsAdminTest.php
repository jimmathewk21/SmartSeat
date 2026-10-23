<?php

namespace Tests\Feature\Admin;

use App\Models\AgendaDay;
use App\Models\AgendaItem;
use App\Models\AgendaRoom;
use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AgendaItemsAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_agenda_sessions_default_to_descending_id_order(): void
    {
        $admin = $this->eventAdminUser();
        $event = Event::factory()->create([
            'title' => 'Agenda Ordering Event',
        ]);

        $day = AgendaDay::create([
            'event_id' => $event->id,
            'title' => 'Day 1',
            'weekday' => 'Monday',
            'agenda_date' => now()->toDateString(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $room = AgendaRoom::create([
            'event_id' => $event->id,
            'name' => 'Main Hall',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'Older Agenda Session',
            'start_time' => '09:00 AM',
            'end_time' => '10:00 AM',
            'panel_theme' => 'Strategy',
            'is_active' => true,
        ]);

        AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'Newer Agenda Session',
            'start_time' => '10:15 AM',
            'end_time' => '11:00 AM',
            'panel_theme' => 'Execution',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.agenda.items.index'));

        $response->assertOk()
            ->assertSeeInOrder([
                'Newer Agenda Session',
                'Older Agenda Session',
            ]);
    }

    public function test_admin_can_upload_supported_agenda_files(): void
    {
        $admin = $this->eventAdminUser();
        $event = Event::factory()->create([
            'title' => 'Agenda Upload Event',
        ]);

        $day = AgendaDay::create([
            'event_id' => $event->id,
            'title' => 'Day 1',
            'weekday' => 'Monday',
            'agenda_date' => now()->toDateString(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $room = AgendaRoom::create([
            'event_id' => $event->id,
            'name' => 'Main Hall',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $payload = [
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'File Upload Session',
            'topic_description' => 'Session material uploads',
            'start_time' => '09:00 AM',
            'end_time' => '10:00 AM',
            'panel_theme' => 'Assets',
            'moderator_name' => 'Moderator One',
            'moderator_speaker_id' => null,
            'moderator_ppt_url' => '',
            'moderator_ppt_file' => UploadedFile::fake()->image('moderator.jpg'),
            'agenda_file_url' => '',
            'agenda_file' => UploadedFile::fake()->create('agenda.pptx', 120),
            'sort_order' => 1,
            'is_active' => '1',
            'speaker_ids' => [],
        ];

        $response = $this->actingAs($admin)->post(route('admin.agenda.items.store'), $payload);

        $response->assertRedirect(route('admin.agenda.items.index'));

        $item = AgendaItem::query()->latest('id')->first();

        $this->assertNotNull($item);
        $this->assertNotNull($item->moderator_ppt_url);
        $this->assertNotNull($item->agenda_file_url);
        $this->assertStringEndsWith('.jpg', $item->moderator_ppt_url);
        $this->assertStringEndsWith('.pptx', $item->agenda_file_url);
        $this->assertFileExists(public_path($item->moderator_ppt_url));
        $this->assertFileExists(public_path($item->agenda_file_url));

        @unlink(public_path($item->moderator_ppt_url));
        @unlink(public_path($item->agenda_file_url));
    }

    public function test_admin_rejects_unsupported_agenda_file_types(): void
    {
        $admin = $this->eventAdminUser();
        $event = Event::factory()->create([
            'title' => 'Agenda Validation Event',
        ]);

        $day = AgendaDay::create([
            'event_id' => $event->id,
            'title' => 'Day 1',
            'weekday' => 'Monday',
            'agenda_date' => now()->toDateString(),
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $room = AgendaRoom::create([
            'event_id' => $event->id,
            'name' => 'Main Hall',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $payload = [
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'Invalid File Session',
            'topic_description' => 'Session material uploads',
            'start_time' => '09:00 AM',
            'end_time' => '10:00 AM',
            'panel_theme' => 'Assets',
            'moderator_name' => 'Moderator One',
            'moderator_speaker_id' => null,
            'moderator_ppt_url' => '',
            'moderator_ppt_file' => UploadedFile::fake()->create('agenda.exe', 10),
            'agenda_file_url' => '',
            'agenda_file' => null,
            'sort_order' => 1,
            'is_active' => '1',
            'speaker_ids' => [],
        ];

        $this->actingAs($admin)
            ->post(route('admin.agenda.items.store'), $payload)
            ->assertSessionHasErrors(['moderator_ppt_file']);
    }

    private function eventAdminUser(array $extraPermissions = []): User
    {
        $role = Role::create(['title' => 'Agenda Session Admin']);
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
