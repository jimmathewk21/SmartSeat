<?php

namespace Tests\Unit;

use App\Services\Intelligence\LogisticRegression;
use App\Services\Intelligence\PredictionMetrics;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LogisticRegressionTest extends TestCase
{
    public function test_original_learner_fits_binary_arithmetic_fixtures_without_external_dependencies(): void
    {
        // Ephemeral arithmetic regression fixtures only, never a historical training dataset.
        $learner = new LogisticRegression;
        $fit = $learner->fit([[-3], [-2], [-1], [1], [2], [3]], [0, 0, 0, 1, 1, 1]);
        $this->assertLessThan(0.5, $learner->predict($fit, [-2]));
        $this->assertGreaterThan(0.5, $learner->predict($fit, [2]));
        $this->assertEqualsWithDelta(0, $fit['means'][0], 1.0e-8);
        $this->assertSame($fit, $learner->fit([[-3], [-2], [-1], [1], [2], [3]], [0, 0, 0, 1, 1, 1]));
    }

    public function test_invalid_model_values_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new LogisticRegression)->predict(['means' => [0], 'scales' => [0], 'weights' => [0, NAN]], [1]);
    }

    public function test_brier_log_loss_and_calibration_are_real_evaluation_metrics(): void
    {
        $metrics = new PredictionMetrics;
        $result = $metrics->evaluate([0, 1], [0.1, 0.9]);
        $this->assertEqualsWithDelta(0.01, $result['brier'], 1.0e-8);
        $this->assertEqualsWithDelta(-log(0.9), $result['log_loss'], 1.0e-8);
        $this->assertFalse($result['calibration']['available']);
        $this->assertNull($result['calibration']['ece']);
        $this->expectException(InvalidArgumentException::class);
        $metrics->evaluate([1], [1.2]);
    }
}
