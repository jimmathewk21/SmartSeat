<?php

namespace Database\Seeders;

use App\Models\AgendaDay;
use App\Models\AgendaItem;
use App\Models\AgendaRoom;
use App\Models\Event;
use App\Models\Speaker;
use App\Support\AgendaPageSettings;
use Illuminate\Database\Seeder;

class AgendaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::query()->where('slug', 'ai-leadership-summit-2026')->first();

        if (! $event) {
            $event = Event::query()->updateOrCreate(
                ['slug' => 'agenda-demo-event'],
                [
                    'title' => 'AI Leadership Summit 2026',
                    'venue' => 'Innovation Hall, San Francisco',
                    'start_at' => now()->addDays(15)->setTime(9, 0),
                    'end_at' => now()->addDays(18)->setTime(18, 0),
                    'description' => 'Demo event for agenda testing.',
                    'image_url' => 'images/misc/l1.webp',
                    'gallery_url' => '/agenda',
                    'brochure_url' => 'uploads/speakers/speaker-presentation-20260302094614-brme28rb.pdf',
                    'is_published' => true,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );
        }

        $speakers = Speaker::query()->where('event_id', $event->id)->limit(8)->get();

        if ($speakers->count() < 12) {
            $countries = ['India', 'United States', 'Singapore', 'United Kingdom', 'UAE', 'Germany'];
            $categories = ['Key Speaker', 'Technology', 'Innovation', 'Leadership'];
            $needed = 12 - $speakers->count();
            $existingCount = $speakers->count();

            for ($i = 1; $i <= $needed; $i++) {
                $index = $existingCount + $i;

                Speaker::query()->updateOrCreate(
                    ['event_id' => $event->id, 'name' => 'Demo Speaker ' . $index],
                    [
                        'designation' => $index % 3 === 0 ? 'Moderator' : 'Panel Expert',
                        'company_name' => 'Demo Company ' . $index,
                        'country' => $countries[($index - 1) % count($countries)],
                        'bio' => 'Demo speaker bio for agenda testing.',
                        'topic' => 'AI Strategy and Enterprise Transformation',
                        'category' => $categories[($index - 1) % count($categories)],
                        'photo_url' => 'images/team/' . (($index % 9) + 1) . '.webp',
                        'presentation_file_url' => 'uploads/speakers/speaker-presentation-20260302094614-brme28rb.pdf',
                        'advert_url' => 'uploads/speakers/speaker-advert-20260302094614-illb9rtf.pdf',
                        'show_on_homepage' => $index <= 8,
                        'show_on_scrollbar' => true,
                        'is_active' => true,
                        'sort_order' => $index,
                    ]
                );
            }

            $speakers = Speaker::query()->where('event_id', $event->id)->orderBy('sort_order')->limit(12)->get();
        }

        $dayDefs = [
            ['title' => 'Day 1', 'weekday' => 'Tuesday', 'sort_order' => 1],
            ['title' => 'Day 2', 'weekday' => 'Wednesday', 'sort_order' => 2],
            ['title' => 'Day 3', 'weekday' => 'Thursday', 'sort_order' => 3],
        ];

        $days = collect();
        foreach ($dayDefs as $idx => $def) {
            $day = AgendaDay::query()->updateOrCreate(
                ['event_id' => $event->id, 'title' => $def['title']],
                [
                    'weekday' => $def['weekday'],
                    'agenda_date' => optional($event->start_at)->copy()->addDays($idx)->toDateString(),
                    'sort_order' => $def['sort_order'],
                    'is_active' => true,
                ]
            );
            $days->push($day);
        }

        $roomNames = ['Hall A - Innovation', 'Hall B - Leadership', 'Hall C - Data', 'Hall D - Strategy'];
        $rooms = collect();
        foreach ($roomNames as $index => $roomName) {
            $room = AgendaRoom::query()->updateOrCreate(
                ['event_id' => $event->id, 'name' => $roomName],
                ['sort_order' => $index + 1, 'is_active' => true]
            );
            $rooms->push($room);
        }

        $timeSlots = [
            ['09:00 AM', '09:45 AM'],
            ['10:00 AM', '10:45 AM'],
            ['11:15 AM', '12:00 PM'],
            ['02:00 PM', '02:45 PM'],
        ];
        $themes = [
            'Future of AI in Enterprise',
            'Practical GenAI Adoption',
            'Data Governance and Compliance',
            'AI, IP and Legal Innovation',
            'Responsible AI and Security',
        ];

        foreach ($days as $dayIndex => $day) {
            foreach ($rooms as $roomIndex => $room) {
                foreach ($timeSlots as $slotIndex => [$startTime, $endTime]) {
                    $sessionNumber = (($dayIndex * count($rooms) * count($timeSlots)) + ($roomIndex * count($timeSlots)) + $slotIndex + 1);
                    $sessionTitle = 'Session ' . $sessionNumber . ' - ' . $themes[$sessionNumber % count($themes)];
                    $moderator = $speakers->get(($dayIndex + $roomIndex + $slotIndex) % $speakers->count());
                    $panelists = $speakers->slice(($roomIndex + $slotIndex) % $speakers->count(), 3)->pluck('id')->values();

                    if ($panelists->count() < 3) {
                        $panelists = $speakers->take(3)->pluck('id')->values();
                    }

                    $item = AgendaItem::query()->updateOrCreate(
                        [
                            'event_id' => $event->id,
                            'agenda_day_id' => $day->id,
                            'agenda_room_id' => $room->id,
                            'title' => $sessionTitle,
                        ],
                        [
                            'topic_description' => 'How AI is transforming enterprises. Includes topics, notes, key takeaways, and downloadable session material.',
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'panel_theme' => $themes[$sessionNumber % count($themes)],
                            'moderator_name' => $moderator?->name,
                            'moderator_speaker_id' => $moderator?->id,
                            'moderator_ppt_url' => 'uploads/speakers/speaker-presentation-20260302094614-brme28rb.pdf',
                            'agenda_file_url' => 'uploads/speakers/speaker-presentation-20260302094720-iyv4lg6y.pdf',
                            'sort_order' => $slotIndex + 1,
                            'is_active' => true,
                        ]
                    );

                    $sync = [];
                    foreach ($panelists as $panelistIndex => $speakerId) {
                        $sync[$speakerId] = ['sort_order' => $panelistIndex + 1];
                    }
                    $item->speakers()->sync($sync);
                }
            }
        }

        AgendaPageSettings::set(array_merge(AgendaPageSettings::get(), [
            'event_id' => $event->id,
            'subtitle' => 'Schedule Details',
            'title' => 'Information of Event Schedules',
            'small_description' => 'Demo agenda data loaded with multiple days, rooms, moderators, speakers, and downloadable session files.',
            'agenda_download_text' => 'Download Agenda',
            'agenda_download_url' => 'uploads/speakers/speaker-presentation-20260302094720-iyv4lg6y.pdf',
        ]));
    }
}
