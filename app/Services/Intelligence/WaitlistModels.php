<?php

namespace App\Services\Intelligence;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class WaitlistModels
{
    private const ROOT = 'intelligence/waitlist';

    public function load(): ?array
    {
        try {
            $disk = Storage::disk('local');
            if (! $disk->exists(self::ROOT.'/active.json')) {
                return null;
            }
            $pointer = json_decode($disk->get(self::ROOT.'/active.json'), true, 32, JSON_THROW_ON_ERROR);
            if (! $this->version($pointer['version'] ?? '')) {
                return null;
            }
            $path = self::ROOT.'/models/'.$pointer['version'].'.json';
            if (! $disk->exists($path) || $disk->size($path) > 1000000) {
                return null;
            }
            $json = $disk->get($path);
            if (! hash_equals($pointer['sha256'] ?? '', hash('sha256', $json))) {
                return null;
            }
            $model = json_decode($json, true, 64, JSON_THROW_ON_ERROR);

            return $this->valid($model) ? $model : null;
        } catch (Throwable) {
            return null; // Missing/corrupt/incompatible optional models never break booking.
        }
    }

    public function save(array $model): void
    {
        if (! $this->valid($model)) {
            throw new InvalidArgumentException('Invalid model artifact.');
        }
        $disk = Storage::disk('local');
        $disk->makeDirectory(self::ROOT.'/models');
        $path = self::ROOT.'/models/'.$model['version'].'.json';
        if ($disk->exists($path)) {
            throw new InvalidArgumentException('Model versions cannot be overwritten.');
        }
        $json = json_encode($model, JSON_THROW_ON_ERROR);
        // Same-directory atomic replacements; JSON only, never executable serialized PHP.
        File::replace($disk->path($path), $json);
        File::replace($disk->path(self::ROOT.'/active.json'), json_encode([
            'version' => $model['version'], 'sha256' => hash('sha256', $json),
        ], JSON_THROW_ON_ERROR));
    }

    public function valid(mixed $model): bool
    {
        try {
            if (! is_array($model)) {
                return false;
            }
            foreach (['data_cutoff', 'trained_at', 'evaluation_asof', 'fit', 'metrics', 'baseline'] as $required) {
                if (! isset($model[$required])) {
                    return false;
                }
            }
            if (! is_array($model) || ($model['schema'] ?? null) !== 1
                || ($model['algorithm'] ?? null) !== LogisticRegression::ALGORITHM
                || ($model['feature_version'] ?? null) !== FeatureVector::VERSION
                || ! $this->version($model['version'] ?? '')
                || ($model['quality_passed'] ?? null) !== true
                || ($model['feature_names'] ?? []) !== FeatureVector::names()
                || ($model['training_rows'] ?? 0) < 1 || ($model['validation_rows'] ?? 0) < 1
                || CarbonImmutable::parse($model['data_cutoff']) > CarbonImmutable::parse($model['trained_at'])
                || CarbonImmutable::parse($model['evaluation_asof']) > CarbonImmutable::parse($model['trained_at'])) {
                return false;
            }
            foreach (['brier', 'log_loss'] as $metric) {
                $value = $model['metrics'][$metric] ?? null;
                if ((! is_float($value) && ! is_int($value)) || ! is_finite((float) $value) || $value < 0) {
                    return false;
                }
            }
            $fit = $model['fit'];
            foreach (['weights', 'means', 'scales', 'minimums', 'maximums'] as $part) {
                if (! isset($fit[$part]) || ! is_array($fit[$part]) || ! array_is_list($fit[$part])) {
                    return false;
                }
            }
            if (count($fit['minimums']) !== count(FeatureVector::names()) || count($fit['maximums']) !== count(FeatureVector::names())) {
                return false;
            }
            foreach ($fit['minimums'] as $index => $minimum) {
                if (! is_numeric($minimum) || ! is_finite((float) $minimum)
                    || ! is_numeric($fit['maximums'][$index]) || ! is_finite((float) $fit['maximums'][$index])
                    || $minimum > $fit['maximums'][$index]) {
                    return false;
                }
            }
            $calibration = $model['metrics']['calibration'] ?? [];
            $ece = $calibration['ece'] ?? null;
            if (($calibration['available'] ?? null) !== true || ! is_numeric($ece)
                || ! is_finite((float) $ece) || $ece < 0 || $ece > 1
                || ! is_array($calibration['bins'] ?? null)
                || ! ($model['metrics']['brier'] < ($model['baseline']['brier'] ?? -1))
                || ! ($model['metrics']['log_loss'] < ($model['baseline']['log_loss'] ?? -1))) {
                return false;
            }
            foreach (array_merge($fit['weights'], $fit['means'], $fit['scales']) as $value) {
                if (! is_numeric($value) || ! is_finite((float) $value) || abs($value) > 1.0e8) {
                    return false;
                }
            }
            (new LogisticRegression)->predict($fit, array_fill(0, count(FeatureVector::names()), 0.0));

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function version(mixed $version): bool
    {
        return is_string($version) && preg_match('/\\Awaitlist-v1-[0-9]{14}-[a-f0-9]{12}\\z/', $version) === 1;
    }
}
