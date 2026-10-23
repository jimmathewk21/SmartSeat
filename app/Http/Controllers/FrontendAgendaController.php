<?php

namespace App\Http\Controllers;

use App\Models\AgendaDay;
use App\Models\AgendaItem;
use App\Models\Event;
use App\Models\Speaker;
use App\Support\AgendaPageSettings;
use Illuminate\Http\Request;

class FrontendAgendaController extends Controller
{
    public function index(Request $request)
    {
        $settings = AgendaPageSettings::get();

        $eventId = $request->integer('event_id');
        if (! $eventId && ! empty($settings['event_id'])) {
            $eventId = (int) $settings['event_id'];
        }

        $eventQuery = Event::query()
            ->published();

        if ($eventId) {
            $event = (clone $eventQuery)->where('id', $eventId)->first();
        } else {
            $event = null;
        }

        if (! $event) {
            $event = (clone $eventQuery)->orderByDesc('id')->first();
        }

        $days = collect();
        $activeDayId = null;
        $rooms = collect();
        $items = collect();

        if ($event) {
            $days = AgendaDay::query()
                ->where('event_id', $event->id)
                ->where('is_active', true)
                ->orderByDesc('id')
                ->get();

            $activeDayId = $request->integer('day_id');
            if (! $activeDayId || ! $days->contains('id', $activeDayId)) {
                $activeDayId = $days->first()->id ?? null;
            }

            $rooms = $event->agendaRooms()
                ->where('is_active', true)
                ->orderByDesc('id')
                ->get();

            if ($activeDayId) {
                $items = AgendaItem::query()
                    ->with(['room', 'speakers', 'moderatorSpeaker'])
                    ->where('event_id', $event->id)
                    ->where('agenda_day_id', $activeDayId)
                    ->where('is_active', true)
                    ->orderByDesc('id')
                    ->get();
            }
        }

        $speakers = Speaker::query()
            ->where('is_active', true)
            ->where('show_on_scrollbar', true)
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('frontend.agenda.index', compact('settings', 'event', 'days', 'activeDayId', 'rooms', 'items', 'speakers'));
    }
}
