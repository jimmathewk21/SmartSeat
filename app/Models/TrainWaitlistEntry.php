<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainWaitlistEntry extends Model
{
    use HasFactory;
    public const STATUS_WAITING = 'waiting';
    public const STATUS_PROMOTED = 'promoted';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['user_id', 'train_trip_id', 'boarding_station_id', 'destination_station_id', 'boarding_at', 'arrival_at', 'seat_count', 'requires_accessible_seat', 'status', 'promoted_booking_id', 'promoted_at'];

    protected $casts = ['boarding_at' => 'datetime', 'arrival_at' => 'datetime', 'requires_accessible_seat' => 'boolean', 'promoted_at' => 'datetime'];

    public function boardingStation()
    {
        return $this->belongsTo(Station::class, 'boarding_station_id');
    }

    public function destinationStation()
    {
        return $this->belongsTo(Station::class, 'destination_station_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(TrainTrip::class, 'train_trip_id');
    }

    public function passengers()
    {
        return $this->hasMany(TrainWaitlistPassenger::class, 'train_waitlist_entry_id');
    }

    public function promotedBooking()
    {
        return $this->belongsTo(TrainBooking::class, 'promoted_booking_id');
    }
}
