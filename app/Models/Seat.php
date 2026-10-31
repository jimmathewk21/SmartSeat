<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = ['coach_id', 'train_id', 'number', 'berth_type', 'accessibility_status'];

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(TripSeatInventory::class);
    }
}
