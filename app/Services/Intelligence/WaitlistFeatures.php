<?php

namespace App\Services\Intelligence;

use App\Models\Booking;
use App\Models\WaitlistPredictionSnapshot;
use App\Support\BookingRules;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class WaitlistFeatures
{
    public function __construct(private WaitlistHistory $history) {}

    /** Called inside the existing booking transaction after the group joins the queue. */
    public function capture(Booking $booking): void
    {
        if (! Config::get('smartseat.intelligence.capture_waitlist_history') || ! Schema::hasTable('waitlist_prediction_snapshots')) {
            return;
        }
        $now = CarbonImmutable::now('UTC');
        $trip = $booking->trip;
        $stop = $trip->train->routeStops()->where('stop_order', $booking->boarding_order)->firstOrFail();
        $departure = CarbonImmutable::parse($trip->service_date->toDateString(), Config::get('smartseat.railway_timezone'))
            ->addMinutes($stop->departure_offset_minutes);
        $class = $booking->travel_class;
        $people = $booking->passengers()->orderBy('id')->get();
        $firstEntry = $trip->waitlistEntries()->whereIn('passenger_id', $people->modelKeys())->orderBy('created_at')->orderBy('id')->firstOrFail();
        $ahead = $trip->waitlistEntries()->where('travel_class', $class)->where('status', 'waiting')
            ->where(function ($query) use ($firstEntry) {
                $query->where('created_at', '<', $firstEntry->created_at)
                    ->orWhere(fn ($tied) => $tied->where('created_at', $firstEntry->created_at)->where('id', '<', $firstEntry->id));
            })->count();
        $inventory = $trip->inventory()->whereHas('seat.coach', fn ($query) => $query->where('travel_class', $class));
        $capacity = $trip->train->seats()->whereHas('coach', fn ($query) => $query->where('travel_class', $class))->count();
        $history = $this->history->rows($now, $trip->train_id, $class, 500);
        $n = count($history);
        $weekday = (int) $departure->isoWeekday();
        $features = [
            'lead_log_hours' => log1p(max(0, ($departure->timestamp - $now->timestamp) / 3600)),
            'queue_log_people' => log1p($ahead), 'group_size' => $people->count(),
            'capacity_log' => log1p($capacity),
            'occupied_log' => log1p((clone $inventory)->whereHas('activeAssignment')->count()),
            'blocked_log' => log1p((clone $inventory)->where('status', 'blocked')->count()),
            'accessible_required' => $people->where('accessible_seat_required', true)->count(),
            'lower_preference' => $people->whereIn('preferred_berth', ['lower', 'side_lower'])->count(),
            'keep_together' => (int) $booking->keep_group_together,
            'history_log_count' => log1p($n),
            // Zero with a zero history count means missing evidence, not a displayed probability.
            'promotion_rate' => $n ? array_sum(array_column($history, 'label')) / $n : 0,
            'cancellation_rate' => $n ? array_sum(array_column($history, 'cancelled')) / $n : 0,
            'weekday_sin' => sin(2 * M_PI * $weekday / 7),
            'weekday_cos' => cos(2 * M_PI * $weekday / 7),
        ];
        foreach (BookingRules::CLASSES as $category) {
            $features['class_'.$category] = (int) ($class === $category);
        }
        WaitlistPredictionSnapshot::firstOrCreate(['booking_id' => $booking->id], [
            'train_id' => $trip->train_id, 'travel_class' => $class, 'feature_version' => FeatureVector::VERSION,
            'observed_at' => $now, 'boarding_departure_at' => $departure->utc(), 'features' => $features,
        ]);
    }
}
