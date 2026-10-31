<?php

namespace App\Services;

use App\Models\TrainTrip;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class BookingDrafts
{
    public function __construct(private Session $session) {}

    public function create(User $user, TrainTrip $trip): string
    {
        $drafts = array_filter($this->session->get('booking_drafts', []), fn ($draft) => $draft['expires_at'] > now()->timestamp);
        $key = (string) Str::uuid();
        $drafts[$key] = [
            'owner_id' => $user->id, 'trip_id' => $trip->id,
            'expires_at' => now()->addMinutes((int) Config::get('smartseat.booking.draft_minutes'))->timestamp,
            'details' => null, 'review' => null,
        ];
        $this->session->put('booking_drafts', array_slice($drafts, -(int) Config::get('smartseat.booking.max_drafts'), null, true));

        return $key;
    }

    public function get(User $user, string $key): array
    {
        abort_unless(Str::isUuid($key), 404);
        $draft = $this->session->get('booking_drafts.'.$key);
        abort_unless($draft && $draft['owner_id'] === $user->id && $draft['expires_at'] > now()->timestamp, 404, 'This booking draft has expired. Start again from train search.');

        return $draft;
    }

    public function update(User $user, string $key, array $changes): void
    {
        $draft = $this->get($user, $key);
        $this->session->put('booking_drafts.'.$key, array_replace($draft, $changes));
    }
}
