<?php

namespace Tests\Feature\Api;

use App\Models\AgendaDay;
use App\Models\AgendaItem;
use App\Models\AgendaRoom;
use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AgendaApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_agenda_list_returns_active_event_days_and_items(): void
    {
        $event = Event::factory()->create([
            'title' => 'Summit 2026',
            'is_published' => true,
            'is_active' => true,
        ]);

        $day = AgendaDay::create([
            'event_id' => $event->id,
            'title' => 'Day 1',
            'weekday' => 'Wed',
            'agenda_date' => '2026-05-20',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $room = AgendaRoom::create([
            'event_id' => $event->id,
            'name' => 'Hall A',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $speaker = Speaker::factory()->create([
            'event_id' => $event->id,
            'name' => 'Ada Lovelace',
            'is_active' => true,
            'show_on_scrollbar' => true,
        ]);

        $item = AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'AI-Powered Future',
            'start_time' => '10:00 AM',
            'end_time' => '11:00 AM',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item->speakers()->sync([$speaker->id]);

        $this->getJson(route('api.v1.agenda.index'))
            ->assertOk()
            ->assertJsonPath('data.event.title', 'Summit 2026')
            ->assertJsonPath('data.days.0.title', 'Day 1')
            ->assertJsonPath('data.days.0.items.0.title', 'AI-Powered Future')
            ->assertJsonPath('data.speakers.0.name', 'Ada Lovelace');
    }

    public function test_agenda_detail_returns_a_session_payload(): void
    {
        $event = Event::factory()->create([
            'title' => 'Summit 2026',
            'is_published' => true,
            'is_active' => true,
        ]);

        $day = AgendaDay::create([
            'event_id' => $event->id,
            'title' => 'Day 1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $room = AgendaRoom::create([
            'event_id' => $event->id,
            'name' => 'Hall A',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $speaker = Speaker::factory()->create([
            'event_id' => $event->id,
            'name' => 'Ada Lovelace',
            'designation' => 'Mathematician',
            'company_name' => 'Analytical Engines',
            'is_active' => true,
        ]);

        $item = AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'AI-Powered Future',
            'sort_order' => 1,
            'is_active' => true,
            'moderator_speaker_id' => $speaker->id,
        ]);

        $item->speakers()->sync([$speaker->id]);

        $this->getJson(route('api.v1.agenda.show', $item))
            ->assertOk()
            ->assertJsonPath('data.title', 'AI-Powered Future')
            ->assertJsonPath('data.event.title', 'Summit 2026')
            ->assertJsonPath('data.day.title', 'Day 1')
            ->assertJsonPath('data.moderator_speaker.name', 'Ada Lovelace')
            ->assertJsonPath('data.speakers.0.name', 'Ada Lovelace');
    }

    public function test_inactive_agenda_item_returns_404(): void
    {
        $event = Event::factory()->create([
            'is_published' => true,
            'is_active' => true,
        ]);

        $day = AgendaDay::create([
            'event_id' => $event->id,
            'title' => 'Day 1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $room = AgendaRoom::create([
            'event_id' => $event->id,
            'name' => 'Hall A',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'Inactive Session',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $this->getJson(route('api.v1.agenda.show', $item))
            ->assertNotFound();
    }
}
