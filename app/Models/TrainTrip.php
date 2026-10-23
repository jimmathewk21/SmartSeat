<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainTrip extends Model
{
    use HasFactory;
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = ['train_id', 'starts_at', 'ends_at', 'status'];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function train()
    {
        return $this->belongsTo(Train::class);
    }

    public function inventories()
    {
        return $this->hasMany(TripSeatInventory::class, 'train_trip_id');
    }

    public function bookings()
    {
        return $this->hasMany(TrainBooking::class, 'train_trip_id');
    }

    public function waitlistEntries()
    {
        return $this->hasMany(TrainWaitlistEntry::class, 'train_trip_id');
    }
}
