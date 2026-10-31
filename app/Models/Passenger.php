<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passenger extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id', 'trip_id', 'name', 'age', 'gender', 'status', 'cancelled_at', 'preferred_berth', 'accessible_seat_required'];

    protected function casts(): array
    {
        return ['cancelled_at' => 'immutable_datetime', 'age' => 'integer', 'accessible_seat_required' => 'boolean'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TrainTrip::class, 'trip_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SeatAssignment::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }
}
