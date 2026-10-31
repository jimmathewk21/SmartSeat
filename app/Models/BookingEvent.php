<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingEvent extends Model
{
    protected $fillable = ['actor_id', 'action', 'from_status', 'to_status'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
