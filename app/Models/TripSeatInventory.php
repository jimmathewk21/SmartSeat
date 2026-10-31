<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TripSeatInventory extends Model
{
    use HasFactory;

    protected $table = 'trip_seat_inventory';

    protected $fillable = ['trip_id', 'seat_id', 'train_id', 'status'];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TrainTrip::class, 'trip_id');
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SeatAssignment::class, 'inventory_id');
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(SeatAssignment::class, 'inventory_id')->where('status', 'active');
    }
}
