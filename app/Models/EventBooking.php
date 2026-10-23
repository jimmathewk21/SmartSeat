<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventBooking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    public $table = 'event_bookings';

    protected $fillable = [
        'booking_code',
        'user_id',
        'participant_id',
        'event_id',
        'event_ticket_plan_id',
        'quantity',
        'unit_price_paise',
        'total_amount_paise',
        'currency',
        'billing_country_code',
        'display_currency',
        'display_unit_amount_minor',
        'display_total_amount_minor',
        'fx_rate_usd_to_inr',
        'billing_company_name',
        'billing_address_line_1',
        'billing_address_line_2',
        'gstin',
        'promo_code',
        'promo_discount_minor',
        'gateway_charge_minor',
        'subtotal_display_minor',
        'grand_total_display_minor',
        'status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'failure_reason',
        'paid_at',
        'payment_payload',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_paise' => 'integer',
        'total_amount_paise' => 'integer',
        'display_unit_amount_minor' => 'integer',
        'display_total_amount_minor' => 'integer',
        'fx_rate_usd_to_inr' => 'decimal:6',
        'paid_at' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function plan()
    {
        return $this->belongsTo(EventTicketPlan::class, 'event_ticket_plan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    public function tickets()
    {
        return $this->hasMany(BookingTicket::class, 'event_booking_id');
    }

    public function delegates()
    {
        return $this->hasMany(EventBookingDelegate::class, 'event_booking_id')->orderBy('delegate_index');
    }
}
