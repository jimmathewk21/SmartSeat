<?php

namespace App\Services\Intelligence;

use App\Models\WaitlistPredictionSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class WaitlistHistory
{
    /** Labels use only real audit events visible by asOf; features are immutable join snapshots. */
    public function rows(CarbonImmutable $asOf, ?int $trainId = null, ?string $class = null, ?int $limit = null): array
    {
        if (! Schema::hasTable('waitlist_prediction_snapshots')) {
            return [];
        }
        $maturity = (int) Config::get('smartseat.intelligence.maturity_hours');
        $snapshots = WaitlistPredictionSnapshot::where('feature_version', FeatureVector::VERSION)
            ->where('boarding_departure_at', '<=', $asOf->utc()->subHours($maturity)->toDateTimeString())
            ->when($trainId, fn ($query) => $query->where('train_id', $trainId)->where('travel_class', $class))
            ->with(['booking.events' => fn ($query) => $query->where('created_at', '<=', $asOf->utc()->toDateTimeString())->orderBy('id')])
            ->orderByDesc('observed_at')->orderByDesc('id')
            ->limit($limit ?? (int) Config::get('smartseat.intelligence.max_rows'))->get();
        $rows = [];
        foreach ($snapshots as $snapshot) {
            if (! FeatureVector::valid($snapshot->features) || $snapshot->observed_at >= $snapshot->boarding_departure_at) {
                continue;
            }
            $events = $snapshot->booking->events->filter(fn ($event) => $event->created_at >= $snapshot->observed_at && $event->created_at < $snapshot->boarding_departure_at);
            $promoted = $events->contains(fn ($event) => $event->from_status === 'waitlisted' && $event->to_status === 'confirmed');
            $cancelled = $events->contains(fn ($event) => $event->action === 'cancelled' && $event->from_status === 'waitlisted');
            $rows[] = [
                'id' => $snapshot->id, 'observed_at' => $snapshot->observed_at,
                'mature_at' => $snapshot->boarding_departure_at->addHours($maturity),
                'features' => $snapshot->features, 'label' => (int) $promoted, 'cancelled' => (int) $cancelled,
            ];
        }
        usort($rows, fn ($a, $b) => ($a['observed_at'] <=> $b['observed_at']) ?: ($a['id'] <=> $b['id']));

        return $rows;
    }
}
