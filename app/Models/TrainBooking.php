<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainBooking extends Model
{
    use HasFactory;
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['booking_code', 'user_id', 'train_trip_id', 'boarding_station_id', 'destination_station_id', 'boarding_at', 'arrival_at', 'status', 'seat_count', 'confirmed_at', 'cancelled_at'];

    protected $casts = ['boarding_at' => 'datetime', 'arrival_at' => 'datetime', 'confirmed_at' => 'datetime', 'cancelled_at' => 'datetime'];

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
        return $this->hasMany(TrainBookingPassenger::class, 'train_booking_id');
    }

    public function seatAssignments()
    {
        return $this->hasMany(TrainBookingSeat::class, 'train_booking_id');
    }
}
