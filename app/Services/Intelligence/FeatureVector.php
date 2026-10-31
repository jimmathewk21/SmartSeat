<?php

namespace App\Services\Intelligence;

use App\Support\BookingRules;

final class FeatureVector
{
    public const VERSION = 'waitlist-join-v1';

    public static function names(): array
    {
        return array_merge([
            'lead_log_hours', 'queue_log_people', 'group_size', 'capacity_log',
            'occupied_log', 'blocked_log', 'accessible_required', 'lower_preference',
            'keep_together', 'history_log_count', 'promotion_rate', 'cancellation_rate',
            'weekday_sin', 'weekday_cos',
        ], array_map(fn ($class) => 'class_'.$class, BookingRules::CLASSES));
    }

    public static function valid(mixed $features): bool
    {
        if (! is_array($features) || array_keys($features) !== self::names()) {
            return false;
        }
        foreach ($features as $value) {
            if ((! is_int($value) && ! is_float($value)) || ! is_finite((float) $value) || abs($value) > 10000) {
                return false;
            }
        }

        return $features['group_size'] >= 1
            && $features['promotion_rate'] >= 0 && $features['promotion_rate'] <= 1
            && $features['cancellation_rate'] >= 0 && $features['cancellation_rate'] <= 1;
    }
}
