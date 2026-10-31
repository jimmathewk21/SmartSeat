<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class WaitlistPredictionSnapshot extends Model
{
    protected $fillable = ['booking_id', 'train_id', 'travel_class', 'feature_version', 'observed_at', 'boarding_departure_at', 'features'];

    protected function casts(): array
    {
        return ['observed_at' => 'immutable_datetime', 'boarding_departure_at' => 'immutable_datetime', 'features' => 'array'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Prediction snapshots are immutable.'));
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
