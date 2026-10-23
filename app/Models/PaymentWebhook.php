<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhook extends Model
{
    use HasFactory;

    public $table = 'payment_webhooks';

    protected $fillable = [
        'provider',
        'event_name',
        'event_id',
        'entity_id',
        'payload_hash',
        'signature_valid',
        'processed',
        'payload',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'processed' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}

