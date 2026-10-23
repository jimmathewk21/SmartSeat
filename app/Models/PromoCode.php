<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'promo_codes';

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_percent',
        'flat_inr_paise',
        'flat_usd_cents',
        'quantity_buy',
        'quantity_pay',
        'max_uses',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}

