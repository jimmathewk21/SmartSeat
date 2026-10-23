<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBookingDelegate extends Model
{
    use HasFactory;

    public $table = 'event_booking_delegates';

    protected $fillable = [
        'event_booking_id',
        'delegate_index',
        'delegate_name',
        'designation',
        'company_name',
        'country',
        'email',
        'mobile',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function booking()
    {
        return $this->belongsTo(EventBooking::class, 'event_booking_id');
    }
}

