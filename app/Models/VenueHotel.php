<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VenueHotel extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'venue_hotels';

    protected $fillable = [
        'name',
        'city',
        'country',
        'stars',
        'hotel_image',
        'hotel_gallery',
        'hotel_address',
        'hotel_description',
        'room_section_heading',
        'room_section_text',
        'gst_rate',
        'hotel_rooms',
        'booking_available_from',
        'booking_available_to',
        'direction_from',
        'direction_to',
        'is_active',
        'sort_order',
        'source_world_event_id',
    ];

    protected $casts = [
        'stars' => 'integer',
        'gst_rate' => 'decimal:2',
        'hotel_gallery' => 'array',
        'hotel_rooms' => 'array',
        'booking_available_from' => 'date',
        'booking_available_to' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'source_world_event_id' => 'integer',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
