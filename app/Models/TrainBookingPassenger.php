<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainBookingPassenger extends Model
{
    use HasFactory;
    protected $fillable = ['train_booking_id', 'name', 'age', 'gender', 'requires_accessible_seat'];

    protected $casts = ['requires_accessible_seat' => 'boolean', 'age' => 'integer'];

    public function booking()
    {
        return $this->belongsTo(TrainBooking::class, 'train_booking_id');
    }

    public function seatAssignments()
    {
        return $this->hasMany(TrainBookingSeat::class, 'train_booking_passenger_id');
    }
}
