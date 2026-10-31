<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainTrip extends Model
{
    use HasFactory;

    protected $fillable = ['train_id', 'service_date', 'status', 'cancelled_at'];

    protected function serviceDate(): Attribute
    {
        // DATE must remain a date-only string on SQLite as well as MariaDB.
        return Attribute::make(set: fn ($value) => CarbonImmutable::parse($value)->toDateString());
    }

    protected function casts(): array
    {
        return ['service_date' => 'immutable_date', 'cancelled_at' => 'immutable_datetime'];
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(TripSeatInventory::class, 'trip_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'trip_id');
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class, 'trip_id');
    }
}
