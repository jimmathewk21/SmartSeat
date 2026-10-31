<?php

namespace App\Services\Intelligence;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;

class WaitlistTraining
{
    public function __construct(private WaitlistDataset $dataset, private LogisticRegression $learner, private PredictionMetrics $metrics, private WaitlistModels $models) {}

    public function train(): array
    {
        $asOf = CarbonImmutable::now('UTC');
        $split = $this->dataset->split($asOf);
        if (! $split['ready']) {
            return ['trained' => false, 'message' => WaitlistPrediction::UNAVAILABLE];
        }
        $train = $split['train'];
        $validation = $split['validation'];
        $fit = $this->learner->fit(array_map(fn ($row) => array_values($row['features']), $train), array_column($train, 'label'));
        $probabilities = array_map(fn ($row) => $this->learner->predict($fit, array_values($row['features'])), $validation);
        $labels = array_column($validation, 'label');
        $evaluation = $this->metrics->evaluate($labels, $probabilities);
        $baseRate = array_sum(array_column($train, 'label')) / count($train);
        $baseline = $this->metrics->evaluate($labels, array_fill(0, count($labels), $baseRate));
        $passed = $fit['gradient_norm'] < 0.01 && $evaluation['brier'] < $baseline['brier'] && $evaluation['log_loss'] < $baseline['log_loss']
            && $evaluation['calibration']['available']
            && $evaluation['calibration']['ece'] <= Config::get('smartseat.intelligence.max_calibration_error');
        if (! $passed) {
            return ['trained' => false, 'message' => 'Model withheld: held-out performance or calibration is insufficient.', 'metrics' => $evaluation, 'baseline' => $baseline];
        }
        $model = [
            'schema' => 1, 'version' => 'waitlist-v1-'.now('UTC')->format('YmdHis').'-'.bin2hex(random_bytes(6)),
            'algorithm' => LogisticRegression::ALGORITHM, 'feature_version' => FeatureVector::VERSION,
            'feature_names' => FeatureVector::names(), 'trained_at' => CarbonImmutable::now('UTC')->toIso8601String(),
            'data_cutoff' => $split['boundary']->toIso8601String(), 'evaluation_asof' => $asOf->toIso8601String(),
            'training_rows' => count($train), 'validation_rows' => count($validation),
            'purged_rows' => $split['purged'], 'metrics' => $evaluation, 'baseline' => $baseline,
            'dataset_sha256' => hash('sha256', json_encode(array_map(fn ($row) => [$row['id'], $row['features'], $row['label']], $train), JSON_THROW_ON_ERROR)),
            'fit' => $fit, 'quality_passed' => true,
        ];
        $this->models->save($model);

        return ['trained' => true, 'message' => 'Model trained and activated.', 'version' => $model['version']];
    }
}
