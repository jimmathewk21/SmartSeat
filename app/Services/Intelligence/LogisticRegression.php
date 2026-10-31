<?php

namespace App\Services\Intelligence;

use InvalidArgumentException;

final class LogisticRegression
{
    public const ALGORITHM = 'l2-logistic-php-v1';

    /** Original full-batch gradient descent with L2 regularization and train-only scaling. */
    public function fit(array $vectors, array $labels): array
    {
        $n = count($vectors);
        $d = count($vectors[0] ?? []);
        if ($n < 2 || $d === 0 || count($labels) !== $n || count(array_unique($labels)) !== 2) {
            throw new InvalidArgumentException('Training requires finite vectors and both binary outcomes.');
        }
        foreach ($vectors as $index => $row) {
            if (count($row) !== $d || ! in_array($labels[$index], [0, 1], true)) {
                throw new InvalidArgumentException('Invalid training shape or outcome.');
            }
            foreach ($row as $value) {
                if (! is_numeric($value) || ! is_finite((float) $value)) {
                    throw new InvalidArgumentException('Non-finite training input.');
                }
            }
        }
        $means = $scales = [];
        for ($j = 0; $j < $d; $j++) {
            $means[$j] = array_sum(array_column($vectors, $j)) / $n;
            $variance = 0;
            foreach ($vectors as $row) {
                $variance += ($row[$j] - $means[$j]) ** 2;
            }
            $scales[$j] = max(1.0e-8, sqrt($variance / $n));
        }
        $x = array_map(fn ($row) => $this->scale($row, $means, $scales), $vectors);
        $weights = array_fill(0, $d + 1, 0.0);
        $positive = array_sum($labels);
        $weights[0] = log(($positive + 0.5) / ($n - $positive + 0.5));
        $norm = INF;
        for ($epoch = 0; $epoch < 800; $epoch++) {
            $gradient = array_fill(0, $d + 1, 0.0);
            foreach ($x as $i => $row) {
                $error = $this->sigmoid($this->dot($row, $weights)) - $labels[$i];
                $gradient[0] += $error / $n;
                for ($j = 0; $j < $d; $j++) {
                    $gradient[$j + 1] += $error * $row[$j] / $n;
                }
            }
            $norm = 0.0;
            foreach ($weights as $j => $weight) {
                $g = $gradient[$j] + ($j === 0 ? 0 : 0.02 * $weight);
                $weights[$j] -= 0.1 * $g;
                $norm += $g * $g;
            }
            if (sqrt($norm) < 0.001) {
                break;
            }
        }

        $minimums = $maximums = [];
        for ($j = 0; $j < $d; $j++) {
            $minimums[] = min(array_column($vectors, $j));
            $maximums[] = max(array_column($vectors, $j));
        }

        return ['means' => $means, 'scales' => $scales, 'weights' => $weights, 'minimums' => $minimums, 'maximums' => $maximums,
            'epochs' => min($epoch + 1, 800), 'gradient_norm' => sqrt($norm)];
    }

    public function predict(array $fit, array $vector): float
    {
        if (count($vector) !== count($fit['means']) || count($fit['scales']) !== count($vector) || count($fit['weights']) !== count($vector) + 1) {
            throw new InvalidArgumentException('Prediction feature shape mismatch.');
        }
        foreach (array_merge($vector, $fit['means'], $fit['scales'], $fit['weights']) as $value) {
            if (! is_numeric($value) || ! is_finite((float) $value)) {
                throw new InvalidArgumentException('Non-finite model/input.');
            }
        }
        if (min($fit['scales']) <= 0) {
            throw new InvalidArgumentException('Invalid feature scales.');
        }

        return $this->sigmoid($this->dot($this->scale($vector, $fit['means'], $fit['scales']), $fit['weights']));
    }

    private function scale(array $row, array $means, array $scales): array
    {
        return array_map(fn ($value, $j) => ($value - $means[$j]) / $scales[$j], array_values($row), array_keys($means));
    }

    private function dot(array $row, array $weights): float
    {
        $sum = $weights[0];
        foreach ($row as $j => $value) {
            $sum += $value * $weights[$j + 1];
        }

        return $sum;
    }

    private function sigmoid(float $z): float
    {
        return 1 / (1 + exp(-max(-35, min(35, $z))));
    }
}
