<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingTicket extends Model
{
    use HasFactory;

    public $table = 'booking_tickets';

    protected $fillable = [
        'event_booking_id',
        'ticket_code',
        'status',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function booking()
    {
        return $this->belongsTo(EventBooking::class, 'event_booking_id');
    }
}

