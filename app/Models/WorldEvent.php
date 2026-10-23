<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorldEvent extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'world_events';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'days',
        'city',
        'country',
        'hotel',
        'theme',
        'website',
        'logo_path',
        'stars',
        'is_active',
        'is_hotel',
        'hotel_image',
        'hotel_gallery',
        'hotel_address',
        'hotel_description',
        'hotel_rooms',
        'booking_available_from',
        'booking_available_to',
        'direction_from',
        'direction_to',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_hotel' => 'boolean',
        'hotel_gallery' => 'array',
        'hotel_rooms' => 'array',
        'booking_available_from' => 'date',
        'booking_available_to' => 'date',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
