<?php

namespace Tests\Feature\Api;

use App\Models\AgendaDay;
use App\Models\AgendaItem;
use App\Models\AgendaRoom;
use App\Models\DownloadMediaItem;
use App\Models\Event;
use App\Models\Speaker;
use App\Models\WorldEvent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_downloads_api_returns_grouped_sections(): void
    {
        DownloadMediaItem::create([
            'section_title' => 'Logo White Background',
            'item_title' => 'Summit Logo',
            'item_subtitle' => 'Primary logo pack',
            'image_url' => 'uploads/downloads/logo.png',
            'primary_button_text' => 'PDF',
            'primary_file_url' => 'uploads/downloads/logo.pdf',
            'secondary_button_text' => 'PNG',
            'secondary_file_url' => 'uploads/downloads/logo.png',
            'is_grouped_versions' => true,
            'version_1_label' => 'Version 1',
            'version_1_image_url' => 'uploads/downloads/logo-v1.png',
            'version_1_pdf_url' => 'uploads/downloads/logo-v1.pdf',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        DownloadMediaItem::create([
            'section_title' => 'Floor Plan',
            'item_title' => 'Venue Floor Plan',
            'item_subtitle' => 'Main hall layout',
            'image_url' => 'uploads/downloads/floor.png',
            'primary_button_text' => 'PDF',
            'primary_file_url' => 'uploads/downloads/floor.pdf',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->getJson(route('api.v1.downloads.index'))
            ->assertOk()
            ->assertJsonPath('data.sections.0.title', 'Logo White Background')
            ->assertJsonPath('data.sections.0.layout', 'logo')
            ->assertJsonPath('data.sections.0.items.0.primary_file_url', '/uploads/downloads/logo.pdf')
            ->assertJsonPath('data.sections.1.layout', 'wide');
    }

    public function test_world_events_api_returns_active_events(): void
    {
        WorldEvent::create([
            'name' => 'WIPO Summit',
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'city' => 'Geneva',
            'country' => 'Switzerland',
            'theme' => 'IP Strategy',
            'website' => 'https://wipo.example',
            'logo_path' => 'uploads/world-events/wipo.png',
            'stars' => 5,
            'is_active' => true,
        ]);

        WorldEvent::create([
            'name' => 'Inactive Summit',
            'start_date' => '2026-05-20',
            'end_date' => '2026-05-21',
            'city' => 'Paris',
            'country' => 'France',
            'theme' => 'Should not appear',
            'website' => 'https://inactive.example',
            'stars' => 3,
            'is_active' => false,
        ]);

        $this->getJson(route('api.v1.world-events.index'))
            ->assertOk()
            ->assertJsonPath('data.events.0.name', 'WIPO Summit')
            ->assertJsonPath('data.events.0.logo_url', '/uploads/world-events/wipo.png')
            ->assertJsonPath('data.stats.total_events', 1);
    }

    public function test_notifications_api_compiles_feed_items_from_content(): void
    {
        $event = Event::factory()->create([
            'title' => 'Summit 2026',
            'is_published' => true,
            'is_active' => true,
        ]);

        $room = AgendaRoom::create([
            'event_id' => $event->id,
            'name' => 'Main Hall',
            'sort_order' => 1,
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

        AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'AI-Powered Future',
            'topic_description' => 'Future trends',
            'start_time' => '10:00 AM',
            'end_time' => '11:00 AM',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Speaker::factory()->create([
            'name' => 'Ada Lovelace',
            'is_active' => true,
            'show_on_scrollbar' => true,
        ]);

        WorldEvent::create([
            'name' => 'IP Week',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-03',
            'city' => 'London',
            'country' => 'UK',
            'theme' => 'Innovation',
            'website' => 'https://ipweek.example',
            'stars' => 4,
            'is_active' => true,
        ]);

        DownloadMediaItem::create([
            'section_title' => 'Logo White Background',
            'item_title' => 'Summit Logo',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->getJson(route('api.v1.notifications.index'))
            ->assertOk()
            ->assertJsonPath('data.today_items.0.title', 'Session Reminder')
            ->assertJsonPath('data.today_items.1.title', 'Speaker Spotlight')
            ->assertJsonPath('data.today_items.2.title', 'World Event Added')
            ->assertJsonPath('data.today_items.3.title', 'Download Available');
    }
}
