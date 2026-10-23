<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelBooking extends Model
{
    use HasFactory;

    public $table = 'hotel_bookings';

    protected $fillable = [
        'world_event_id',
        'venue_hotel_id',
        'booking_code',
        'hotel_name',
        'room_name',
        'rate_inr',
        'rate_usd',
        'full_name',
        'email',
        'phone',
        'country',
        'occupancy',
        'bed_size',
        'preference',
        'check_in_date',
        'check_out_date',
        'number_of_guests',
        'number_of_rooms',
        'number_of_nights',
        'subtotal_inr',
        'subtotal_usd',
        'gst_rate',
        'gst_amount_inr',
        'gst_amount_usd',
        'total_inr',
        'total_usd',
        'special_requests',
        'status',
    ];

    protected $casts = [
        'world_event_id' => 'integer',
        'venue_hotel_id' => 'integer',
        'rate_inr' => 'decimal:2',
        'rate_usd' => 'decimal:2',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'number_of_guests' => 'integer',
        'number_of_rooms' => 'integer',
        'number_of_nights' => 'integer',
        'subtotal_inr' => 'decimal:2',
        'subtotal_usd' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'gst_amount_inr' => 'decimal:2',
        'gst_amount_usd' => 'decimal:2',
        'total_inr' => 'decimal:2',
        'total_usd' => 'decimal:2',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function setCheckInDateAttribute($value): void
    {
        $this->attributes['check_in_date'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function setCheckOutDateAttribute($value): void
    {
        $this->attributes['check_out_date'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }
}
