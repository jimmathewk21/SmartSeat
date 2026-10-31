<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['passenger_id', 'inventory_id', 'trip_id', 'status', 'cancelled_at'];

    protected function casts(): array
    {
        return ['cancelled_at' => 'immutable_datetime'];
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(TripSeatInventory::class, 'inventory_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TrainTrip::class, 'trip_id');
    }
}
