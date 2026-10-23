<?php

namespace Tests\Feature\Api;

use App\Models\AgendaDay;
use App\Models\AgendaItem;
use App\Models\AgendaRoom;
use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SpeakerApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_speaker_list_returns_active_speakers_in_default_display_order(): void
    {
        $featured = Speaker::factory()->create([
            'name' => 'Featured Speaker',
            'is_active' => true,
            'is_key_figure' => true,
            'sort_order' => 25,
        ]);

        $regular = Speaker::factory()->create([
            'name' => 'Regular Speaker',
            'is_active' => true,
            'is_key_figure' => false,
            'sort_order' => 0,
        ]);

        Speaker::factory()->create([
            'name' => 'Inactive Speaker',
            'is_active' => false,
        ]);

        $response = $this->getJson(route('api.v1.speakers.index'));

        $response->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.id', $featured->id)
            ->assertJsonPath('data.1.id', $regular->id)
            ->assertJsonMissingPath('data.2');
    }

    public function test_speaker_list_applies_search_and_filters(): void
    {
        $nameMatch = Speaker::factory()->create([
            'name' => 'Aria Namefield',
            'company_name' => 'Northwind Labs',
            'designation' => 'Chief Strategy Officer',
            'email' => 'aria.namefield@example.com',
            'topic' => 'Future of work',
            'country' => 'USA',
            'category' => 'Keynote',
            'gender' => 'female',
            'speaker_role' => 'speaker',
            'is_key_figure' => true,
            'is_active' => true,
        ]);

        Speaker::factory()->create([
            'name' => 'Bora Companyfield',
            'company_name' => 'Company Search Co',
            'designation' => 'Innovation Lead',
            'email' => 'bora.companyfield@example.com',
            'topic' => 'Digital transformation',
            'country' => 'Canada',
            'category' => 'Panel',
            'gender' => 'male',
            'speaker_role' => 'moderator',
            'is_key_figure' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('api.v1.speakers.index', [
            'keyword' => 'Aria Namefield',
            'country' => 'USA',
            'category' => 'Keynote',
            'speaker_type' => 'female',
            'speaker_role' => 'speaker',
            'is_key_figure' => '1',
            'sort_by' => 'name',
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $nameMatch->id)
            ->assertJsonPath('data.0.country', 'USA')
            ->assertJsonPath('data.0.is_key_figure', true);
    }

    public function test_speaker_detail_returns_related_sessions_and_assets(): void
    {
        $event = Event::factory()->create([
            'title' => 'API Speaker Event',
        ]);

        $speaker = Speaker::factory()->create([
            'event_id' => $event->id,
            'name' => 'Detail Speaker',
            'bio' => '<p>Speaker bio</p>',
            'email' => 'detail@example.com',
            'phone' => '+1 555 0100',
            'linkedin_url' => 'https://linkedin.com/in/detail-speaker',
            'show_contact_details' => true,
            'resource_url' => 'uploads/speakers/bio.pdf',
            'advert_url' => 'uploads/speakers/ad.png',
            'presentation_file_url' => 'uploads/speakers/slides.pptx',
            'is_active' => true,
        ]);

        $day = AgendaDay::create([
            'event_id' => $event->id,
            'title' => 'Day 1',
            'weekday' => 'Tuesday',
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

        $speakerSession = AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'Speaker Session',
            'topic_description' => 'Speaker-led discussion',
            'start_time' => '09:00 AM',
            'end_time' => '10:00 AM',
            'panel_theme' => 'Strategy',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $speakerSession->speakers()->attach($speaker->id, ['sort_order' => 0]);

        AgendaItem::create([
            'event_id' => $event->id,
            'agenda_day_id' => $day->id,
            'agenda_room_id' => $room->id,
            'title' => 'Moderator Session',
            'topic_description' => 'Moderated discussion',
            'start_time' => '11:00 AM',
            'end_time' => '12:00 PM',
            'panel_theme' => 'Leadership',
            'moderator_speaker_id' => $speaker->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('api.v1.speakers.show', $speaker));

        $response->assertOk()
            ->assertJsonPath('data.id', $speaker->id)
            ->assertJsonPath('data.show_contact_details', true)
            ->assertJsonPath('data.email', 'detail@example.com')
            ->assertJsonPath('data.resource_url', '/uploads/speakers/bio.pdf')
            ->assertJsonPath('data.presentation_file_url', '/uploads/speakers/slides.pptx')
            ->assertJsonPath('data.related_sessions.0.title', 'Speaker Session')
            ->assertJsonPath('data.related_sessions.1.title', 'Moderator Session')
            ->assertJsonPath('data.related_sessions.1.role', 'Moderator');
    }

    public function test_inactive_speaker_detail_returns_404(): void
    {
        $speaker = Speaker::factory()->create([
            'is_active' => false,
        ]);

        $this->getJson(route('api.v1.speakers.show', $speaker))
            ->assertNotFound();
    }
}
