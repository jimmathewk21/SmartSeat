<?php

namespace Tests\Feature\Frontend;

use App\Models\AgendaDay;
use App\Models\AgendaItem;
use App\Models\AgendaRoom;
use App\Models\Event;
use App\Models\User;
use App\Support\FrontendPageAccessSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AgendaPageOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_agenda_page_shows_latest_agenda_items_first(): void
    {
        FrontendPageAccessSettings::set(FrontendPageAccessSettings::defaults());

        $user = User::factory()->create();
        $event = Event::factory()->create([
            'title' => 'Agenda Ordering Event',
            'is_active' => true,
            'is_published' => true,
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

        $older = AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'Older Session',
            'topic_description' => 'Older session description',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $newer = AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'Newer Session',
            'topic_description' => 'Newer session description',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('frontend.agenda.index', [
                'event_id' => $event->id,
                'day_id' => $day->id,
            ]))
            ->assertOk()
            ->assertSeeInOrder([
                $newer->title,
                $older->title,
            ]);
    }
}
