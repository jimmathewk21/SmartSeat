<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTicketPlan extends Model
{
    use HasFactory;

    public $table = 'event_ticket_plans';

    protected $fillable = [
        'event_id',
        'plan_index',
        'sort_order',
        'name',
        'caption',
        'background_image_url',
        'price_paise',
        'mrp_inr_paise',
        'sale_inr_paise',
        'mrp_usd_cents',
        'sale_usd_cents',
        'benefits',
        'is_active',
        'is_featured',
        'valid_till_text',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'price_paise' => 'integer',
        'mrp_inr_paise' => 'integer',
        'sale_inr_paise' => 'integer',
        'mrp_usd_cents' => 'integer',
        'sale_usd_cents' => 'integer',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function bookings()
    {
        return $this->hasMany(EventBooking::class, 'event_ticket_plan_id');
    }
}
