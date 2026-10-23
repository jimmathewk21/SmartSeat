<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainBookingSeat extends Model
{
    use HasFactory;
    protected $fillable = ['train_booking_id', 'train_trip_id', 'trip_seat_inventory_id', 'train_booking_passenger_id', 'released_at'];

    protected $casts = ['released_at' => 'datetime'];

    public function booking()
    {
        return $this->belongsTo(TrainBooking::class, 'train_booking_id');
    }

    public function inventory()
    {
        return $this->belongsTo(TripSeatInventory::class, 'trip_seat_inventory_id');
    }

    public function passenger()
    {
        return $this->belongsTo(TrainBookingPassenger::class, 'train_booking_passenger_id');
    }
}
