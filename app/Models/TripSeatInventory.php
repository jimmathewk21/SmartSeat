<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripSeatInventory extends Model
{
    use HasFactory;
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BOOKED = 'booked';

    protected $fillable = ['train_trip_id', 'train_id', 'train_seat_id', 'status', 'current_booking_id'];

    public function trip()
    {
        return $this->belongsTo(TrainTrip::class, 'train_trip_id');
    }

    public function seat()
    {
        return $this->belongsTo(TrainSeat::class, 'train_seat_id');
    }

    public function currentBooking()
    {
        return $this->belongsTo(TrainBooking::class, 'current_booking_id');
    }

    public function assignments()
    {
        return $this->hasMany(TrainBookingSeat::class, 'trip_seat_inventory_id');
    }
}
