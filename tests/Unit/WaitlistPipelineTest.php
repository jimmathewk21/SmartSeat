<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\WaitlistPredictionSnapshot;
use App\Services\Intelligence\FeatureVector;
use App\Services\Intelligence\WaitlistDataset;
use App\Services\Intelligence\WaitlistHistory;
use App\Services\Intelligence\WaitlistModels;
use App\Services\Intelligence\WaitlistPrediction;
use App\Services\Intelligence\WaitlistTraining;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class WaitlistPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->beforeApplicationDestroyed(fn () => Storage::disk('local')->deleteDirectory('intelligence'));
    }

    private function features(float $signal): array
    {
        $features = array_fill_keys(FeatureVector::names(), 0);
        $features['lead_log_hours'] = $signal;
        $features['group_size'] = 1;
        $features['class_SL'] = 1;

        return $features;
    }

    public function test_chronological_split_purges_outcomes_unknown_at_validation_start(): void
    {
        config(['smartseat.intelligence.min_training_rows' => 2, 'smartseat.intelligence.min_validation_rows' => 2, 'smartseat.intelligence.min_class_rows' => 1]);
        $rows = [];
        foreach ([[1, 2, 0], [2, 3, 1], [3, 10, 0], [4, 10, 1], [5, 6, 0], [6, 7, 1]] as $i => [$day, $mature, $label]) {
            $rows[] = ['id' => $i, 'observed_at' => CarbonImmutable::parse('2024-01-'.$day), 'mature_at' => CarbonImmutable::parse('2024-01-'.$mature), 'label' => $label, 'features' => $this->features($label)];
        }
        $history = Mockery::mock(WaitlistHistory::class);
        $history->shouldReceive('rows')->once()->andReturn($rows);
        $split = (new WaitlistDataset($history))->split(CarbonImmutable::parse('2024-02-01'));
        $this->assertTrue($split['ready']);
        $this->assertSame([0, 1], array_column($split['train'], 'id'));
        $this->assertSame([4, 5], array_column($split['validation'], 'id'));
        $this->assertSame(2, $split['purged']);
    }

    private function publishArithmeticFixture(): array
    {
        // Isolated in-memory arithmetic fixtures only. No historical dataset is imported,
        // written or claimed genuine, and the resulting artifact exists on the fake disk only.
        $train = $validation = [];
        for ($i = 0; $i < 200; $i++) {
            $train[] = ['id' => $i, 'features' => $this->features($i % 2), 'label' => $i % 2];
        }
        for ($i = 0; $i < 100; $i++) {
            $validation[] = ['id' => 200 + $i, 'features' => $this->features($i % 2 ? 5 : 0), 'label' => $i % 2];
        }
        $dataset = Mockery::mock(WaitlistDataset::class);
        $dataset->shouldReceive('split')->once()->andReturn([
            'ready' => true, 'train' => $train, 'validation' => $validation, 'purged' => 5,
            'boundary' => CarbonImmutable::now('UTC')->subDays(2),
        ]);
        $this->app->instance(WaitlistDataset::class, $dataset);
        $result = app(WaitlistTraining::class)->train();
        $this->assertTrue($result['trained']);

        return app(WaitlistModels::class)->load();
    }

    public function test_training_persistence_versioning_and_optional_prediction_use_fake_storage_only(): void
    {
        $model = $this->publishArithmeticFixture();
        $this->assertSame(FeatureVector::VERSION, $model['feature_version']);
        $this->assertSame(200, $model['training_rows']);
        $this->assertSame(100, $model['validation_rows']);
        $this->assertEqualsWithDelta(0.5, $model['fit']['means'][0], 1.0e-8); // Validation has different mean.
        $this->assertTrue($model['metrics']['calibration']['available']);
        $this->assertLessThan($model['baseline']['brier'], $model['metrics']['brier']);
        $booking = new Booking(['status' => 'waitlisted']);
        $booking->setRelation('predictionSnapshot', new WaitlistPredictionSnapshot([
            'features' => $this->features(1), 'feature_version' => FeatureVector::VERSION,
            'observed_at' => now(), 'boarding_departure_at' => now()->addDays(3),
        ]));
        $result = app(WaitlistPrediction::class)->forBooking($booking);
        $this->assertTrue($result['available']);
        $this->assertGreaterThanOrEqual(0, $result['probability']);
        $this->assertLessThanOrEqual(1, $result['probability']);
        $this->assertSame($model['version'], $result['version']);
    }

    public function test_unknown_feature_version_or_corrupt_model_is_not_used(): void
    {
        $model = $this->publishArithmeticFixture();
        $model['feature_version'] = 'future-incompatible';
        $json = json_encode($model, JSON_THROW_ON_ERROR);
        $disk = Storage::disk('local');
        $disk->put('intelligence/waitlist/models/'.$model['version'].'.json', $json);
        $disk->put('intelligence/waitlist/active.json', json_encode(['version' => $model['version'], 'sha256' => hash('sha256', $json)]));
        $this->assertNull(app(WaitlistModels::class)->load());
        $disk->put('intelligence/waitlist/active.json', '{"version":"../../outside"}');
        $this->assertNull(app(WaitlistModels::class)->load());
    }

    public function test_invalid_features_and_single_outcome_history_are_not_trainable(): void
    {
        $this->assertFalse(FeatureVector::valid($this->features(NAN)));
        $this->assertFalse(FeatureVector::valid(['group_size' => 1]));
        config(['smartseat.intelligence.min_training_rows' => 1, 'smartseat.intelligence.min_validation_rows' => 1, 'smartseat.intelligence.min_class_rows' => 1]);
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[] = ['id' => $i, 'observed_at' => CarbonImmutable::parse('2024-01-01')->addDays($i * 3), 'mature_at' => CarbonImmutable::parse('2024-01-02')->addDays($i * 3), 'features' => $this->features(0), 'label' => 0];
        }
        $history = Mockery::mock(WaitlistHistory::class);
        $history->shouldReceive('rows')->andReturn($rows);
        $this->assertFalse((new WaitlistDataset($history))->split(CarbonImmutable::parse('2024-03-01'))['ready']);
    }

    public function test_insufficient_retraining_preserves_model_and_stale_or_uncovered_states_abstain(): void
    {
        $model = $this->publishArithmeticFixture();
        $dataset = Mockery::mock(WaitlistDataset::class);
        $dataset->shouldReceive('split')->once()->andReturn(['ready' => false]);
        $this->app->instance(WaitlistDataset::class, $dataset);
        $this->assertFalse(app(WaitlistTraining::class)->train()['trained']);
        $this->assertSame($model['version'], app(WaitlistModels::class)->load()['version']);
        $booking = new Booking(['status' => 'waitlisted']);
        $booking->setRelation('predictionSnapshot', new WaitlistPredictionSnapshot([
            'features' => $this->features(99), 'feature_version' => FeatureVector::VERSION,
            'observed_at' => now(), 'boarding_departure_at' => now()->addDays(3),
        ]));
        $this->assertFalse(app(WaitlistPrediction::class)->forBooking($booking)['available']);
        $this->travel(91)->days();
        $prediction = app(WaitlistPrediction::class)->forBooking($booking);
        $this->assertFalse($prediction['available']);
        $this->assertArrayNotHasKey('probability', $prediction);
    }
}
