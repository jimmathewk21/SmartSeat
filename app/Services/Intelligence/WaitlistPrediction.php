<?php

namespace App\Services\Intelligence;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Throwable;

class WaitlistPrediction
{
    public const UNAVAILABLE = 'Prediction unavailable — insufficient historical data.';

    public function __construct(private WaitlistModels $models, private LogisticRegression $learner) {}

    public function forBooking(Booking $booking): array
    {
        $unavailable = ['available' => false, 'message' => self::UNAVAILABLE];
        if ($booking->status !== 'waitlisted' || ! ($model = $this->models->load())) {
            return $unavailable;
        }
        try {
            if (CarbonImmutable::parse($model['trained_at'])->isFuture()
                || CarbonImmutable::parse($model['trained_at'])->lt(CarbonImmutable::now('UTC')->subDays((int) Config::get('smartseat.intelligence.max_model_days')))
                || $model['training_rows'] < Config::get('smartseat.intelligence.min_training_rows')
                || $model['validation_rows'] < Config::get('smartseat.intelligence.min_validation_rows')) {
                return ['available' => false, 'message' => 'Prediction unavailable — model needs retraining.'];
            }
            $snapshot = $booking->predictionSnapshot;
            if (! $snapshot || $snapshot->feature_version !== FeatureVector::VERSION || ! FeatureVector::valid($snapshot->features)
                || $snapshot->boarding_departure_at <= CarbonImmutable::now('UTC')) {
                return $unavailable;
            }
            foreach (array_values($snapshot->features) as $index => $value) {
                if ($value < $model['fit']['minimums'][$index] - 1.0e-8 || $value > $model['fit']['maximums'][$index] + 1.0e-8) {
                    return ['available' => false, 'message' => 'Prediction unavailable — this queue state is outside the model’s historical coverage.'];
                }
            }
            $p = $this->learner->predict($model['fit'], array_values($snapshot->features));
            if (! is_finite($p) || $p < 0 || $p > 1) {
                return $unavailable;
            }

            return ['available' => true, 'probability' => $p, 'version' => $model['version'],
                'observed_at' => $snapshot->observed_at, 'message' => 'Estimate from your queue state at joining, using historical outcomes. Not a guarantee.'];
        } catch (Throwable) {
            return $unavailable;
        }
    }
}
