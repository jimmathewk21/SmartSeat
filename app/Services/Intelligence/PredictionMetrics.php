<?php

namespace App\Services\Intelligence;

use InvalidArgumentException;

final class PredictionMetrics
{
    public function evaluate(array $labels, array $probabilities): array
    {
        if ($labels === [] || count($labels) !== count($probabilities)) {
            throw new InvalidArgumentException('Metrics require matching nonempty observations.');
        }
        $brier = $logLoss = 0;
        $bins = array_fill(0, 5, ['count' => 0, 'prediction_sum' => 0, 'outcome_sum' => 0]);
        foreach ($labels as $i => $label) {
            $p = $probabilities[$i];
            if (! in_array($label, [0, 1], true) || (! is_int($p) && ! is_float($p)) || ! is_finite((float) $p) || $p < 0 || $p > 1) {
                throw new InvalidArgumentException('Invalid probability or outcome.');
            }
            $brier += ($p - $label) ** 2;
            $q = max(1.0e-10, min(1 - 1.0e-10, $p));
            $logLoss -= $label * log($q) + (1 - $label) * log(1 - $q);
            $bin = min(4, (int) floor($p * 5));
            $bins[$bin]['count']++;
            $bins[$bin]['prediction_sum'] += $p;
            $bins[$bin]['outcome_sum'] += $label;
        }
        $reliability = [];
        $ece = 0.0;
        foreach ($bins as $i => $bin) {
            if ($bin['count'] === 0) {
                continue;
            }
            $mean = $bin['prediction_sum'] / $bin['count'];
            $rate = $bin['outcome_sum'] / $bin['count'];
            $ece += $bin['count'] / count($labels) * abs($mean - $rate);
            $reliability[] = ['lower' => $i / 5, 'upper' => ($i + 1) / 5, 'count' => $bin['count'], 'mean_probability' => $mean, 'observed_rate' => $rate];
        }

        return ['count' => count($labels), 'brier' => $brier / count($labels), 'log_loss' => $logLoss / count($labels),
            'calibration' => ['available' => count($labels) >= 100, 'ece' => count($labels) >= 100 ? $ece : null, 'bins' => $reliability]];
    }
}
