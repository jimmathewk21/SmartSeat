<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['reference', 'user_id', 'trip_id', 'train_id', 'boarding_order', 'alighting_order', 'status', 'total_paise', 'cancelled_at', 'submission_key', 'request_hash', 'travel_class', 'keep_group_together'];

    protected $hidden = ['submission_key', 'request_hash'];

    public function predictionSnapshot(): HasOne
    {
        return $this->hasOne(WaitlistPredictionSnapshot::class);
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function events(): HasMany
    {
        return $this->hasMany(BookingEvent::class);
    }

    protected function casts(): array
    {
        return ['cancelled_at' => 'immutable_datetime', 'total_paise' => 'integer', 'keep_group_together' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TrainTrip::class, 'trip_id');
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }
}
